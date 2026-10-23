<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';
import TooltipIcon from '@/Components/TooltipIcon.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    suppliers: Object,
    summary: Object,
    filters: Object, // 👈 new
});

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'contact_person', label: 'Contact Person' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
];

const peso = (val) => `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const deleteSupplier = (id) => {
    if (confirm('Delete this supplier?')) {
        router.delete(route('suppliers.destroy', id));
    }
};

// ─── Search state ──────────────────────────
const search = ref(props.filters?.search || '');
const appliedSearch = ref(search.value);
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const hasExpenses = ref(props.filters?.has_expenses || '');

// ─── Debounce helper ────────────────────────
function debounce(fn, delay) {
    let timeoutId = null;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// ─── Apply filters ──────────────────────────
const applyFilters = () => {
    appliedSearch.value = search.value;
    const params = {};
    if (appliedSearch.value) params.search = appliedSearch.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    if (hasExpenses.value) params.has_expenses = hasExpenses.value;
    router.get(route('suppliers.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const debouncedSearch = debounce(applyFilters, 400);
const debouncedFilters = debounce(applyFilters, 250);
watch(search, debouncedSearch);
watch([dateFrom, dateTo, hasExpenses], debouncedFilters);

// ─── Reset search ───────────────────────────
const resetSearch = () => {
    search.value = '';
    appliedSearch.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    hasExpenses.value = '';
    applyFilters();
};
</script>

<template>
    <AppLayout>
        <div class="p-6">

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
                <h1 class="text-2xl font-bold">Suppliers</h1>
                <Link :href="route('suppliers.create')" class="btn btn-primary btn-md">Add Supplier</Link>
            </div>

            <!-- Summary Cards with Tooltips -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Suppliers -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-4 shadow relative">
                    <div class="flex items-center justify-between">
                        <p class="text-sm uppercase tracking-wider opacity-80">Total Suppliers</p>
                        <TooltipIcon text="Total number of suppliers in your system." />
                    </div>
                    <p class="text-2xl font-bold">{{ summary.total_suppliers ?? 0 }}</p>
                </div>

                <!-- Total Expenses -->
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow relative">
                    <div class="flex items-center justify-between">
                        <p class="text-sm uppercase tracking-wider opacity-80">Total Expenses</p>
                        <TooltipIcon text="Total amount spent across all suppliers." />
                    </div>
                    <p class="text-2xl font-bold">{{ peso(summary.total_expenses) }}</p>
                </div>

                <!-- Top Supplier (Spend) -->
                <div class="bg-gradient-to-br from-rose-600 to-red-700 text-white rounded-lg p-4 shadow relative">
                    <div class="flex items-center justify-between">
                        <p class="text-sm uppercase tracking-wider opacity-80">Top Supplier (Spend)</p>
                        <TooltipIcon text="Supplier with the highest total expenses." />
                    </div>
                    <p class="text-lg font-bold">{{ summary.top_supplier_amount?.name || '—' }}</p>
                    <p class="text-sm opacity-80">{{ peso(summary.top_supplier_amount?.amount) }}</p>
                </div>

                <!-- Most Used -->
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-lg p-4 shadow relative">
                    <div class="flex items-center justify-between">
                        <p class="text-sm uppercase tracking-wider opacity-80">Most Used</p>
                        <TooltipIcon text="Supplier with the most transactions." />
                    </div>
                    <p class="text-lg font-bold">{{ summary.top_supplier_count?.name || '—' }}</p>
                    <p class="text-sm opacity-80">{{ summary.top_supplier_count?.count ?? 0 }} transactions</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-4 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Search Suppliers</label>
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, contact, email, phone, address..."
                            class="w-full border rounded-lg px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 pr-8"
                        />
                        <button
                            v-if="search"
                            @click="search = ''"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                        >
                            ✕
                        </button>
                    </div>
                </div>
                <div>
                    <label for="supplier-date-from" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Added From</label>
                    <input id="supplier-date-from" v-model="dateFrom" type="date" class="rounded-lg border px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label for="supplier-date-to" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Added To</label>
                    <input id="supplier-date-to" v-model="dateTo" type="date" class="rounded-lg border px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label for="supplier-expense-filter" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Expense Activity</label>
                    <select id="supplier-expense-filter" v-model="hasExpenses" class="rounded-lg border px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Suppliers</option>
                        <option value="yes">Has Expenses</option>
                        <option value="no">No Expenses</option>
                    </select>
                </div>
                <button
                    @click="resetSearch"
                    class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1.5 rounded text-sm transition"
                >
                    Reset
                </button>
                <span class="text-xs text-gray-500 ml-2">{{ suppliers.total }} records</span>
            </div>

            <!-- DataTable -->
            <DataTable :columns="columns" :data="suppliers.data">
                <template #column-email="{ value }">
                    <a :href="`mailto:${value}`" class="text-blue-600 hover:underline">{{ value || '-' }}</a>
                </template>
                <template #actions="{ row }">
                    <Link :href="route('suppliers.edit', row.id)" class="text-blue-600 mr-2">Edit</Link>
                    <Link :href="route('suppliers.show', row.id)" class="text-green-600 mr-2">View</Link>
                    <button @click="deleteSupplier(row.id)" class="text-red-600">Delete</button>
                </template>
            </DataTable>

            <Pagination :links="suppliers.links" />
        </div>
    </AppLayout>
</template>
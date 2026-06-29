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

// ─── Debounce helper ────────────────────────
function debounce(fn, delay) {
    let timeoutId = null;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// ─── Apply search ───────────────────────────
const applySearch = debounce(() => {
    const params = {};
    if (search.value) params.search = search.value;
    router.get(route('suppliers.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

// ─── Watch search ───────────────────────────
watch(search, applySearch);

// ─── Reset search ───────────────────────────
const resetSearch = () => {
    search.value = '';
    window.location.href = route('suppliers.index');
};
</script>

<template>
    <AppLayout>
        <div class="p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Suppliers</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
                <h1 class="text-2xl font-bold">Suppliers</h1>
                <Link :href="route('suppliers.create')" class="bg-blue-500 text-white px-4 py-2 rounded">Add Supplier</Link>
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
                <div class="bg-gradient-to-br from-purple-500 to-violet-600 text-white rounded-lg p-4 shadow relative">
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

            <!-- Search Bar -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-4 flex flex-wrap items-center gap-4">
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

            <!-- Pagination -->
            <div v-if="suppliers.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in suppliers.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
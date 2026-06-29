<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AppStatus from '@/Components/AppStatus.vue';

const props = defineProps({
    transactions: Object,
    summary: Object,
    filters: Object,
    categories: Array,
});

const peso = (val) => `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// ─── Filter state ──────────────────────────
const filters = ref({
    type: props.filters?.type || '',
    category: props.filters?.category || '',
    search: props.filters?.search || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

// ─── Debounce helper ────────────────────────
function debounce(fn, delay) {
    let timeoutId = null;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// ─── Apply filters ──────────────────────────
const applyFilters = debounce(() => {
    const params = new URLSearchParams();
    Object.keys(filters.value).forEach(key => {
        if (filters.value[key]) params.append(key, filters.value[key]);
    });
    router.get(route('misc.index'), params.toString(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch(filters, applyFilters, { deep: true });

// ─── Reset filters ──────────────────────────
const resetFilters = () => {
    filters.value = { type: '', category: '', search: '', date_from: '', date_to: '' };
    applyFilters();
};

// ─── Export CSV ─────────────────────────────
const exportCSV = () => {
    const params = new URLSearchParams();
    Object.keys(filters.value).forEach(key => {
        if (filters.value[key]) params.append(key, filters.value[key]);
    });
    window.open(route('misc.export.csv') + '?' + params.toString(), '_blank');
};

// ─── Row click to edit ──────────────────────
const goToEdit = (id) => {
    router.visit(route('misc.edit', id));
};

// ─── Delete function ────────────────────────
const deleteMisc = (id, event) => {
    event.stopPropagation(); // prevent row click
    if (confirm('Delete this record?')) {
        router.delete(route('misc.destroy', id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Miscellaneous</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Miscellaneous Transactions</h1>
                <div class="flex gap-2">
                    <button @click="exportCSV" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition text-sm">
                        📄 Export CSV
                    </button>
                    <Link :href="route('misc.create')" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition">
                        + Add Record
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Income</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_income) }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Expenses</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_expense) }}</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-blue-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Net</p>
                    <p class="text-2xl font-bold" :class="summary.net < 0 ? 'text-red-200' : 'text-green-200'">
                        {{ peso(summary.net) }}
                    </p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-violet-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Records</p>
                    <p class="text-2xl font-bold">{{ summary.count ?? 0 }}</p>
                </div>
            </div>

            <!-- ─── Filters ──────────────────────────────── -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select v-model="filters.type" class="border rounded px-3 py-1.5 dark:bg-gray-700 text-sm">
                        <option value="">All</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select v-model="filters.category" class="border rounded px-3 py-1.5 dark:bg-gray-700 text-sm">
                        <option value="">All</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Search</label>
                    <input v-model="filters.search" placeholder="Desc or Ref #" class="border rounded px-3 py-1.5 dark:bg-gray-700 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">From</label>
                    <input type="date" v-model="filters.date_from" class="border rounded px-3 py-1.5 dark:bg-gray-700 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">To</label>
                    <input type="date" v-model="filters.date_to" class="border rounded px-3 py-1.5 dark:bg-gray-700 text-sm" />
                </div>
                <div class="flex gap-2 self-end pb-0.5">
                    <button @click="resetFilters" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-1.5 rounded text-sm transition">
                        Reset
                    </button>
                    <span class="text-xs text-gray-500 ml-1 self-center">{{ transactions.total }} records</span>
                </div>
            </div>

            <!-- ─── Transactions Table ──────────────────────── -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference #</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="t in transactions.data" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer" @click="goToEdit(t.id)">
                            <td class="px-4 py-2">{{ t.id }}</td>
                            <td class="px-4 py-2">
                                <AppStatus :type="t.type === 'income' ? 'success' : 'danger'" :label="t.type" size="sm" />
                            </td>
                            <td class="px-4 py-2">{{ t.date }}</td>
                            <td class="px-4 py-2 font-medium"
                                :class="t.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                ₱{{ t.amount.toLocaleString() }}
                            </td>
                            <td class="px-4 py-2">{{ t.category || '-' }}</td>
                            <td class="px-4 py-2 max-w-xs truncate" :title="t.description || ''">{{ t.description || '-' }}</td>
                            <td class="px-4 py-2">{{ t.reference_number || '-' }}</td>
                            <td class="px-4 py-2" @click.stop>
                                <Link :href="route('misc.edit', t.id)" class="text-blue-600 dark:text-blue-400 hover:underline mr-2">Edit</Link>
                                <button @click="deleteMisc(t.id, $event)" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!transactions.data || transactions.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No miscellaneous transactions found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ─── Pagination ────────────────────────────── -->
            <div v-if="transactions.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in transactions.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
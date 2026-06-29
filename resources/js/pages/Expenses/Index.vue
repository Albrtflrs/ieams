<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    transactions: { type: Object, default: () => ({ data: [], links: [] }) },
    summary: { type: Object, default: () => ({ categories: {}, total_expenses: 0, status_counts: {} }) },
    filters: { type: Object, default: () => ({ period: 'this_month', month: '', category: '', status: '', search: '' }) },
    categories: { type: Array, default: () => [] }, // 👈 dynamic from controller
});

const deleteExpense = (id) => {
    if (confirm('Delete this expense record?')) {
        router.delete(route('expenses.destroy', id));
    }
};

// ── Use categories from props ──
const CATEGORIES = props.categories.length ? props.categories : [];

const STATUSES = ['Paid', 'Unpaid', 'Pending'];
const PERIODS = [
    { value: 'this_month', label: 'This Month' },
    { value: 'this_year', label: 'This Year' },
    { value: 'last_12_months', label: 'Last 12 Months' },
    { value: 'all_time', label: 'All Time' },
];

// ── Filter state ──────────────────────────
const selectedPeriod = ref(props.filters.period || 'this_month');
const selectedMonth = ref(props.filters.month || new Date().toISOString().slice(0, 7));
const selectedCategory = ref(props.filters.category || '');
const selectedStatus = ref(props.filters.status || '');
const search = ref(props.filters.search || '');

// ── Debounce helper ────────────────────────
function debounce(fn, delay) {
    let timeoutId = null;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// ── Apply filters ──────────────────────────
const applyFilter = () => {
    const params = {
        period: selectedPeriod.value,
        category: selectedCategory.value,
        status: selectedStatus.value || undefined,
        search: search.value || undefined,
    };
    if (selectedPeriod.value === 'this_month') {
        params.month = selectedMonth.value;
    }
    router.get(route('expenses.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// ── Debounced apply ────────────────────────
const debouncedApply = debounce(applyFilter, 300);

// ── Watchers ───────────────────────────────
watch([selectedPeriod, selectedMonth, selectedCategory, selectedStatus, search], debouncedApply);

// ── Reset filters ──────────────────────────
const resetFilters = () => {
    selectedPeriod.value = 'this_month';
    selectedMonth.value = new Date().toISOString().slice(0, 7);
    selectedCategory.value = '';
    selectedStatus.value = '';
    search.value = '';
    applyFilter();
};

const peso = (val) =>
    `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const categoryTotals = computed(() => props.summary.categories ?? {});

const statusBadgeClass = (status) => {
    const map = {
        'Paid': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'Unpaid': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'Pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};

// ── Global status counts (from summary) ── 👈 NEW
const statusCounts = computed(() => props.summary.status_counts ?? {
    Paid: 0,
    Unpaid: 0,
    Pending: 0,
});
</script>

<template>
    <AppLayout>
        <div class="p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Expenses</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Expense Transactions</h1>
                <Link :href="route('expenses.create')"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded transition">
                + Add Expense
                </Link>
            </div>

            <!-- Status summary badges (global counts) -->
            <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-gray-800 p-3 rounded-lg shadow mb-4">
                <span
                    class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status:</span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Paid ({{ statusCounts.Paid }})
                </span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    Unpaid ({{ statusCounts.Unpaid }})
                </span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                    Pending ({{ statusCounts.Pending }})
                </span>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4 mb-6 bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <!-- Period -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Period:</label>
                    <select v-model="selectedPeriod" class="border rounded-lg px-3 py-1.5 text-sm dark:bg-gray-700">
                        <option v-for="p in PERIODS" :key="p.value" :value="p.value">{{ p.label }}</option>
                    </select>
                </div>
                <div v-if="selectedPeriod === 'this_month'" class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Month:</label>
                    <input type="month" v-model="selectedMonth"
                        class="border rounded-lg px-3 py-1.5 text-sm dark:bg-gray-700" />
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category:</label>
                    <select v-model="selectedCategory" class="border rounded-lg px-3 py-1.5 text-sm dark:bg-gray-700">
                        <option value="">All</option>
                        <option v-for="cat in CATEGORIES" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                    <select v-model="selectedStatus" class="border rounded-lg px-3 py-1.5 text-sm dark:bg-gray-700">
                        <option value="">All</option>
                        <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
                    </select>
                </div>

                <!-- Search Bar -->
                <div class="flex items-center gap-2 flex-1 min-w-[180px]">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Search:</label>
                    <div class="relative flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Supplier, description, receipt #..."
                            class="w-full border rounded-lg px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 pr-8 text-sm"
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

                <span class="text-xs text-gray-500 ml-2">({{ transactions.total }} records)</span>

                <!-- Reset button -->
                <button
                    @click="resetFilters"
                    class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1.5 rounded text-sm transition"
                >
                    Reset
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Expenses</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_expenses) }}</p>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700 col-span-3">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">By Category</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        <div v-for="(total, cat) in categoryTotals" :key="cat"
                            class="flex justify-between px-3 py-1 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ cat }}</span>
                            <span class="text-sm font-medium text-rose-600 dark:text-rose-400">{{ peso(total) }}</span>
                        </div>
                        <div v-if="Object.keys(categoryTotals).length === 0"
                            class="text-sm text-gray-500 col-span-full">
                            No expenses for this period.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div v-if="transactions?.data && transactions.data.length > 0"
                class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Supplier</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Category</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Receipt #</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Payment Method</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="t in transactions.data" :key="t.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2">{{ t.id }}</td>
                            <td class="px-4 py-2">{{ t.date || '-' }}</td>
                            <td class="px-4 py-2">{{ t.supplier_name || '-' }}</td>
                            <td class="px-4 py-2">{{ t.category }}</td>
                            <td class="px-4 py-2 font-medium text-rose-600 dark:text-rose-400">
                                ₱{{ (t.amount || 0).toLocaleString() }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    :class="statusBadgeClass(t.status)">
                                    {{ t.status || 'Unpaid' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ t.receipt_number || '-' }}</td>
                            <td class="px-4 py-2">{{ t.payment_method || '-' }}</td>
                            <td class="px-4 py-2">
                                <Link :href="route('expenses.edit', t.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:underline mr-2">Edit</Link>
                                <button @click="deleteExpense(t.id)"
                                    class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-8 text-gray-500">No transactions for this period.</div>

            <!-- Pagination -->
            <div v-if="transactions?.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in transactions.links" :key="link.label">
                    <button v-if="link.url" @click="router.visit(link.url)"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md" v-html="link.label" />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
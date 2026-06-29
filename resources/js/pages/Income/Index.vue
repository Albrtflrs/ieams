<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    transactions: { type: Object, required: true },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] }, // 👈 dynamic from controller
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// Use categories from props, fallback to empty array
const CATEGORIES = props.categories.length ? props.categories : [];

const STATUSES = ['Paid', 'Unpaid', 'Cash On Hold', 'Paid Royalty'];

// ── Filters ──
const selectedCategory = ref(props.filters.category ?? '');
const selectedStatus = ref(props.filters.status ?? '');
const searchQuery = ref(props.filters.search ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

// ── Period preset ──
const selectedPeriod = ref(
    dateFrom.value || dateTo.value ? 'custom' : 'all'
);

function pad(n) {
    return String(n).padStart(2, '0');
}
function iso(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function applyPeriodPreset(period) {
    const now = new Date();
    if (period === 'today') {
        dateFrom.value = iso(now);
        dateTo.value = iso(now);
    } else if (period === 'this_month') {
        dateFrom.value = iso(new Date(now.getFullYear(), now.getMonth(), 1));
        dateTo.value = iso(new Date(now.getFullYear(), now.getMonth() + 1, 0));
    } else if (period === 'all') {
        dateFrom.value = '';
        dateTo.value = '';
    }
}

watch(selectedPeriod, applyPeriodPreset);

// ── Custom debounce ──
function debounce(fn, delay) {
    let timeoutId = null;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// ── Debounced filter application ──
const applyFilters = debounce(() => {
    const params = {
        category: selectedCategory.value || undefined,
        status: selectedStatus.value || undefined,
        search: searchQuery.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    };
    router.get(route('income.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch([selectedCategory, selectedStatus, searchQuery, dateFrom, dateTo], applyFilters);

// ── Reset Filters – HARD RESET ──
const resetFilters = () => {
    selectedCategory.value = '';
    selectedStatus.value = '';
    searchQuery.value = '';
    selectedPeriod.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    window.location.href = route('income.index');
};

// ── Columns ──
const columns = [
    { key: 'item_no', label: 'Item No.' },
    { key: 'client_name', label: 'Client / Agency' },
    { key: 'municipality', label: 'Municipality' },
    { key: 'barangay', label: 'Barangay' },
    { key: 'particulars', label: 'Particulars' },
    { key: 'category', label: 'Category' },
    { key: 'date_delivered', label: 'Date Delivered' },
    { key: 'date_paid', label: 'Date Paid' },
    { key: 'receipt_number', label: 'Receipt No.' },
    { key: 'gross_price', label: 'Gross Price' },
    { key: 'amount_paid', label: 'Amount Paid' },
    { key: 'royalty_gross', label: 'Royalty Gross' },
    { key: 'deductions', label: 'Deductions' },
    { key: 'net_sales', label: 'Net Sales' },
    { key: 'receivables', label: 'Receivable' },
    { key: 'status', label: 'Status' },
    { key: 'withdrawn', label: 'Withdrawn' },
];

// ── Helpers ──
const deleteIncome = (id) => {
    if (confirm('Delete this income record?')) {
        router.delete(route('income.destroy', id));
    }
};

const categoryTotals = computed(() => props.summary.categories ?? {});

const statusBadgeClass = (status) => {
    const map = {
        'Paid': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'Unpaid': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'Cash On Hold': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'Paid Royalty': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    };
    return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// ── Global status counts (from summary) ──
const statusCounts = computed(() => props.summary.status_counts ?? {
    Paid: 0,
    Unpaid: 0,
    'Cash On Hold': 0,
    'Paid Royalty': 0,
});

const clearSearch = () => {
    searchQuery.value = '';
};
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Income</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center gap-2">
                <h1 class="text-2xl font-bold">Income Transactions</h1>
                <Link :href="route('income.create')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded transition">
                    + Add Income
                </Link>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- By Category -->
                <div class="bg-gray-900 dark:bg-gray-800 text-white rounded-lg overflow-hidden shadow">
                    <div class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-xs uppercase tracking-wider text-gray-400">
                        By Category
                    </div>
                    <div v-for="cat in CATEGORIES" :key="cat"
                         class="flex justify-between px-4 py-2 border-b border-gray-700 last:border-0 hover:bg-gray-800 transition"
                         :title="`Total gross price for ${cat}`">
                        <span class="text-sm">{{ cat }}</span>
                        <span class="font-semibold">{{ peso(categoryTotals[cat]) }}</span>
                    </div>
                    <div v-if="Object.keys(categoryTotals).length === 0" class="text-center text-gray-400 text-xs py-2">
                        No data for selected filters
                    </div>
                </div>

                <!-- Profit & Sales -->
                <div class="bg-blue-900 dark:bg-blue-950 text-white rounded-lg overflow-hidden shadow">
                    <div class="px-4 py-2 bg-blue-800 dark:bg-blue-900 text-xs uppercase tracking-wider text-blue-200">
                        Profit & Sales
                    </div>
                    <div class="flex justify-between px-4 py-2 border-b border-blue-800 hover:bg-blue-800 transition"
                         title="Gross price minus deductions and royalty gross">
                        <span>Gross Profit</span>
                        <span class="font-semibold">{{ peso(summary.gross_profit) }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2 border-b border-blue-800 hover:bg-blue-800 transition"
                         title="Total amount actually paid by client">
                        <span>Amount Paid</span>
                        <span class="font-semibold">{{ peso(summary.amount_paid) }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2 border-b border-blue-800 hover:bg-blue-800 transition"
                         title="Outstanding balance for transactions matching the current filters">
                        <span>Receivables</span>
                        <span class="font-semibold">{{ peso(summary.receivables) }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2 hover:bg-blue-800 transition"
                         title="Gross price minus deductions">
                        <span>Net Sales</span>
                        <span class="font-semibold">{{ peso(summary.net_sales) }}</span>
                    </div>
                </div>

                <!-- Royalty -->
                <div class="bg-yellow-600 dark:bg-yellow-700 text-white rounded-lg overflow-hidden shadow">
                    <div class="px-4 py-2 bg-yellow-700 dark:bg-yellow-800 text-xs uppercase tracking-wider text-yellow-200">
                        Royalty
                    </div>
                    <div class="flex justify-between px-4 py-2 border-b border-yellow-500 hover:bg-yellow-700 transition"
                         title="Gross royalty amount before deductions">
                        <span>Royalty Gross</span>
                        <span class="font-semibold">{{ peso(summary.royalty_gross) }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2 hover:bg-yellow-700 transition"
                         title="Royalty gross minus deductions">
                        <span>Royalty Net</span>
                        <span class="font-semibold">{{ peso(summary.royalty_net) }}</span>
                    </div>
                </div>
            </div>

            <!-- Status badges (global counts) -->
            <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-gray-800 p-3 rounded-lg shadow">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status:</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Paid ({{ statusCounts.Paid }})
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    Unpaid ({{ statusCounts.Unpaid }})
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                    Cash On Hold ({{ statusCounts['Cash On Hold'] }})
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                    Paid Royalty ({{ statusCounts['Paid Royalty'] }})
                </span>
            </div>

            <!-- Filters with Reset button -->
            <div class="flex flex-wrap items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category:</label>
                    <select v-model="selectedCategory" class="border rounded px-3 py-1.5 dark:bg-gray-700">
                        <option value="">All</option>
                        <option v-for="cat in CATEGORIES" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                    <select v-model="selectedStatus" class="border rounded px-3 py-1.5 dark:bg-gray-700">
                        <option value="">All</option>
                        <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Period:</label>
                    <select v-model="selectedPeriod" class="border rounded px-3 py-1.5 dark:bg-gray-700">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="this_month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2">
                    <input type="date" v-model="dateFrom" class="border rounded px-2 py-1.5 dark:bg-gray-700" />
                    <span class="text-gray-400">to</span>
                    <input type="date" v-model="dateTo" class="border rounded px-2 py-1.5 dark:bg-gray-700" />
                </div>
                <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Search:</label>
                    <div class="relative flex-1">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Client, agency, particulars..."
                            class="w-full border rounded-lg px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 pr-8"
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                        >
                            ✕
                        </button>
                    </div>
                </div>
                <button @click="resetFilters" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1.5 rounded text-sm transition">
                    Reset Filters
                </button>
                <span class="text-xs text-gray-500 ml-2">({{ transactions.total }} records)</span>
            </div>

            <!-- DataTable -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <DataTable :columns="columns" :data="transactions.data" row-key="id">
                    <template #column-client_name="{ row }">
                        {{ row.client_name || row.agency_department || '-' }}
                    </template>
                    <template #column-municipality="{ value }">
                        {{ value || '-' }}
                    </template>
                    <template #column-barangay="{ value }">
                        {{ value || '-' }}
                    </template>
                    <template #column-date_delivered="{ value }">
                        {{ value || '-' }}
                    </template>
                    <template #column-date_paid="{ value }">
                        {{ value || '-' }}
                    </template>
                    <template #column-receipt_number="{ value }">
                        {{ value || '-' }}
                    </template>
                    <template #column-gross_price="{ value }">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ peso(value) }}</span>
                    </template>
                    <template #column-amount_paid="{ value }">
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ peso(value) }}</span>
                    </template>
                    <template #column-royalty_gross="{ value }">
                        {{ peso(value) }}
                    </template>
                    <template #column-deductions="{ value }">
                        {{ peso(value) }}
                    </template>
                    <template #column-net_sales="{ value }">
                        <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ peso(value) }}</span>
                    </template>
                    <template #column-receivables="{ value }">
                        <span :class="Number(value) > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500'">
                            {{ Number(value) > 0 ? peso(value) : '—' }}
                        </span>
                    </template>
                    <template #column-status="{ value }">
                        <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                              :class="statusBadgeClass(value)">
                            {{ value || 'Unpaid' }}
                        </span>
                    </template>
                    <template #column-withdrawn="{ value }">
                        <span :class="value ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'">
                            {{ value ? 'Done' : 'Not Yet' }}
                        </span>
                    </template>
                    <template #actions="{ row }">
                        <Link :href="route('income.edit', row.id)" class="text-blue-600 dark:text-blue-400 hover:underline mr-2">Edit</Link>
                        <button @click="deleteIncome(row.id)" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </template>
                </DataTable>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in transactions.links" :key="link.label">
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
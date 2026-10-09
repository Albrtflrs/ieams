<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Chart from 'chart.js/auto';
import { useDarkMode } from '@/composables/useDarkMode';
import AppStatus from '@/Components/AppStatus.vue';

const props = defineProps({
    months: { type: Array, default: () => [] },
    income_by_month: { type: Array, default: () => [] },
    expense_by_month: { type: Array, default: () => [] },
    gross_profit_this_month: { type: Number, default: 0 },
    direct_costs_this_month: { type: [Number, String], default: 0 },
    revenue_this_month: { type: [Number, String], default: 0 },
    net_profit_this_month: { type: Number, default: 0 },
    operating_expenses_this_month: { type: Number, default: 0 },
    operating_profit_this_month: { type: Number, default: 0 },
    gross_profit_last_12m: { type: Number, default: 0 },
    direct_costs_last_12m: { type: [Number, String], default: 0 },
    revenue_last_12m: { type: [Number, String], default: 0 },
    net_profit_last_12m: { type: Number, default: 0 },
    operating_expenses_last_12m: { type: Number, default: 0 },
    operating_profit_last_12m: { type: Number, default: 0 },
    cash_balance: { type: Number, default: 0 },
    expense_categories: { type: Array, default: () => [] },
    recent_transactions: { type: Array, default: () => [] },
    accounting_categories: { type: Array, default: () => [] },
    receivables_this_month: { type: Number, default: 0 },
    receivables_last_12m: { type: Number, default: 0 },
    fallback: {
        type: Object,
        default: () => ({ is_fallback: false, original_month: '', display_month: '' }),
    },
});

const { isDark } = useDarkMode();

const toNumber = (val) => {
    if (typeof val === 'string') return parseFloat(val) || 0;
    return typeof val === 'number' && !isNaN(val) ? val : 0;
};

// ─── Color Palette ────────────────────────────
const colorPalette = [
    '#6366F1', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981',
    '#3B82F6', '#F43F5E', '#14B8A6', '#F97316', '#84CC16',
    '#06B6D4', '#A855F7',
];

// Chart refs
let incomeExpenseChart = null;
let netIncomeChart = null;
let expenseCategoryChart = null;
let accountingChart = null;

const selectedMonth = ref(new Date().toISOString().slice(0, 7));
const searchQuery = ref('');

function getChartTextColor() { return isDark.value ? '#94a3b8' : '#64748b'; }
function getGridColor() { return isDark.value ? '#1e293b' : '#f1f5f9'; }

const incomeByMonthNumbers = computed(() => props.income_by_month.map(toNumber));
const expenseByMonthNumbers = computed(() => props.expense_by_month.map(toNumber));

function initCharts() {
    [incomeExpenseChart, netIncomeChart, expenseCategoryChart, accountingChart].forEach(c => c?.destroy());

    const dark = isDark.value;
    const txt = getChartTextColor();
    const grid = getGridColor();

    const sharedScales = {
        x: { ticks: { color: txt, font: { size: 11 } }, grid: { color: grid } },
        y: { ticks: { color: txt, font: { size: 11 } }, grid: { color: grid } }
    };

    // 1. Income vs Expenses
    const ctx1 = document.getElementById('incomeExpenseChart');
    if (ctx1) {
        incomeExpenseChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: props.months,
                datasets: [
                    { 
                        label: 'Income', 
                        data: incomeByMonthNumbers.value, 
                        backgroundColor: dark ? 'rgba(16,185,129,0.7)' : 'rgba(16,185,129,0.85)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4 
                    },
                    { 
                        label: 'Expenses', 
                        data: expenseByMonthNumbers.value, 
                        backgroundColor: dark ? 'rgba(239,68,68,0.7)' : 'rgba(239,68,68,0.85)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 4 
                    }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: sharedScales }
        });
    }

    // 2. Net Income
    const netData = incomeByMonthNumbers.value.map((inc, i) => inc - expenseByMonthNumbers.value[i]);
    const ctx2 = document.getElementById('netIncomeChart');
    if (ctx2) {
        netIncomeChart = new Chart(ctx2, {
            type: 'line',
            data: { 
                labels: props.months, 
                datasets: [{ 
                    label: 'Net Income', 
                    data: netData, 
                    borderColor: '#8b5cf6', 
                    backgroundColor: dark ? 'rgba(139,92,246,0.15)' : 'rgba(139,92,246,0.08)',
                    tension: 0.4, 
                    fill: true, 
                    pointRadius: 3, 
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#8b5cf6',
                    borderWidth: 2,
                }] 
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: sharedScales }
        });
    }

    // 3. Expense Categories (Bar chart)
    const ctx3 = document.getElementById('expenseCategoryChart');
    if (ctx3 && props.expense_categories.length) {
        const sorted = [...props.expense_categories].sort((a,b) => b.amount - a.amount);
        const colors = sorted.map((_, i) => colorPalette[i % colorPalette.length]);
        expenseCategoryChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: sorted.map(c => c.name),
                datasets: [{ 
                    label: 'Expense Amount', 
                    data: sorted.map(c => toNumber(c.amount)), 
                    backgroundColor: colors,
                    borderColor: colors.map(c => dark ? c : c),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12 } } }, scales: sharedScales }
        });
    }

    // 4. Accounting Categories (Bar chart)
    const ctx4 = document.getElementById('accountingChart');
    if (ctx4 && props.accounting_categories.length) {
        const sorted = [...props.accounting_categories].sort((a,b) => b.amount - a.amount);
        const colors = sorted.map((_, i) => colorPalette[(i + 3) % colorPalette.length]);
        accountingChart = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: sorted.map(c => c.name),
                datasets: [{ 
                    label: 'Amount', 
                    data: sorted.map(c => toNumber(c.amount)), 
                    backgroundColor: colors,
                    borderColor: colors.map(c => dark ? c : c),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12 } } }, scales: sharedScales }
        });
    }
}

watch(isDark, () => initCharts());

function applyFilter() {
    router.get(route('dashboard', { month: selectedMonth.value }), {}, { preserveState: true });
}

const filteredTransactions = computed(() => {
    const transactions = props.recent_transactions ?? [];
    if (!searchQuery.value) return transactions;
    return transactions.filter(tx =>
        tx.particulars?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        tx.client_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        tx.supplier_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const getVal = (key) => {
    const v = props[key];
    return toNumber(v);
};

const peso = (val) => `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const navigateTo = (routeName) => {
    router.visit(route(routeName));
};

// ─── Collapsible State ───────────────────────────────
const collapsedGroups = ref({
    this_month: localStorage.getItem('dashboard_collapse_this_month') === 'true',
    last_12m: localStorage.getItem('dashboard_collapse_last_12m') === 'true',
});

const toggleGroup = (key) => {
    collapsedGroups.value[key] = !collapsedGroups.value[key];
    localStorage.setItem(`dashboard_collapse_${key}`, String(collapsedGroups.value[key]));
};

// ─── Color Mapping for Card Labels, Values & Icons ──
const cardColorMap = {
    'Gross Profit': { label: 'text-emerald-600 dark:text-emerald-400', value: 'text-emerald-700 dark:text-emerald-300', bg: 'bg-emerald-50 dark:bg-emerald-900/20', icon: 'text-emerald-500' },
    'Amount Paid': { label: 'text-blue-600 dark:text-blue-400', value: 'text-blue-700 dark:text-blue-300', bg: 'bg-blue-50 dark:bg-blue-900/20', icon: 'text-blue-500' },
    'Receivables': { label: 'text-amber-600 dark:text-amber-400', value: 'text-amber-700 dark:text-amber-300', bg: 'bg-amber-50 dark:bg-amber-900/20', icon: 'text-amber-500' },
    'Net Sales': { label: 'text-purple-600 dark:text-purple-400', value: 'text-purple-700 dark:text-purple-300', bg: 'bg-purple-50 dark:bg-purple-900/20', icon: 'text-purple-500' },
    'Royalty Gross': { label: 'text-rose-600 dark:text-rose-400', value: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-900/20', icon: 'text-rose-500' },
    'Royalty Net': { label: 'text-indigo-600 dark:text-indigo-400', value: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-900/20', icon: 'text-indigo-500' },
};

function getCardColors(label) {
    return cardColorMap[label] || { label: 'text-gray-600 dark:text-gray-400', value: 'text-gray-800 dark:text-white', bg: 'bg-gray-100 dark:bg-gray-700', icon: 'text-gray-500' };
}

// ─── SVG Icons for Cards ─────────────────────────────
const iconMap = {
    'Gross Profit': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'Amount Paid': 'M4 4h16v16H4V4zm2 2v12h12V6H6zm2 2h8v2H8V8zm0 4h8v2H8v-2zm0 4h8v2H8v-2z',
    'Receivables': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'Net Sales': 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    'Royalty Gross': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'Royalty Net': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

// ─── Card Groups ──────────────────────────────────────
const groups = [
    {
        key: 'this_month',
        label: 'This Month',
        accent: '#10b981',
        cards: [
            { key: 'gross_profit_this_month', label: 'Gross Profit', icon: iconMap['Gross Profit'], to: route('reports.index') },
            { key: 'direct_costs_this_month', label: 'Amount Paid', icon: iconMap['Amount Paid'], to: route('expenses.index') },
            { key: 'receivables_this_month', label: 'Receivables', icon: iconMap['Receivables'], to: route('income.index') },
            { key: 'net_profit_this_month', label: 'Net Sales', icon: iconMap['Net Sales'], to: route('reports.index') },
            { key: 'operating_expenses_this_month', label: 'Royalty Gross', icon: iconMap['Royalty Gross'], to: route('reports.index') },
            { key: 'operating_profit_this_month', label: 'Royalty Net', icon: iconMap['Royalty Net'], to: route('reports.index') },
        ]
    },
    {
        key: 'last_12m',
        label: 'Last 12 Months',
        accent: '#6366f1',
        cards: [
            { key: 'gross_profit_last_12m', label: 'Gross Profit', icon: iconMap['Gross Profit'], to: route('reports.index') },
            { key: 'direct_costs_last_12m', label: 'Amount Paid', icon: iconMap['Amount Paid'], to: route('expenses.index') },
            { key: 'receivables_last_12m', label: 'Receivables', icon: iconMap['Receivables'], to: route('income.index') },
            { key: 'net_profit_last_12m', label: 'Net Sales', icon: iconMap['Net Sales'], to: route('reports.index') },
            { key: 'operating_expenses_last_12m', label: 'Royalty Gross', icon: iconMap['Royalty Gross'], to: route('reports.index') },
            { key: 'operating_profit_last_12m', label: 'Royalty Net', icon: iconMap['Royalty Net'], to: route('reports.index') },
        ]
    },
];

onMounted(() => initCharts());
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="w-full overflow-x-auto space-y-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Dashboard</span>
            </div>

            <!-- Fallback banner -->
            <div v-if="fallback.is_fallback" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 flex items-center gap-2 text-amber-700 dark:text-amber-300 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    No data for <strong>{{ fallback.original_month }}</strong>. Showing data for <strong>{{ fallback.display_month }}</strong> instead.
                </span>
            </div>

            <!-- Header & Filters -->
            <div class="flex flex-col sm:flex-row flex-wrap justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Overview</p>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Financial Dashboard</h1>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        type="month"
                        v-model="selectedMonth"
                        @change="applyFilter"
                        class="px-3 py-1.5 text-sm border border-slate-200 dark:border-gray-700 rounded-lg bg-slate-50 dark:bg-gray-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    />
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search transactions…"
                            class="pl-9 pr-3 py-1.5 text-sm border border-slate-200 dark:border-gray-700 rounded-lg bg-slate-50 dark:bg-gray-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 w-48 md:w-60"
                        />
                    </div>
                </div>
            </div>

            <!-- ─── Summary Cards – responsive: 1→2→4 columns ── -->
            <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-4">
                <!-- Revenue -->
                <div class="w-full min-w-0 bg-white dark:bg-gray-900 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-800 transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Revenue</p>
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1 truncate">{{ peso(getVal('revenue_this_month')) }}</p>
                            <p class="text-xs text-emerald-500 mt-1 truncate">▲ 12.5% from last month</p>
                        </div>
                        <div class="flex-shrink-0 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 ml-3">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Target</span>
                            <span>58%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-1">
                            <div class="h-1.5 rounded-full bg-emerald-500" style="width: 58%"></div>
                        </div>
                    </div>
                </div>

                <!-- Expenses -->
                <div class="w-full min-w-0 bg-white dark:bg-gray-900 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-800 transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Expenses</p>
                            <p class="text-2xl font-bold text-rose-700 dark:text-rose-300 mt-1 truncate">{{ peso(getVal('direct_costs_this_month')) }}</p>
                            <p class="text-xs text-rose-500 mt-1 truncate">▲ 8.3% from last month</p>
                        </div>
                        <div class="flex-shrink-0 p-3 rounded-xl bg-rose-50 dark:bg-rose-900/20 ml-3">
                            <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M9 4v2m6-2v2M5 12h14M7 18h10" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Budget</span>
                            <span>72%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-1">
                            <div class="h-1.5 rounded-full bg-rose-500" style="width: 72%"></div>
                        </div>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="w-full min-w-0 bg-white dark:bg-gray-900 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-800 transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Net Profit</p>
                            <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300 mt-1 truncate">{{ peso(getVal('net_profit_this_month')) }}</p>
                            <p class="text-xs text-emerald-500 mt-1 truncate">▲ 6.2% from last month</p>
                        </div>
                        <div class="flex-shrink-0 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 ml-3">
                            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Target</span>
                            <span>45%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-1">
                            <div class="h-1.5 rounded-full bg-indigo-500" style="width: 45%"></div>
                        </div>
                    </div>
                </div>

                <!-- Receivables -->
                <div class="w-full min-w-0 bg-white dark:bg-gray-900 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-800 transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-amber-600 dark:text-amber-400 truncate">Receivables</p>
                            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-1 truncate">
                                {{ peso(getVal('receivables_this_month')) }}
                            </p>
                            <p class="text-xs text-amber-500 mt-1 truncate">▲ 4.3% from last month</p>
                        </div>
                        <div class="flex-shrink-0 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 ml-3">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Overdue (>30 days)</span>
                            <span>18%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-1">
                            <div class="h-1.5 rounded-full bg-amber-500" style="width: 18%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Collapsible Metric Cards (always 2 columns) ── -->
            <div class="space-y-4">
                <div v-for="group in groups" :key="group.key" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div
                        @click="toggleGroup(group.key)"
                        class="flex items-center justify-between px-5 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-1.5 h-5 rounded-full flex-shrink-0" :style="{ backgroundColor: group.accent }"></span>
                            <span class="text-sm font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 truncate">{{ group.label }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">
                                ({{ group.cards.length }} metrics)
                            </span>
                        </div>
                        <svg
                            class="w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0"
                            :class="collapsedGroups[group.key] ? '-rotate-90' : 'rotate-0'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div
                        v-show="!collapsedGroups[group.key]"
                        class="p-4 pt-0 transition-all duration-300 ease-in-out"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="card in group.cards" :key="card.key" class="w-full h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition hover:shadow-md hover:-translate-y-0.5 cursor-pointer">
                                <Link :href="card.to" class="block w-full h-full">
                                    <div class="flex items-start justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium truncate" :class="getCardColors(card.label).label">
                                                {{ card.label }}
                                            </p>
                                            <p class="text-2xl font-bold mt-1 truncate" :class="getCardColors(card.label).value">
                                                {{ peso(getVal(card.key)) }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 p-2 rounded-lg ml-3" :class="getCardColors(card.label).bg">
                                            <svg class="w-5 h-5" :class="getCardColors(card.label).icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-gray-400 dark:text-gray-500 truncate">Click to view</span>
                                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Charts ────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Income vs Expenses -->
                <div @click="navigateTo('income.index')" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5 transition hover:shadow-lg cursor-pointer">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Income vs Expenses</p>
                    <div style="height: 240px;"><canvas id="incomeExpenseChart"></canvas></div>
                    <p class="text-xs text-center text-slate-400 dark:text-slate-500 mt-2">Click to view Income</p>
                </div>

                <!-- Net Income -->
                <div @click="navigateTo('income.index')" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5 transition hover:shadow-lg cursor-pointer">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Net Income by Month</p>
                    <div style="height: 240px;"><canvas id="netIncomeChart"></canvas></div>
                    <p class="text-xs text-center text-slate-400 dark:text-slate-500 mt-2">Click to view Income</p>
                </div>

                <!-- Expense Categories -->
                <div @click="navigateTo('expenses.index')" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5 transition hover:shadow-lg cursor-pointer">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Expense Categories</p>
                    <div style="height: 240px;"><canvas id="expenseCategoryChart"></canvas></div>
                    <p v-if="!expense_categories.length" class="text-center text-gray-500 py-4">No expense category data.</p>
                    <p class="text-xs text-center text-slate-400 dark:text-slate-500 mt-2">Click to view Expenses</p>
                </div>

                <!-- Accounting Categories -->
                <div @click="navigateTo('expenses.index')" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5 transition hover:shadow-lg cursor-pointer">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Accounting Categories</p>
                    <div style="height: 240px;"><canvas id="accountingChart"></canvas></div>
                    <p v-if="!accounting_categories.length" class="text-center text-gray-500 py-4">No accounting category data.</p>
                    <p class="text-xs text-center text-slate-400 dark:text-slate-500 mt-2">Click to view Expenses</p>
                </div>
            </div>

            <!-- ─── Recent Transactions ────────────────────────── -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Recent Transactions</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-gray-800">
                                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Date</th>
                                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Particulars</th>
                                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Client / Supplier</th>
                                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Amount</th>
                                <th class="pb-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-gray-800">
                            <tr v-for="tx in filteredTransactions" :key="tx.id" class="hover:bg-slate-50 dark:hover:bg-gray-800/60 transition-colors duration-150">
                                <td class="py-3 pr-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ tx.date || '—' }}</td>
                                <td class="py-3 pr-4 text-slate-700 dark:text-slate-300 max-w-[200px] truncate">{{ tx.particulars || '—' }}</td>
                                <td class="py-3 pr-4 text-slate-600 dark:text-slate-400 truncate max-w-[150px]">{{ tx.client_name || tx.supplier_name || '—' }}</td>
                                <td class="py-3 pr-4 font-semibold whitespace-nowrap" :class="tx.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400'">
                                    {{ tx.type === 'income' ? '+' : '−' }} ₱{{ (tx.amount || 0).toLocaleString() }}
                                </td>
                                <td class="py-3"><AppStatus :type="tx.type === 'income' ? 'success' : 'danger'" :label="tx.type" /></td>
                            </tr>
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="5" class="py-10 text-center text-slate-400 dark:text-slate-500 text-sm">No transactions found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
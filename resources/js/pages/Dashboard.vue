<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Chart from 'chart.js/auto';
import { useDarkMode } from '@/composables/useDarkMode';
import AppStatus from '@/Components/AppStatus.vue';
import TooltipIcon from '@/Components/TooltipIcon.vue';

const props = defineProps({
    cards: { type: Object, default: () => ({}) },
    months: { type: Array, default: () => [] },
    income_by_month: { type: Array, default: () => [] },
    expense_by_month: { type: Array, default: () => [] },
    revenue_this_month: { type: [Number, String], default: 0 },
    direct_costs_this_month: { type: [Number, String], default: 0 },
    gross_profit_this_month: { type: Number, default: 0 },
    operating_expenses_this_month: { type: Number, default: 0 },
    operating_profit_this_month: { type: Number, default: 0 },
    net_profit_this_month: { type: Number, default: 0 },
    revenue_last_12m: { type: [Number, String], default: 0 },
    direct_costs_last_12m: { type: [Number, String], default: 0 },
    gross_profit_last_12m: { type: Number, default: 0 },
    operating_expenses_last_12m: { type: Number, default: 0 },
    operating_profit_last_12m: { type: Number, default: 0 },
    net_profit_last_12m: { type: Number, default: 0 },
    cash_balance: { type: Number, default: 0 },
    burn_rate: { type: Number, default: 0 },
    cash_runaway: { type: Number, default: 0 },
    revenue_by_month: { type: Array, default: () => [] },
    operating_expenses_by_month: { type: Array, default: () => [] },
    margin_by_month: { type: Array, default: () => [] },
    gross_margin_by_month: { type: Array, default: () => [] },
    operating_expenses_ratio_by_month: { type: Array, default: () => [] },
    expense_categories: { type: Array, default: () => [] },
    expense_vendors: { type: Array, default: () => [] },
    recent_transactions: { type: Array, default: () => [] },
    cash_balance_by_month: { type: Array, default: () => [] },
    // ── Receivables & Payables props ──
    total_receivables: { type: Number, default: 0 },
    total_payables: { type: Number, default: 0 },
    net_position: { type: Number, default: 0 },
    receivable_aging: { type: Object, default: () => ({ '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 }) },
    payable_aging: { type: Object, default: () => ({ '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 }) },
});

const { isDark } = useDarkMode();

// Helper: convert any value to a number
const toNumber = (val) => {
    if (typeof val === 'string') return parseFloat(val) || 0;
    return typeof val === 'number' && !isNaN(val) ? val : 0;
};

// Chart refs
let incomeExpenseChart = null;
let netIncomeChart = null;
let operatingChart = null;
let marginChart = null;
let categoryChart = null;
let vendorChart = null;
let cashBalanceChart = null;

const selectedMonth = ref(new Date().toISOString().slice(0, 7));
const searchQuery = ref('');

function getChartTextColor() { return isDark.value ? '#94a3b8' : '#64748b'; }
function getGridColor() { return isDark.value ? '#1e293b' : '#f1f5f9'; }

// ---- Convert array items to numbers ----
const incomeByMonthNumbers = computed(() => props.income_by_month.map(toNumber));
const expenseByMonthNumbers = computed(() => props.expense_by_month.map(toNumber));
const revenueByMonthNumbers = computed(() => props.revenue_by_month.map(toNumber));
const operatingExpensesByMonthNumbers = computed(() => props.operating_expenses_by_month.map(toNumber));
const marginByMonthNumbers = computed(() => props.margin_by_month.map(toNumber));
const grossMarginByMonthNumbers = computed(() => props.gross_margin_by_month.map(toNumber));
const operatingExpensesRatioByMonthNumbers = computed(() => props.operating_expenses_ratio_by_month.map(toNumber));
const cashBalanceByMonthNumbers = computed(() => props.cash_balance_by_month.map(toNumber));

function initCharts() {
    [incomeExpenseChart, netIncomeChart, operatingChart, marginChart, categoryChart, vendorChart, cashBalanceChart].forEach(c => c?.destroy());
    const dark = isDark.value;
    const txt = getChartTextColor();
    const grid = getGridColor();

    const sharedScales = {
        x: { ticks: { color: txt, font: { size: 11 } }, grid: { color: grid } },
        y: { ticks: { color: txt, font: { size: 11 } }, grid: { color: grid } }
    };

    const ctx1 = document.getElementById('incomeExpenseChart');
    if (ctx1) incomeExpenseChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: props.months,
            datasets: [
                { label: 'Income', data: incomeByMonthNumbers.value, backgroundColor: dark ? 'rgba(16,185,129,0.65)' : 'rgba(16,185,129,0.85)', borderRadius: 6 },
                { label: 'Expenses', data: expenseByMonthNumbers.value, backgroundColor: dark ? 'rgba(239,68,68,0.65)' : 'rgba(239,68,68,0.85)', borderRadius: 6 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: sharedScales }
    });

    const netData = incomeByMonthNumbers.value.map((inc, i) => inc - expenseByMonthNumbers.value[i]);
    const ctx2 = document.getElementById('netIncomeChart');
    if (ctx2) netIncomeChart = new Chart(ctx2, {
        type: 'line',
        data: { labels: props.months, datasets: [{ label: 'Net Income', data: netData, borderColor: '#6366f1', backgroundColor: dark ? 'rgba(99,102,241,0.15)' : 'rgba(99,102,241,0.08)', tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#6366f1' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: sharedScales }
    });

    const ctx3 = document.getElementById('operatingChart');
    if (ctx3) operatingChart = new Chart(ctx3, {
        type: 'line',
        data: {
            labels: props.months,
            datasets: [
                { label: 'Revenue', data: revenueByMonthNumbers.value, borderColor: '#10b981', backgroundColor: dark ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.06)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Operating Expenses', data: operatingExpensesByMonthNumbers.value, borderColor: '#f43f5e', backgroundColor: dark ? 'rgba(244,63,94,0.1)' : 'rgba(244,63,94,0.06)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Operating Margin %', data: marginByMonthNumbers.value, borderColor: '#6366f1', fill: false, tension: 0.4, yAxisID: 'y1', pointRadius: 3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: { ...sharedScales, y1: { position: 'right', ticks: { color: txt, font: { size: 11 } }, grid: { display: false } } } }
    });

    const ctx4 = document.getElementById('marginChart');
    if (ctx4) marginChart = new Chart(ctx4, {
        type: 'line',
        data: {
            labels: props.months,
            datasets: [
                { label: 'Gross Margin %', data: grossMarginByMonthNumbers.value, borderColor: '#8b5cf6', backgroundColor: dark ? 'rgba(139,92,246,0.1)' : 'rgba(139,92,246,0.06)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Operating Expenses Ratio %', data: operatingExpensesRatioByMonthNumbers.value, borderColor: '#f59e0b', backgroundColor: dark ? 'rgba(245,158,11,0.1)' : 'rgba(245,158,11,0.06)', fill: true, tension: 0.4, pointRadius: 3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: sharedScales }
    });

    const ctx5 = document.getElementById('categoryChart');
    if (ctx5 && props.expense_categories.length) categoryChart = new Chart(ctx5, {
        type: 'doughnut',
        data: {
            labels: props.expense_categories.map(c => c.name),
            datasets: [{ data: props.expense_categories.map(c => toNumber(c.amount)), backgroundColor: ['#10b981','#6366f1','#8b5cf6','#f59e0b','#f43f5e','#ec4899'], borderWidth: 2, borderColor: dark ? '#0f172a' : '#ffffff' }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 14 } } } }
    });

    const ctx6 = document.getElementById('vendorChart');
    if (ctx6 && props.expense_vendors.length) vendorChart = new Chart(ctx6, {
        type: 'bar',
        data: { labels: props.expense_vendors.map(v => v.name), datasets: [{ label: 'Amount', data: props.expense_vendors.map(v => toNumber(v.amount)), backgroundColor: dark ? 'rgba(139,92,246,0.65)' : 'rgba(139,92,246,0.85)', borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12 } } }, scales: sharedScales }
    });

    const ctx7 = document.getElementById('cashBalanceChart');
    if (ctx7) cashBalanceChart = new Chart(ctx7, {
        type: 'line',
        data: { labels: props.months, datasets: [{ label: 'Cash Balance', data: cashBalanceByMonthNumbers.value, borderColor: '#10b981', backgroundColor: dark ? 'rgba(16,185,129,0.15)' : 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#10b981' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: txt, boxWidth: 12, padding: 16 } } }, scales: sharedScales }
    });
}

watch(isDark, () => initCharts());

// ---- Quick Period Functions ----
function setPeriod(period) {
    const now = new Date();
    let month = '';
    switch (period) {
        case 'this_month':
            month = now.toISOString().slice(0, 7);
            break;
        case 'last_month':
            const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            month = lastMonth.toISOString().slice(0, 7);
            break;
        case 'this_year':
            month = `${now.getFullYear()}-01`;
            break;
        case 'last_12_months':
            const start = new Date(now.getFullYear(), now.getMonth() - 11, 1);
            month = start.toISOString().slice(0, 7);
            break;
        case 'all_time':
            month = '2000-01';
            break;
        default:
            month = now.toISOString().slice(0, 7);
    }
    selectedMonth.value = month;
    applyFilter();
}

function applyFilter() {
    router.get(route('dashboard', { month: selectedMonth.value }), {}, { preserveState: true });
}
function generateReport() {
    router.visit(route('reports.index'));
}

// ---- Filtered transactions ----
const filteredTransactions = computed(() => {
    const transactions = props.recent_transactions ?? [];
    if (!searchQuery.value) return transactions;
    return transactions.filter(tx =>
        tx.particulars?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        tx.client_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        tx.supplier_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Helper: get a numeric value from props
const getVal = (key) => {
    // Handle nested keys like 'receivable_aging.0_30'
    if (key.includes('.')) {
        const parts = key.split('.');
        let obj = props;
        for (const part of parts) {
            obj = obj[part];
            if (obj === undefined) return 0;
        }
        return toNumber(obj);
    }
    const v = props[key];
    return toNumber(v);
};

// Darken hex color
function darkenColor(hex, percent) {
    const c = hex.replace('#', '');
    const num = parseInt(c, 16);
    const amt = Math.round(2.55 * percent);
    let R = (num >> 16) - amt;
    let G = (num >> 8 & 0x00FF) - amt;
    let B = (num & 0x0000FF) - amt;
    R = Math.max(0, Math.min(255, R));
    G = Math.max(0, Math.min(255, G));
    B = Math.max(0, Math.min(255, B));
    return `#${(1 << 24 | R << 16 | G << 8 | B).toString(16).slice(1)}`;
}

// ── Format currency with ₱ sign ──
const peso = (val) => `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// ── Card Groups ──
const groups = [
    {
        label: 'This Month',
        accent: '#10b981',
        cards: [
            { key: 'revenue_this_month',             label: 'Revenue',              color: '#10b981', to: route('income.index'), tooltip: 'Total income from all sources.' },
            { key: 'direct_costs_this_month',         label: 'Direct Costs',         color: '#38bdf8', to: route('expenses.index'), tooltip: 'Costs directly tied to revenue.' },
            { key: 'gross_profit_this_month',         label: 'Gross Profit',         color: '#8b5cf6', to: route('reports.index'), tooltip: 'Revenue minus direct costs.' },
            { key: 'operating_expenses_this_month',   label: 'Operating Expenses',   color: '#f59e0b', to: route('misc.index'), tooltip: 'Day-to-day running costs.' },
            { key: 'operating_profit_this_month',     label: 'Operating Profit',     color: '#f43f5e', to: route('reports.index'), tooltip: 'Gross profit minus operating expenses.' },
            { key: 'net_profit_this_month',           label: 'Net Profit',           color: '#6366f1', to: route('reports.index'), tooltip: 'Revenue minus total expenses.' },
        ]
    },
    {
        label: 'Last 12 Months',
        accent: '#6366f1',
        cards: [
            { key: 'revenue_last_12m',               label: 'Revenue',              color: '#14b8a6', to: route('income.index'), tooltip: 'Total income over 12 months.' },
            { key: 'direct_costs_last_12m',           label: 'Direct Costs',         color: '#06b6d4', to: route('expenses.index'), tooltip: 'Total direct costs over 12 months.' },
            { key: 'gross_profit_last_12m',           label: 'Gross Profit',         color: '#a855f7', to: route('reports.index'), tooltip: 'Revenue minus direct costs over 12 months.' },
            { key: 'operating_expenses_last_12m',     label: 'Operating Expenses',   color: '#f97316', to: route('misc.index'), tooltip: 'Total operating expenses over 12 months.' },
            { key: 'operating_profit_last_12m',       label: 'Operating Profit',     color: '#ec4899', to: route('reports.index'), tooltip: 'Gross profit minus operating expenses over 12 months.' },
            { key: 'net_profit_last_12m',             label: 'Net Profit',           color: '#a78bfa', to: route('reports.index'), tooltip: 'Revenue minus total expenses over 12 months.' },
        ]
    },
    {
        label: 'Cash Position',
        accent: '#38bdf8',
        cards: [
            { key: 'cash_balance',  label: 'Cash Balance',         color: '#38bdf8', to: route('reports.index'), tooltip: 'Total cash available (all time).' },
            { key: 'burn_rate',     label: 'Burn Rate',            color: '#f43f5e', to: route('reports.index'), tooltip: 'Average monthly expenses over last 3 months.' },
            { key: 'cash_runaway',  label: 'Cash Runway (months)', color: '#f59e0b', isNumber: true, to: route('reports.index'), tooltip: 'Months your cash will last at current burn rate.' },
        ]
    },
    // ── NEW: Receivables & Payables ──
    {
        label: 'Receivables & Payables',
        accent: '#8b5cf6',
        cards: [
            { key: 'total_receivables',  label: 'Total Receivables',   color: '#8b5cf6', to: route('receivables-payables.index'), tooltip: 'Total unpaid income (what customers owe you).' },
            { key: 'total_payables',     label: 'Total Payables',      color: '#f43f5e', to: route('receivables-payables.index'), tooltip: 'Total unpaid expenses (what you owe suppliers).' },
            { key: 'net_position',       label: 'Net Position',        color: '#6366f1', to: route('receivables-payables.index'), tooltip: 'Receivables minus Payables (your net cash position).' },
        ]
    },
    // ── NEW: Aging Summary (Receivables) ──
    {
        label: 'Receivables Aging',
        accent: '#8b5cf6',
        cards: [
            { key: 'receivable_aging.0_30',    label: '0-30 Days',   color: '#10b981', isAging: true, to: route('receivables-payables.index') },
            { key: 'receivable_aging.31_60',   label: '31-60 Days',  color: '#f59e0b', isAging: true, to: route('receivables-payables.index') },
            { key: 'receivable_aging.61_90',   label: '61-90 Days',  color: '#f97316', isAging: true, to: route('receivables-payables.index') },
            { key: 'receivable_aging.90_plus', label: '90+ Days',    color: '#f43f5e', isAging: true, to: route('receivables-payables.index') },
        ]
    },
    // ── NEW: Aging Summary (Payables) ──
    {
        label: 'Payables Aging',
        accent: '#f43f5e',
        cards: [
            { key: 'payable_aging.0_30',    label: '0-30 Days',   color: '#10b981', isAging: true, to: route('receivables-payables.index') },
            { key: 'payable_aging.31_60',   label: '31-60 Days',  color: '#f59e0b', isAging: true, to: route('receivables-payables.index') },
            { key: 'payable_aging.61_90',   label: '61-90 Days',  color: '#f97316', isAging: true, to: route('receivables-payables.index') },
            { key: 'payable_aging.90_plus', label: '90+ Days',    color: '#f43f5e', isAging: true, to: route('receivables-payables.index') },
        ]
    },
];

onMounted(() => initCharts());
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="min-h-screen bg-slate-50 dark:bg-gray-950 px-6 py-6 transition-colors duration-300">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Dashboard</span>
            </div>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-0.5">Overview</p>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Financial Dashboard</h1>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('income.create')" class="btn btn-success btn-lg">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Income
                    </Link>
                    <Link :href="route('expenses.create')" class="btn btn-danger btn-lg">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Expense
                    </Link>
                    <button @click="generateReport" class="btn btn-secondary btn-lg">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generate Report
                    </button>
                </div>
            </div>

            <!-- Critical Alerts -->
            <div v-if="props.net_profit_this_month && props.net_profit_this_month < 0" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 p-3 rounded-lg mb-4 flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <span>Your business is currently operating at a loss (₱{{ Math.abs(props.net_profit_this_month).toLocaleString() }}). Consider reviewing expenses.</span>
            </div>
            <div v-if="props.cash_runaway && props.cash_runaway > 0 && props.cash_runaway < 3" class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 p-3 rounded-lg mb-4 flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <span>Cash runway is less than 3 months ({{ props.cash_runaway.toFixed(1) }} months). Reduce burn rate immediately.</span>
            </div>

            <!-- Quick Period Buttons -->
            <div class="flex flex-wrap gap-2 mb-3">
                <button @click="setPeriod('this_month')" 
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedMonth === new Date().toISOString().slice(0, 7) ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    MTD
                </button>
                <button @click="setPeriod('last_month')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedMonth === new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().slice(0, 7) ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    Last Month
                </button>
                <button @click="setPeriod('this_year')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedMonth === `${new Date().getFullYear()}-01` ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    YTD
                </button>
                <button @click="setPeriod('last_12_months')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedMonth === new Date(new Date().getFullYear(), new Date().getMonth() - 11, 1).toISOString().slice(0, 7) ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    12M
                </button>
                <button @click="setPeriod('all_time')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedMonth === '2000-01' ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    All Time
                </button>
            </div>

            <!-- Filter row -->
            <div class="flex flex-wrap items-center gap-4 mb-8 bg-white dark:bg-gray-900 px-5 py-3.5 rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm transition-colors duration-300">
                <div class="flex items-center gap-2.5">
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Month</label>
                    <input type="month" v-model="selectedMonth" @change="applyFilter"
                        class="border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-700 dark:text-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-colors duration-200" />
                </div>
                <div class="flex-1 min-w-[220px] relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                    <input v-model="searchQuery" type="text" placeholder="Search transactions…"
                        class="w-full pl-9 pr-4 border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-700 dark:text-slate-200 rounded-lg py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-colors duration-200" />
                </div>
            </div>

            <!-- ── Metric Card Groups ── -->
            <div class="space-y-8 mb-10">
                <div v-for="group in groups" :key="group.label">

                    <!-- Group label -->
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-1.5 h-4 rounded-full" :style="{ backgroundColor: group.accent }"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ group.label }}</span>
                        <div class="flex-1 h-px bg-slate-100 dark:bg-gray-800"></div>
                    </div>

                    <!-- Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div
                            v-for="card in group.cards"
                            :key="card.key"
                            class="group rounded-xl px-5 py-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 text-white cursor-pointer"
                            :style="{
                                background: `linear-gradient(135deg, ${card.color}, ${darkenColor(card.color, 30)})`
                            }"
                        >
                            <!-- If card.to exists, wrap in Link; else just render content -->
                            <template v-if="card.to">
                                <Link :href="card.to" class="block w-full h-full">
                                    <div class="flex justify-between items-start">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">{{ card.label }}</p>
                                        <TooltipIcon v-if="card.tooltip" :text="card.tooltip" class="text-white/60 hover:text-white" />
                                    </div>
                                    <p class="text-[1.6rem] font-bold leading-none tracking-tight mt-1.5">
                                        <template v-if="card.isNumber">{{ getVal(card.key).toFixed(1) }}</template>
                                        <template v-else-if="card.isAging">
                                            {{ getVal(card.key) > 0 ? peso(getVal(card.key)) : '₱0.00' }}
                                        </template>
                                        <template v-else>{{ peso(getVal(card.key)) }}</template>
                                    </p>
                                </Link>
                            </template>
                            <template v-else>
                                <div class="flex justify-between items-start">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-white/80">{{ card.label }}</p>
                                    <TooltipIcon v-if="card.tooltip" :text="card.tooltip" class="text-white/60 hover:text-white" />
                                </div>
                                <p class="text-[1.6rem] font-bold leading-none tracking-tight mt-1.5">
                                    <template v-if="card.isNumber">{{ getVal(card.key).toFixed(1) }}</template>
                                    <template v-else-if="card.isAging">
                                        {{ getVal(card.key) > 0 ? peso(getVal(card.key)) : '₱0.00' }}
                                    </template>
                                    <template v-else>{{ peso(getVal(card.key)) }}</template>
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Charts ── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
                <div v-for="chart in [
                    { id: 'incomeExpenseChart', title: 'Income vs Expenses' },
                    { id: 'netIncomeChart',     title: 'Net Income by Month' },
                    { id: 'operatingChart',     title: 'Operating Income & Expenses' },
                    { id: 'marginChart',        title: 'Gross Margin vs Operating Expenses' },
                    { id: 'categoryChart',      title: 'Expenses by Category' },
                    { id: 'vendorChart',        title: 'Expenses by Vendor' },
                ]" :key="chart.id"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm p-5 transition-colors duration-300"
                >
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">{{ chart.title }}</p>
                    <div style="height: 240px;"><canvas :id="chart.id"></canvas></div>
                </div>

                <!-- Cash Balance – full width -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm p-5 transition-colors duration-300">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Cash Balance Trend</p>
                    <div style="height: 240px;"><canvas id="cashBalanceChart"></canvas></div>
                </div>
            </div>

            <!-- ── Recent Transactions ── -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm p-5 transition-colors duration-300">
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
                            <tr v-for="tx in filteredTransactions" :key="tx.id"
                                class="hover:bg-slate-50 dark:hover:bg-gray-800/60 transition-colors duration-150">
                                <td class="py-3 pr-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ tx.date || '—' }}</td>
                                <td class="py-3 pr-4 text-slate-700 dark:text-slate-300 max-w-[200px] truncate">{{ tx.particulars || '—' }}</td>
                                <td class="py-3 pr-4 text-slate-600 dark:text-slate-400">{{ tx.client_name || tx.supplier_name || '—' }}</td>
                                <td class="py-3 pr-4 font-semibold whitespace-nowrap"
                                    :class="tx.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400'">
                                    {{ tx.type === 'income' ? '+' : '−' }} ₱{{ (tx.amount || 0).toLocaleString() }}
                                </td>
                                <td class="py-3">
                                    <AppStatus :type="tx.type === 'income' ? 'success' : 'danger'" :label="tx.type" />
                                </td>
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
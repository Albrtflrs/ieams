<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed, nextTick } from 'vue';
import { useSettings } from '@/composables/useSettings';
import DetailModal from '@/Components/DetailModal.vue';

const props = defineProps({
    period: String,
    month: String,
    date_from: String,
    date_to: String,
    metrics: Object,
    income_by_category: Array,
    expense_by_category: Array,
    months: Array,
    income_trend: Array,
    expense_trend: Array,
    top_client: Object,
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// Filters
const selectedPeriod = ref(props.period || 'this_month');
const selectedMonth = ref(props.month || new Date().toISOString().slice(0, 7));
const dateFrom = ref(props.date_from || '');
const dateTo = ref(props.date_to || '');

const applyFilter = () => {
    const params = {
        period: selectedPeriod.value,
        month: selectedPeriod.value === 'this_month' ? selectedMonth.value : undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    };
    router.get(route('summary.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let timeoutId = null;
const debouncedApply = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(applyFilter, 300);
};

watch([selectedPeriod, selectedMonth, dateFrom, dateTo], debouncedApply);

// Quick period setter
function setPeriod(period) {
    selectedPeriod.value = period;
    if (!['this_month', 'last_month'].includes(period)) {
        selectedMonth.value = '';
    }
    applyFilter();
}

// Format number with commas
const formatNumber = (val) => Number(val ?? 0).toLocaleString();

// Modal state
const modalShow = ref(false);
const modalTitle = ref('');
const modalType = ref('');
const modalData = ref({});

function openModal(cardKey) {
    switch (cardKey) {
        case 'revenue':
            modalTitle.value = 'Revenue Details';
            modalType.value = 'revenue';
            modalData.value = {
                incomeByCategory: props.income_by_category || [],
                totalRevenue: props.metrics?.revenue || 0,
            };
            break;
        case 'expenses':
            modalTitle.value = 'Expenses Details';
            modalType.value = 'expenses';
            modalData.value = {
                expenseByCategory: props.expense_by_category || [],
                totalExpenses: props.metrics?.expenses || 0,
            };
            break;
        case 'net_profit':
            modalTitle.value = 'Net Profit Details';
            modalType.value = 'net_profit';
            modalData.value = {
                totalRevenue: props.metrics?.revenue || 0,
                totalExpenses: props.metrics?.expenses || 0,
                netProfit: props.metrics?.net_profit || 0,
            };
            break;
        case 'direct_costs':
            modalTitle.value = 'Direct Costs Details';
            modalType.value = 'direct_costs';
            modalData.value = {
                expenseByCategory: props.expense_by_category || [],
                totalDirectCosts: props.metrics?.direct_costs || 0,
            };
            break;
        case 'gross_profit':
            modalTitle.value = 'Gross Profit Details';
            modalType.value = 'gross_profit';
            modalData.value = {
                totalRevenue: props.metrics?.revenue || 0,
                directCosts: props.metrics?.direct_costs || 0,
                grossProfit: props.metrics?.gross_profit || 0,
            };
            break;
        case 'operating_expenses':
            modalTitle.value = 'Operating Expenses Details';
            modalType.value = 'operating_expenses';
            modalData.value = {
                totalOperatingExpenses: props.metrics?.operating_expenses || 0,
            };
            break;
        case 'operating_profit':
            modalTitle.value = 'Operating Profit Details';
            modalType.value = 'operating_profit';
            modalData.value = {
                grossProfit: props.metrics?.gross_profit || 0,
                operatingExpenses: props.metrics?.operating_expenses || 0,
                operatingProfit: props.metrics?.operating_profit || 0,
            };
            break;
        case 'cash_balance':
            modalTitle.value = 'Cash Balance Details';
            modalType.value = 'cash_balance';
            modalData.value = {
                totalIncomeAll: props.metrics?.revenue + props.metrics?.direct_costs,
                totalExpensesAll: props.metrics?.expenses,
                cashBalance: props.metrics?.cash_balance || 0,
            };
            break;
        case 'receivables':
            modalTitle.value = 'Receivables Details';
            modalType.value = 'receivables';
            modalData.value = {
                totalReceivables: props.metrics?.receivables || 0,
            };
            break;
        default:
            modalTitle.value = 'Details';
            modalType.value = 'default';
            modalData.value = {};
    }
    modalShow.value = true;
}

function closeModal() {
    modalShow.value = false;
}

// ─── Cards definition ────────────────────
const mainCards = computed(() => [
    { 
        key: 'revenue', 
        label: 'Revenue', 
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'emerald',
        bg: 'bg-emerald-50 dark:bg-emerald-900/20',
        text: 'text-emerald-700 dark:text-emerald-300',
        iconColor: 'text-emerald-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '58%',
        change: '+12.5%',
        changeColor: 'text-emerald-500'
    },
    { 
        key: 'receivables', 
        label: 'Receivables', 
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'amber',
        bg: 'bg-amber-50 dark:bg-amber-900/20',
        text: 'text-amber-700 dark:text-amber-300',
        iconColor: 'text-amber-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '42%',
        change: '+5.1%',
        changeColor: 'text-emerald-500'
    },
    { 
        key: 'expenses', 
        label: 'Expenses', 
        icon: 'M3 6h18M9 4v2m6-2v2M5 12h14M7 18h10',
        color: 'rose',
        bg: 'bg-rose-50 dark:bg-rose-900/20',
        text: 'text-rose-700 dark:text-rose-300',
        iconColor: 'text-rose-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '72%',
        change: '+8.3%',
        changeColor: 'text-rose-500'
    },
    { 
        key: 'net_profit', 
        label: 'Net Profit', 
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        color: 'indigo',
        bg: 'bg-indigo-50 dark:bg-indigo-900/20',
        text: 'text-indigo-700 dark:text-indigo-300',
        iconColor: 'text-indigo-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '45%',
        change: '+6.2%',
        changeColor: 'text-emerald-500'
    },
    { 
        key: 'direct_costs', 
        label: 'Direct Costs', 
        icon: 'M16 10a4 4 0 01-8 0M8 21V14M16 21V14M12 18h.01M8 12h8',
        color: 'cyan',
        bg: 'bg-cyan-50 dark:bg-cyan-900/20',
        text: 'text-cyan-700 dark:text-cyan-300',
        iconColor: 'text-cyan-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '35%',
        change: '+3.2%',
        changeColor: 'text-emerald-500'
    },
    { 
        key: 'gross_profit', 
        label: 'Gross Profit', 
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'violet',
        bg: 'bg-violet-50 dark:bg-violet-900/20',
        text: 'text-violet-700 dark:text-violet-300',
        iconColor: 'text-violet-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '68%',
        change: '+4.8%',
        changeColor: 'text-emerald-500'
    },
    { 
        key: 'operating_expenses', 
        label: 'Operating Expenses', 
        icon: 'M3 6h18M9 4v2m6-2v2M5 12h14M7 18h10',
        color: 'orange',
        bg: 'bg-orange-50 dark:bg-orange-900/20',
        text: 'text-orange-700 dark:text-orange-300',
        iconColor: 'text-orange-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '55%',
        change: '+1.8%',
        changeColor: 'text-amber-500'
    },
    { 
        key: 'operating_profit', 
        label: 'Operating Profit', 
        icon: 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        color: 'pink',
        bg: 'bg-pink-50 dark:bg-pink-900/20',
        text: 'text-pink-700 dark:text-pink-300',
        iconColor: 'text-pink-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '62%',
        change: '+7.3%',
        changeColor: 'text-emerald-500'
    },
    { 
        key: 'cash_balance', 
        label: 'Cash Balance', 
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        color: 'cyan',
        bg: 'bg-cyan-50 dark:bg-cyan-900/20',
        text: 'text-cyan-700 dark:text-cyan-300',
        iconColor: 'text-cyan-500',
        labelColor: 'text-gray-500 dark:text-gray-400',
        progress: '82%',
        change: '+2.1%',
        changeColor: 'text-emerald-500'
    },
]);

// ─── Ratio cards ──────────────────────────
const ratioCards = computed(() => [
    { label: 'Gross Margin', key: 'gross_margin', suffix: '%', icon: '📈', color: 'from-green-400 to-emerald-500' },
    { label: 'Operating Margin', key: 'operating_margin', suffix: '%', icon: '📊', color: 'from-blue-400 to-indigo-500' },
    { label: 'Net Margin', key: 'net_margin', suffix: '%', icon: '🎯', color: 'from-purple-400 to-violet-500' },
]);

// ─── Helpers ──────────────────────────────
const getVal = (key) => {
    const v = props.metrics?.[key];
    return typeof v === 'number' ? v : 0;
};

// ─── Icon SVG render helper ──────────────
const iconPath = (d) => d;
</script>

<template>
    <AppLayout>
        <Head title="Financial Summary" />

        <div class="p-6">
            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Dashboard</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Financial Summary</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">📊 Financial Summary</h1>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('dashboard')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                        Dashboard
                    </Link>
                    <Link :href="route('reports.index')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Full Reports
                    </Link>
                </div>
            </div>

            <!-- Alerts -->
            <div v-if="metrics?.net_profit < 0" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 p-3 rounded-lg mb-4 flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <span>Your business is currently operating at a loss ({{ peso(metrics.net_profit) }}). Consider reviewing expenses or increasing revenue.</span>
            </div>
            <div v-if="metrics?.cash_balance < 0" class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 p-3 rounded-lg mb-4 flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <span>Your cash balance is negative ({{ peso(metrics.cash_balance) }}). Immediate action required.</span>
            </div>

            <!-- Quick Period Buttons -->
            <div class="flex flex-wrap gap-2 mb-3">
                <button @click="setPeriod('this_month')" 
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedPeriod === 'this_month' ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    MTD
                </button>
                <button @click="setPeriod('last_month')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedPeriod === 'last_month' ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    Last Month
                </button>
                <button @click="setPeriod('this_year')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedPeriod === 'this_year' ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    YTD
                </button>
                <button @click="setPeriod('last_12_months')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedPeriod === 'last_12_months' ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    12M
                </button>
                <button @click="setPeriod('all_time')"
                        class="px-4 py-1.5 text-sm rounded-full border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        :class="selectedPeriod === 'all_time' ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' : ''">
                    All Time
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow mb-6 space-y-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div v-if="selectedPeriod === 'this_month' || selectedPeriod === 'last_month'">
                        <label class="text-sm font-medium mr-2">Month:</label>
                        <input type="month" v-model="selectedMonth" class="border rounded px-3 py-1 dark:bg-gray-700" />
                    </div>
                    <div>
                        <label class="text-sm font-medium mr-2">Date From:</label>
                        <input type="date" v-model="dateFrom" class="border rounded px-3 py-1 dark:bg-gray-700" />
                    </div>
                    <div>
                        <label class="text-sm font-medium mr-2">Date To:</label>
                        <input type="date" v-model="dateTo" class="border rounded px-3 py-1 dark:bg-gray-700" />
                    </div>
                    <button @click="applyFilter" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded">Apply</button>
                </div>
            </div>

            <!-- ─── Modern Summary Cards ────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div v-for="card in mainCards" :key="card.key"
                     class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 transition hover:shadow-md cursor-pointer"
                     @click="openModal(card.key)">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                            <p class="text-2xl font-bold mt-1" :class="card.text">
                                {{ peso(getVal(card.key)) }}
                            </p>
                            <p class="text-xs mt-1" :class="card.changeColor">
                                {{ card.change }} from last period
                            </p>
                        </div>
                        <div class="p-3 rounded-xl" :class="card.bg">
                            <svg class="w-6 h-6" :class="card.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Target</span>
                            <span>{{ card.progress }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-1">
                            <div class="h-1.5 rounded-full" :class="card.color === 'emerald' ? 'bg-emerald-500' : 
                                                             card.color === 'amber' ? 'bg-amber-500' :
                                                             card.color === 'rose' ? 'bg-rose-500' :
                                                             card.color === 'indigo' ? 'bg-indigo-500' :
                                                             card.color === 'cyan' ? 'bg-cyan-500' :
                                                             card.color === 'violet' ? 'bg-violet-500' :
                                                             card.color === 'orange' ? 'bg-orange-500' :
                                                             card.color === 'pink' ? 'bg-pink-500' :
                                                             'bg-blue-500'" 
                                 :style="{ width: card.progress }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Ratio Cards ────────────────────────────── -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow mb-6">
                <h2 class="text-lg font-semibold mb-3">Key Ratios</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div v-for="ratio in ratioCards" :key="ratio.key"
                         class="bg-gradient-to-br text-white rounded-xl p-4 shadow-md border border-white/10 transition-all duration-300 hover:scale-105 hover:shadow-xl"
                         :class="ratio.color">
                        <div class="flex justify-between items-center">
                            <span class="text-2xl">{{ ratio.icon }}</span>
                            <span class="text-xs opacity-80 uppercase tracking-wider">{{ ratio.label }}</span>
                        </div>
                        <p class="text-2xl font-bold mt-2">
                            {{ metrics[ratio.key]?.toFixed(2) ?? 0 }}{{ ratio.suffix }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ─── Top Client ───────────────────────────────── -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                <h2 class="text-lg font-semibold mb-3">🏆 Top Client (by Revenue)</h2>
                <div v-if="top_client" class="flex justify-between items-center border-b pb-2">
                    <Link :href="route('clients.show', top_client.id)" class="text-blue-600 hover:underline font-medium">
                        {{ top_client.name }}
                    </Link>
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ peso(top_client.total) }}</span>
                </div>
                <div v-else class="text-gray-500">No data for this period.</div>
            </div>
        </div>

        <!-- ─── Modal ────────────────────────────────────────── -->
        <DetailModal
            :show="modalShow"
            :title="modalTitle"
            :type="modalType"
            :data="modalData"
            @close="closeModal"
        />
    </AppLayout>
</template>
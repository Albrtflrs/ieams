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
        case 'burn_rate':
            modalTitle.value = 'Burn Rate Details';
            modalType.value = 'burn_rate';
            modalData.value = {
                months: props.months?.slice(-3) || [],
                total: props.metrics?.expenses || 0,
                burnRate: props.metrics?.burn_rate || 0,
            };
            break;
        case 'cash_runaway':
            modalTitle.value = 'Cash Runway Details';
            modalType.value = 'cash_runaway';
            modalData.value = {
                cashBalance: props.metrics?.cash_balance || 0,
                burnRate: props.metrics?.burn_rate || 0,
                cashRunaway: props.metrics?.cash_runaway || 0,
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

// Cards definition with tooltips and emphasis
const cards = computed(() => [
    { 
        label: 'Revenue', key: 'revenue', 
        color: 'from-emerald-500 to-teal-600', 
        icon: '💰',
        tooltip: 'Total income from all sources (sales, services, miscellaneous income).' 
    },
    { 
        label: 'Expenses', key: 'expenses', 
        color: 'from-rose-500 to-pink-600', 
        icon: '💸',
        tooltip: 'All operating expenses including direct costs and overhead.' 
    },
    { 
        label: 'Net Profit', key: 'net_profit', 
        color: props.metrics?.net_profit >= 0 ? 'from-indigo-500 to-blue-600' : 'from-red-500 to-rose-600', 
        icon: '📈',
        tooltip: 'Revenue minus total expenses. A positive number means profit, negative means loss.' 
    },
    { 
        label: 'Direct Costs', key: 'direct_costs', 
        color: 'from-cyan-500 to-sky-600', 
        icon: '📦',
        tooltip: 'Costs directly tied to generating revenue (e.g., materials, labor).' 
    },
    { 
        label: 'Gross Profit', key: 'gross_profit', 
        color: 'from-purple-500 to-violet-600', 
        icon: '📊',
        tooltip: 'Revenue minus direct costs. Shows profitability before operating expenses.' 
    },
    { 
        label: 'Operating Expenses', key: 'operating_expenses', 
        color: 'from-amber-500 to-orange-600', 
        icon: '🔧',
        tooltip: 'Day-to-day running costs (rent, salaries, utilities, etc.) from miscellaneous transactions.' 
    },
    { 
        label: 'Operating Profit', key: 'operating_profit', 
        color: 'from-fuchsia-500 to-pink-600', 
        icon: '📉',
        tooltip: 'Gross profit minus operating expenses. Measures core business profitability.' 
    },
    { 
        label: 'Cash Balance', key: 'cash_balance', 
        color: 'from-cyan-400 to-blue-500', 
        icon: '💵',
        tooltip: 'Total cash available – calculated as total income minus total expenses (all time).' 
    },
    { 
        label: 'Burn Rate (Monthly)', key: 'burn_rate', 
        color: 'from-rose-400 to-red-500', 
        icon: '🔥',
        tooltip: 'Average monthly expenses over the last 3 months. Higher burn = faster cash depletion.' 
    },
    { 
        label: 'Cash Runway', key: 'cash_runaway', 
        color: 'from-amber-400 to-yellow-500', 
        icon: '⏳',
        tooltip: 'Number of months your current cash balance will last at the current burn rate.' 
    },
]);

const ratioCards = computed(() => [
    { label: 'Gross Margin', key: 'gross_margin', suffix: '%' },
    { label: 'Operating Margin', key: 'operating_margin', suffix: '%' },
    { label: 'Net Margin', key: 'net_margin', suffix: '%' },
]);
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
            <div v-if="metrics?.cash_runaway > 0 && metrics?.cash_runaway < 3" class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 p-3 rounded-lg mb-4 flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <span>Cash runway is less than 3 months ({{ metrics.cash_runaway.toFixed(1) }} months). Reduce burn rate immediately.</span>
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

            <!-- Metrics Cards (with stagger animation) -->
            <TransitionGroup
                name="card-stagger"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                appear
            >
                <div v-for="(card, index) in cards" :key="card.key"
                     :style="{ transitionDelay: `${index * 50}ms` }"
                     class="bg-gradient-to-br text-white rounded-lg p-4 shadow transition hover:scale-105 hover:shadow-xl duration-200 cursor-pointer"
                     :class="[card.color, index < 3 ? 'sm:col-span-2' : '']"
                     @click="openModal(card.key)">
                    <div class="flex justify-between items-start">
                        <span class="text-2xl">{{ card.icon }}</span>
                        <span class="text-xs opacity-80 uppercase tracking-wider">{{ card.label }}</span>
                        <!-- Tooltip icon -->
                        <div class="relative group ml-1">
                            <button @click.stop class="text-white/50 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 text-xs bg-gray-900 dark:bg-gray-700 text-white rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                                {{ card.tooltip }}
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl font-bold mt-2">
                        <template v-if="card.key === 'cash_runaway'">
                            {{ formatNumber(metrics[card.key]) }} months
                        </template>
                        <template v-else-if="card.key === 'burn_rate'">
                            {{ peso(metrics[card.key]) }} / mo
                        </template>
                        <template v-else>
                            {{ peso(metrics[card.key]) }}
                        </template>
                    </p>
                    <!-- Trend indicator for key metrics -->
                    <div v-if="['revenue', 'expenses', 'net_profit'].includes(card.key) && metrics[card.key + '_change'] !== undefined" 
                         class="flex items-center mt-1 text-xs">
                        <span v-if="metrics[card.key + '_change'] > 0" class="text-green-200">▲</span>
                        <span v-else-if="metrics[card.key + '_change'] < 0" class="text-red-200">▼</span>
                        <span v-else class="text-gray-300">—</span>
                        <span class="ml-1" 
                              :class="{
                                'text-green-200': metrics[card.key + '_change'] > 0,
                                'text-red-200': metrics[card.key + '_change'] < 0,
                                'text-gray-300': metrics[card.key + '_change'] === 0
                              }">
                            {{ Math.abs(metrics[card.key + '_change'] || 0) }}%
                        </span>
                        <span class="ml-1 text-white/60">vs previous</span>
                    </div>
                </div>
            </TransitionGroup>

            <!-- Ratios -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow mb-6 mt-6">
                <h2 class="text-lg font-semibold mb-3">Key Ratios</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div v-for="ratio in ratioCards" :key="ratio.key" class="bg-gray-50 dark:bg-gray-700 p-3 rounded text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ ratio.label }}</p>
                        <p class="text-xl font-bold">{{ metrics[ratio.key]?.toFixed(2) ?? 0 }}{{ ratio.suffix }}</p>
                    </div>
                </div>
            </div>

            <!-- Top Client (Clickable) -->
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

        <!-- Modal -->
        <DetailModal
            :show="modalShow"
            :title="modalTitle"
            :type="modalType"
            :data="modalData"
            @close="closeModal"
        />
    </AppLayout>
</template>

<style scoped>
/* Stagger animation */
.card-stagger-enter-active {
    animation: cardFadeUp 0.5s ease both;
}
.card-stagger-leave-active {
    animation: cardFadeDown 0.3s ease both;
}
@keyframes cardFadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes cardFadeDown {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(20px); }
}
</style>
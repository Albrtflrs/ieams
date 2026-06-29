<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    period: String,
    month: String,
    summary: Object,
    income_by_category: Array,
    expense_by_category: Array,
    months: Array,
    income_trend: Array,
    expense_trend: Array,
    top_clients: Array,
    top_suppliers: Array,
    filters: Object,
    filterOptions: Object,
    transactions: Array,
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// Filters
const selectedPeriod = ref(props.period || 'this_month');
const selectedMonth = ref(props.month || new Date().toISOString().slice(0, 7));
const selectedClient = ref(props.filters?.client_id || '');
const selectedSupplier = ref(props.filters?.supplier_id || '');
const selectedIncomeCategory = ref(props.filters?.income_category || '');
const selectedExpenseCategory = ref(props.filters?.expense_category || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const applyFilter = () => {
    const params = {
        period: selectedPeriod.value,
        month: selectedPeriod.value === 'this_month' ? selectedMonth.value : undefined,
        client_id: selectedClient.value || undefined,
        supplier_id: selectedSupplier.value || undefined,
        income_category: selectedIncomeCategory.value || undefined,
        expense_category: selectedExpenseCategory.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    };
    router.get(route('reports.index'), params, {
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

watch([selectedPeriod, selectedMonth, selectedClient, selectedSupplier, selectedIncomeCategory, selectedExpenseCategory, dateFrom, dateTo], debouncedApply);

// Export
const exportReport = (format) => {
    const params = {
        period: selectedPeriod.value,
        month: selectedPeriod.value === 'this_month' ? selectedMonth.value : undefined,
        client_id: selectedClient.value || undefined,
        supplier_id: selectedSupplier.value || undefined,
        income_category: selectedIncomeCategory.value || undefined,
        expense_category: selectedExpenseCategory.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    };
    const url = format === 'csv'
        ? route('reports.export.csv', params)
        : route('reports.export.pdf', params);
    window.location.href = url;
};

// ─── Chart refs ──────────────────────────────────────
let incomeBarChart = null;      // Replaces doughnut
let expenseBarChart = null;    // Replaces doughnut

function initCharts() {
    if (incomeBarChart) incomeBarChart.destroy();
    if (expenseBarChart) expenseBarChart.destroy();

    const dark = document.documentElement.classList.contains('dark');
    const txtColor = dark ? '#e5e7eb' : '#1f2937';
    const gridColor = dark ? '#374151' : '#e5e7eb';

    // ── Income by Category (Bar) ──
    const ctx1 = document.getElementById('incomeBarChart');
    if (ctx1 && props.income_by_category.length) {
        const sorted = [...props.income_by_category].sort((a,b) => b.value - a.value);
        incomeBarChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: sorted.map(c => c.name),
                datasets: [{ 
                    label: 'Income Amount', 
                    data: sorted.map(c => c.value), 
                    backgroundColor: dark ? 'rgba(16,185,129,0.7)' : 'rgba(16,185,129,0.85)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: txtColor, boxWidth: 12 } },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${peso(ctx.raw)}` } }
                },
                scales: {
                    x: { ticks: { color: txtColor, maxRotation: 45 }, grid: { color: gridColor } },
                    y: { ticks: { color: txtColor, callback: (val) => peso(val) }, grid: { color: gridColor } }
                }
            }
        });
    }

    // ── Expense by Category (Bar) ──
    const ctx2 = document.getElementById('expenseBarChart');
    if (ctx2 && props.expense_by_category.length) {
        const sorted = [...props.expense_by_category].sort((a,b) => b.value - a.value);
        expenseBarChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: sorted.map(c => c.name),
                datasets: [{ 
                    label: 'Expense Amount', 
                    data: sorted.map(c => c.value), 
                    backgroundColor: dark ? 'rgba(239,68,68,0.7)' : 'rgba(239,68,68,0.85)',
                    borderColor: '#ef4444',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: txtColor, boxWidth: 12 } },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${peso(ctx.raw)}` } }
                },
                scales: {
                    x: { ticks: { color: txtColor, maxRotation: 45 }, grid: { color: gridColor } },
                    y: { ticks: { color: txtColor, callback: (val) => peso(val) }, grid: { color: gridColor } }
                }
            }
        });
    }
}

watch(() => [props.income_by_category, props.expense_by_category], () => {
    nextTick(() => initCharts());
}, { deep: true });

onMounted(() => {
    nextTick(initCharts);
});
</script>

<template>
    <AppLayout>
        <Head title="Reports" />

        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Financial Reports</h1>
                <div class="flex gap-2">
                    <Link :href="route('reports.aging')" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                        Receivables Aging
                    </Link>
                    <Link :href="route('reports.payables-aging')" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm">
                        Payables Aging
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow mb-6 space-y-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <label class="text-sm font-medium mr-2">Period:</label>
                        <select v-model="selectedPeriod" class="border rounded px-3 py-1 dark:bg-gray-700">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_12_months">Last 12 Months</option>
                            <option value="all_time">All Time</option>
                        </select>
                    </div>
                    <div v-if="selectedPeriod === 'this_month'">
                        <label class="text-sm font-medium mr-2">Month:</label>
                        <input type="month" v-model="selectedMonth" class="border rounded px-3 py-1 dark:bg-gray-700" />
                    </div>
                    <div>
                        <label class="text-sm font-medium mr-2">Client:</label>
                        <select v-model="selectedClient" class="border rounded px-3 py-1 dark:bg-gray-700">
                            <option value="">All</option>
                            <option v-for="c in filterOptions.clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium mr-2">Supplier:</label>
                        <select v-model="selectedSupplier" class="border rounded px-3 py-1 dark:bg-gray-700">
                            <option value="">All</option>
                            <option v-for="s in filterOptions.suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <label class="text-sm font-medium mr-2">Income Category:</label>
                        <select v-model="selectedIncomeCategory" class="border rounded px-3 py-1 dark:bg-gray-700">
                            <option value="">All</option>
                            <option v-for="cat in filterOptions.incomeCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium mr-2">Expense Category:</label>
                        <select v-model="selectedExpenseCategory" class="border rounded px-3 py-1 dark:bg-gray-700">
                            <option value="">All</option>
                            <option v-for="cat in filterOptions.expenseCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
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
                    <div class="flex items-center gap-2 ml-auto">
                        <button @click="exportReport('csv')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded text-sm">
                            Export CSV
                        </button>
                        <button @click="exportReport('pdf')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded text-sm">
                            Export PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Income</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_income) }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Expenses</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_expenses) }}</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-blue-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Net Profit</p>
                    <p class="text-2xl font-bold" :class="summary.net_profit < 0 ? 'text-red-200' : 'text-green-200'">
                        {{ peso(summary.net_profit) }}
                    </p>
                </div>
            </div>

            <!-- ── Bar Charts (Replaces doughnuts) ── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Income by Category</h2>
                    <div style="height: 240px;"><canvas id="incomeBarChart"></canvas></div>
                    <div v-if="!income_by_category.length" class="text-center text-gray-500 py-4">No income data.</div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Expense by Category</h2>
                    <div style="height: 240px;"><canvas id="expenseBarChart"></canvas></div>
                    <div v-if="!expense_by_category.length" class="text-center text-gray-500 py-4">No expense data.</div>
                </div>
            </div>

            <!-- ── Monthly Income vs Expenses REMOVED ── -->

            <!-- Top Lists -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Top Clients (by Revenue)</h2>
                    <div v-if="top_clients.length">
                        <div v-for="c in top_clients" :key="c.name" class="flex justify-between border-b py-2">
                            <span>{{ c.name }}</span>
                            <span class="font-semibold">{{ peso(c.total) }}</span>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 py-2">No data.</div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Top Suppliers (by Expense)</h2>
                    <div v-if="top_suppliers.length">
                        <div v-for="s in top_suppliers" :key="s.name" class="flex justify-between border-b py-2">
                            <span>{{ s.name }}</span>
                            <span class="font-semibold">{{ peso(s.total) }}</span>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 py-2">No data.</div>
                </div>
            </div>

            <!-- Detailed Transactions -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                <h2 class="text-lg font-semibold mb-2">Recent Transactions</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left">Date</th>
                                <th class="py-2 text-left">Client / Supplier</th>
                                <th class="py-2 text-left">Particulars</th>
                                <th class="py-2 text-left">Amount</th>
                                <th class="py-2 text-left">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in transactions" :key="tx.date + tx.particulars" class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2">{{ tx.date }}</td>
                                <td>{{ tx.client || '-' }}</td>
                                <td>{{ tx.particulars || '-' }}</td>
                                <td :class="tx.type === 'Income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    {{ tx.type === 'Income' ? '+' : '−' }} {{ peso(tx.amount) }}
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium" :class="tx.type === 'Income' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400'">
                                        {{ tx.type }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!transactions || transactions.length === 0">
                                <td colspan="5" class="py-4 text-center text-gray-500">No transactions found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import { computed } from 'vue';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    show: Boolean,
    title: String,
    type: String,
    data: Object,
});

const emit = defineEmits(['close']);

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const content = computed(() => {
    switch (props.type) {
        case 'revenue':
            return {
                breakdown: props.data?.incomeByCategory || [],
                total: props.data?.totalRevenue || 0,
                label: 'by Category',
            };
        case 'expenses':
            return {
                breakdown: props.data?.expenseByCategory || [],
                total: props.data?.totalExpenses || 0,
                label: 'by Category',
            };
        case 'direct_costs':
            return {
                breakdown: props.data?.expenseByCategory || [],
                total: props.data?.totalDirectCosts || 0,
                label: 'by Category',
            };
        case 'gross_profit':
            return {
                revenue: props.data?.totalRevenue || 0,
                directCosts: props.data?.directCosts || 0,
                profit: props.data?.grossProfit || 0,
            };
        case 'operating_expenses':
            return {
                total: props.data?.totalOperatingExpenses || 0,
            };
        case 'operating_profit':
            return {
                grossProfit: props.data?.grossProfit || 0,
                operatingExpenses: props.data?.operatingExpenses || 0,
                profit: props.data?.operatingProfit || 0,
            };
        case 'net_profit':
            return {
                income: props.data?.totalRevenue || 0,
                expenses: props.data?.totalExpenses || 0,
                profit: props.data?.netProfit || 0,
            };
        case 'cash_balance':
            return {
                totalIncome: props.data?.totalIncomeAll || 0,
                totalExpenses: props.data?.totalExpensesAll || 0,
                balance: props.data?.cashBalance || 0,
            };
        case 'burn_rate':
            return {
                months: props.data?.months || [],
                total: props.data?.total || 0,
                avg: props.data?.burnRate || 0,
            };
        case 'cash_runaway':
            return {
                balance: props.data?.cashBalance || 0,
                burnRate: props.data?.burnRate || 0,
                runway: props.data?.cashRunaway || 0,
            };
        default:
            return null;
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="emit('close')">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h2 class="text-xl font-bold">{{ title }}</h2>
                    <button @click="emit('close')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-2xl">&times;</button>
                </div>

                <!-- Content based on type -->
                <div v-if="type === 'revenue' || type === 'expenses' || type === 'direct_costs'">
                    <div class="flex justify-between mb-3">
                        <span class="text-lg font-semibold">Total: {{ peso(content?.total) }}</span>
                        <span class="text-sm text-gray-500">{{ content?.label }}</span>
                    </div>
                    <div v-if="content?.breakdown?.length" class="space-y-2">
                        <div v-for="item in content.breakdown" :key="item.name" class="flex justify-between border-b py-1">
                            <span>{{ item.name || 'Uncategorized' }}</span>
                            <span class="font-semibold">{{ peso(item.value) }}</span>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-center py-4">No data available.</div>
                </div>

                <div v-else-if="type === 'gross_profit'">
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Revenue</p>
                            <p class="text-xl font-bold text-emerald-600">{{ peso(content?.revenue) }}</p>
                        </div>
                        <div class="bg-cyan-50 dark:bg-cyan-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Direct Costs</p>
                            <p class="text-xl font-bold text-cyan-600">{{ peso(content?.directCosts) }}</p>
                        </div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded text-center">
                        <p class="text-sm text-gray-500">Gross Profit</p>
                        <p class="text-2xl font-bold text-purple-600">{{ peso(content?.profit) }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 text-center">
                        {{ peso(content?.revenue) }} − {{ peso(content?.directCosts) }} = {{ peso(content?.profit) }}
                    </div>
                </div>

                <div v-else-if="type === 'operating_expenses'">
                    <div class="bg-amber-50 dark:bg-amber-900/20 p-3 rounded text-center">
                        <p class="text-sm text-gray-500">Total Operating Expenses</p>
                        <p class="text-2xl font-bold text-amber-600">{{ peso(content?.total) }}</p>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 text-center">Operating expenses are tracked under Miscellaneous transactions.</p>
                </div>

                <div v-else-if="type === 'operating_profit'">
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Gross Profit</p>
                            <p class="text-xl font-bold text-purple-600">{{ peso(content?.grossProfit) }}</p>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Operating Expenses</p>
                            <p class="text-xl font-bold text-amber-600">{{ peso(content?.operatingExpenses) }}</p>
                        </div>
                    </div>
                    <div class="bg-fuchsia-50 dark:bg-fuchsia-900/20 p-3 rounded text-center">
                        <p class="text-sm text-gray-500">Operating Profit</p>
                        <p class="text-2xl font-bold" :class="content?.profit >= 0 ? 'text-fuchsia-600' : 'text-red-600'">
                            {{ peso(content?.profit) }}
                        </p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 text-center">
                        {{ peso(content?.grossProfit) }} − {{ peso(content?.operatingExpenses) }} = {{ peso(content?.profit) }}
                    </div>
                </div>

                <div v-else-if="type === 'net_profit'">
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Income</p>
                            <p class="text-xl font-bold text-emerald-600">{{ peso(content?.income) }}</p>
                        </div>
                        <div class="bg-rose-50 dark:bg-rose-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Expenses</p>
                            <p class="text-xl font-bold text-rose-600">{{ peso(content?.expenses) }}</p>
                        </div>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded text-center">
                        <p class="text-sm text-gray-500">Net Profit</p>
                        <p class="text-2xl font-bold" :class="content?.profit >= 0 ? 'text-indigo-600' : 'text-red-600'">
                            {{ peso(content?.profit) }}
                        </p>
                    </div>
                </div>

                <div v-else-if="type === 'cash_balance'">
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Total Income (All Time)</p>
                            <p class="text-xl font-bold text-emerald-600">{{ peso(content?.totalIncome) }}</p>
                        </div>
                        <div class="bg-rose-50 dark:bg-rose-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Total Expenses (All Time)</p>
                            <p class="text-xl font-bold text-rose-600">{{ peso(content?.totalExpenses) }}</p>
                        </div>
                    </div>
                    <div class="bg-cyan-50 dark:bg-cyan-900/20 p-3 rounded text-center">
                        <p class="text-sm text-gray-500">Cash Balance</p>
                        <p class="text-2xl font-bold text-cyan-600">{{ peso(content?.balance) }}</p>
                    </div>
                </div>

                <div v-else-if="type === 'burn_rate'">
                    <div class="text-center mb-3">
                        <p class="text-sm text-gray-500">Average Monthly Expenses (Last 3 Months)</p>
                        <p class="text-2xl font-bold text-rose-600">{{ peso(content?.avg) }}</p>
                    </div>
                    <div v-if="content?.months?.length" class="space-y-1">
                        <div v-for="(month, idx) in content.months" :key="idx" class="flex justify-between border-b py-1">
                            <span>{{ month }}</span>
                            <span class="font-semibold">{{ peso(content.total / content.months.length) }}</span>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-center py-4">No monthly data available.</div>
                </div>

                <div v-else-if="type === 'cash_runaway'">
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="bg-cyan-50 dark:bg-cyan-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Cash Balance</p>
                            <p class="text-xl font-bold text-cyan-600">{{ peso(content?.balance) }}</p>
                        </div>
                        <div class="bg-rose-50 dark:bg-rose-900/20 p-3 rounded text-center">
                            <p class="text-sm text-gray-500">Burn Rate (Monthly)</p>
                            <p class="text-xl font-bold text-rose-600">{{ peso(content?.burnRate) }}</p>
                        </div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 p-3 rounded text-center">
                        <p class="text-sm text-gray-500">Cash Runway</p>
                        <p class="text-2xl font-bold text-amber-600">{{ content?.runway?.toFixed(1) ?? 0 }} months</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 text-center">
                        {{ peso(content?.balance) }} ÷ {{ peso(content?.burnRate) }} = {{ content?.runway?.toFixed(1) ?? 0 }} months
                    </div>
                </div>

                <div v-else class="text-gray-500 text-center py-4">No details available for this metric.</div>
            </div>
        </div>
    </Teleport>
</template>
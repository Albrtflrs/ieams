<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';
import { useDateFormat } from '@/composables/useDateFormat';
import { computed } from 'vue';

const props = defineProps({
    receivables: { type: Array, default: () => [] },
    payables: { type: Array, default: () => [] },
    totalReceivables: { type: Number, default: 0 },
    totalPayables: { type: Number, default: 0 },
    netPosition: { type: Number, default: 0 },
    receivableAging: { type: Object, default: () => ({ '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 }) },
    payableAging: { type: Object, default: () => ({ '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 }) },
});

const { currency } = useSettings();
const { formatDate } = useDateFormat();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const statusBadgeClass = (status) => {
    const map = {
        'Unpaid': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'Cash On Hold': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'Pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'Paid': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};

const agingBucketClass = (bucket) => {
    const map = {
        '0_30': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        '31_60': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        '61_90': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        '90_plus': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[bucket] || 'bg-gray-100 text-gray-800';
};

const receivableAgingSafe = computed(() => props.receivableAging || { '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 });
const payableAgingSafe = computed(() => props.payableAging || { '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 });

const agingLabels = {
    '0_30': '0-30 Days',
    '31_60': '31-60 Days',
    '61_90': '61-90 Days',
    '90_plus': '90+ Days',
};

const hasReceivableData = computed(() => Object.values(receivableAgingSafe.value).some(v => v > 0));
const hasPayableData = computed(() => Object.values(payableAgingSafe.value).some(v => v > 0));
</script>

<template>
    <AppLayout>
        <div class="p-4 md:p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Receivables &amp; Payables</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">Receivables &amp; Payables</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Track what customers owe you and what you owe suppliers</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('reports.aging')" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1.5 rounded text-sm transition">
                        📋 Receivables Aging
                    </Link>
                    <Link :href="route('reports.payables-aging')" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded text-sm transition">
                        📋 Payables Aging
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mb-4">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase tracking-wider opacity-80">Total Receivables</p>
                    <p class="text-xl md:text-2xl font-bold">{{ peso(totalReceivables) }}</p>
                    <p class="text-xs opacity-70 mt-1">What customers owe you</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase tracking-wider opacity-80">Total Payables</p>
                    <p class="text-xl md:text-2xl font-bold">{{ peso(totalPayables) }}</p>
                    <p class="text-xs opacity-70 mt-1">What you owe suppliers</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-cyan-600 text-white rounded-lg p-4 shadow">
                    <p class="text-xs uppercase tracking-wider opacity-80">Net Position</p>
                    <p class="text-xl md:text-2xl font-bold" :class="netPosition >= 0 ? 'text-green-200' : 'text-red-200'">
                        {{ peso(netPosition) }}
                    </p>
                    <p class="text-xs opacity-70 mt-1">{{ netPosition >= 0 ? 'Positive cash position' : 'Negative cash position' }}</p>
                </div>
            </div>

            <!-- Aging Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Receivables Aging -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            Receivables Aging
                        </h2>
                    </div>
                    <div class="p-3 divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="(amount, bucket) in receivableAgingSafe" :key="bucket" class="flex justify-between items-center py-1.5">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ agingLabels[bucket] }}</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="agingBucketClass(bucket)">
                                {{ peso(amount) }}
                            </span>
                        </div>
                        <div v-if="!hasReceivableData" class="text-sm text-gray-500 text-center py-2">
                            No aging data
                        </div>
                    </div>
                </div>

                <!-- Payables Aging -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Payables Aging
                        </h2>
                    </div>
                    <div class="p-3 divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="(amount, bucket) in payableAgingSafe" :key="bucket" class="flex justify-between items-center py-1.5">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ agingLabels[bucket] }}</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="agingBucketClass(bucket)">
                                {{ peso(amount) }}
                            </span>
                        </div>
                        <div v-if="!hasPayableData" class="text-sm text-gray-500 text-center py-2">
                            No aging data
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Receivables Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Unpaid Invoices</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ receivables.length }} items</span>
                    </div>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Client</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Amount</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="r in receivables.slice(0, 15)" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-3 py-1.5 max-w-[100px] truncate" :title="r.client?.name">{{ r.client?.name || '-' }}</td>
                                    <td class="px-3 py-1.5 font-semibold text-rose-600 dark:text-rose-400">{{ peso(r.amount_paid) }}</td>
                                    <td class="px-3 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="statusBadgeClass(r.status)">
                                            {{ r.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="receivables.length === 0">
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500 text-sm">No unpaid invoices</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="receivables.length > 15" class="px-3 py-1.5 text-xs text-gray-500 border-t border-gray-200 dark:border-gray-700 text-center">
                        Showing 15 of {{ receivables.length }}
                    </div>
                </div>

                <!-- Payables Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Unpaid Expenses</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ payables.length }} items</span>
                    </div>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Supplier</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Amount</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="p in payables.slice(0, 15)" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-3 py-1.5 max-w-[100px] truncate" :title="p.supplier?.name">{{ p.supplier?.name || '-' }}</td>
                                    <td class="px-3 py-1.5 font-semibold text-rose-600 dark:text-rose-400">{{ peso(p.amount) }}</td>
                                    <td class="px-3 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="statusBadgeClass(p.status)">
                                            {{ p.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="payables.length === 0">
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500 text-sm">No unpaid expenses</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="payables.length > 15" class="px-3 py-1.5 text-xs text-gray-500 border-t border-gray-200 dark:border-gray-700 text-center">
                        Showing 15 of {{ payables.length }}
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <div class="mt-4 text-xs text-gray-400 dark:text-gray-500 text-center border-t border-gray-200 dark:border-gray-700 pt-3">
                💡 Click the Aging Reports links above for detailed aging reports with export options.
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    receivables: Object,
    payables: Object,
    totalReceivables: Number,
    totalPayables: Number,
    netPosition: Number,
    receivableAging: Object,
    payableAging: Object,
    filters: Object,
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// ─── Status badge classes ──────────────────
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

const agingLabels = {
    '0_30': '0-30 Days',
    '31_60': '31-60 Days',
    '61_90': '61-90 Days',
    '90_plus': '90+ Days',
};

const getAgingBucket = (date) => {
    if (!date) return '0_30';
    const days = new Date().getTime() - new Date(date).getTime();
    const diffDays = days / (1000 * 60 * 60 * 24);
    if (diffDays <= 30) return '0_30';
    if (diffDays <= 60) return '31_60';
    if (diffDays <= 90) return '61_90';
    return '90_plus';
};

// ─── Filter state ──────────────────────────
const receivableFilters = ref({
    search: props.filters?.receivable_search || '',
    status: props.filters?.receivable_status || '',
    date_from: props.filters?.receivable_date_from || '',
    date_to: props.filters?.receivable_date_to || '',
});

const payableFilters = ref({
    search: props.filters?.payable_search || '',
    status: props.filters?.payable_status || '',
    date_from: props.filters?.payable_date_from || '',
    date_to: props.filters?.payable_date_to || '',
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
    const params = {
        receivable_search: receivableFilters.value.search || undefined,
        receivable_status: receivableFilters.value.status || undefined,
        receivable_date_from: receivableFilters.value.date_from || undefined,
        receivable_date_to: receivableFilters.value.date_to || undefined,
        payable_search: payableFilters.value.search || undefined,
        payable_status: payableFilters.value.status || undefined,
        payable_date_from: payableFilters.value.date_from || undefined,
        payable_date_to: payableFilters.value.date_to || undefined,
    };
    router.get(route('receivables-payables.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch(receivableFilters, applyFilters, { deep: true });
watch(payableFilters, applyFilters, { deep: true });

// ─── Reset all filters ──────────────────────
const resetFilters = () => {
    receivableFilters.value = { search: '', status: '', date_from: '', date_to: '' };
    payableFilters.value = { search: '', status: '', date_from: '', date_to: '' };
    applyFilters();
};

// ─── Helper: Outstanding amount for receivable ──
const outstanding = (receivable) => {
    return (receivable.gross_price || 0) - (receivable.amount_paid || 0);
};

// ─── Computed: aging data safe ──────────────
const receivableAgingSafe = computed(() => props.receivableAging || { '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 });
const payableAgingSafe = computed(() => props.payableAging || { '0_30': 0, '31_60': 0, '61_90': 0, '90_plus': 0 });
</script>

<template>
    <AppLayout>
        <div class="p-4 md:p-6">

            <!-- Breadcrumb with icon -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <svg class="w-4 h-4 inline mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-medium text-gray-700 dark:text-gray-300">Receivables &amp; Payables</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">Receivables &amp; Payables</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Track what customers owe you and what you owe suppliers</p>
                </div>
                <div class="flex gap-2">
                    <!-- Receivables Aging link with icon -->
                    <Link :href="route('reports.aging')" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1.5 rounded text-sm transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Receivables Aging
                    </Link>
                    <!-- Payables Aging link with icon -->
                    <Link :href="route('reports.payables-aging')" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded text-sm transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Payables Aging
                    </Link>
                    <button @click="resetFilters" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-3 py-1.5 rounded text-sm transition">
                        Reset Filters
                    </button>
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
                    </div>
                </div>
            </div>

            <!-- Tables with Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- ─── Receivables Table ────────────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Unpaid Invoices</h3>
                    </div>
                    <!-- Receivables Filters -->
                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-2">
                        <input
                            v-model="receivableFilters.search"
                            type="text"
                            placeholder="Search client..."
                            class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600 flex-1 min-w-[100px]"
                        />
                        <select v-model="receivableFilters.status" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600">
                            <option value="">All Status</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Cash On Hold">Cash On Hold</option>
                        </select>
                        <input type="date" v-model="receivableFilters.date_from" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600" />
                        <input type="date" v-model="receivableFilters.date_to" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Client</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Outstanding</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Aging</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="r in receivables.data" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer" @click="router.visit(route('income.edit', r.id))">
                                    <td class="px-3 py-1.5 max-w-[100px] truncate" :title="r.client?.name">{{ r.client?.name || '-' }}</td>
                                    <td class="px-3 py-1.5 font-semibold text-rose-600 dark:text-rose-400">{{ peso(outstanding(r)) }}</td>
                                    <td class="px-3 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-xs font-medium" :class="agingBucketClass(getAgingBucket(r.date_delivered))">
                                            {{ agingLabels[getAgingBucket(r.date_delivered)] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="statusBadgeClass(r.status)">
                                            {{ r.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="receivables.data?.length === 0">
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500 text-sm">No unpaid invoices</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 flex justify-between items-center">
                        <span>{{ receivables.total }} records</span>
                        <div class="flex gap-1">
                            <button v-for="link in receivables.links" :key="link.label"
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                    class="px-2 py-0.5 rounded border dark:border-gray-600"
                                    :class="{'bg-blue-500 text-white border-blue-500': link.active, 'text-gray-400 cursor-not-allowed pointer-events-none': !link.url}"
                            />
                        </div>
                    </div>
                </div>

                <!-- ─── Payables Table ─────────────────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Unpaid Expenses</h3>
                    </div>
                    <!-- Payables Filters -->
                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-2">
                        <input
                            v-model="payableFilters.search"
                            type="text"
                            placeholder="Search supplier..."
                            class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600 flex-1 min-w-[100px]"
                        />
                        <select v-model="payableFilters.status" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600">
                            <option value="">All Status</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Pending">Pending</option>
                        </select>
                        <input type="date" v-model="payableFilters.date_from" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600" />
                        <input type="date" v-model="payableFilters.date_to" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Supplier</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Amount</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Aging</th>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="p in payables.data" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer" @click="router.visit(route('expenses.edit', p.id))">
                                    <td class="px-3 py-1.5 max-w-[100px] truncate" :title="p.supplier?.name">{{ p.supplier?.name || '-' }}</td>
                                    <td class="px-3 py-1.5 font-semibold text-rose-600 dark:text-rose-400">{{ peso(p.amount) }}</td>
                                    <td class="px-3 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-xs font-medium" :class="agingBucketClass(getAgingBucket(p.date))">
                                            {{ agingLabels[getAgingBucket(p.date)] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="statusBadgeClass(p.status)">
                                            {{ p.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="payables.data?.length === 0">
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500 text-sm">No unpaid expenses</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 flex justify-between items-center">
                        <span>{{ payables.total }} records</span>
                        <div class="flex gap-1">
                            <button v-for="link in payables.links" :key="link.label"
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                    class="px-2 py-0.5 rounded border dark:border-gray-600"
                                    :class="{'bg-blue-500 text-white border-blue-500': link.active, 'text-gray-400 cursor-not-allowed pointer-events-none': !link.url}"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer note with lightbulb icon -->
            <div class="mt-4 text-xs text-gray-400 dark:text-gray-500 text-center border-t border-gray-200 dark:border-gray-700 pt-3 flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span>Click any row to edit the transaction. Use the Aging Reports links above for detailed reports.</span>
            </div>
        </div>
    </AppLayout>
</template>
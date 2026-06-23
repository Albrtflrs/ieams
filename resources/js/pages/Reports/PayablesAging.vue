<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    agingData: Array,
    asOf: String,
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const selectedDate = ref(props.asOf || new Date().toISOString().slice(0, 10));

const applyFilter = () => {
    router.get(route('reports.payables-aging'), { as_of: selectedDate.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const exportReport = (format) => {
    const url = format === 'csv'
        ? route('reports.payables-aging.export.csv', { as_of: selectedDate.value })
        : route('reports.payables-aging.export.pdf', { as_of: selectedDate.value });
    window.location.href = url;
};

watch(selectedDate, applyFilter);
</script>

<template>
    <AppLayout>
        <Head title="Payables Aging" />

        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Payables Aging Report</h1>
                <Link :href="route('reports.index')" class="text-blue-600 hover:underline">← Back to Reports</Link>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow mb-6 flex flex-wrap items-center gap-4">
                <div>
                    <label class="text-sm font-medium mr-2">As of Date:</label>
                    <input type="date" v-model="selectedDate" class="border rounded px-3 py-1 dark:bg-gray-700" />
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

            <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Supplier</th>
                            <th class="px-4 py-2 text-right">0–30 days</th>
                            <th class="px-4 py-2 text-right">31–60 days</th>
                            <th class="px-4 py-2 text-right">61–90 days</th>
                            <th class="px-4 py-2 text-right">90+ days</th>
                            <th class="px-4 py-2 text-right font-bold">Total Payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in agingData" :key="row.supplier + index"
                            :class="row.supplier === 'TOTAL' ? 'bg-gray-50 dark:bg-gray-700 font-bold' : 'border-b border-gray-100 dark:border-gray-800'">
                            <td class="px-4 py-2">{{ row.supplier }}</td>
                            <td class="px-4 py-2 text-right">{{ peso(row.bucket_0_30) }}</td>
                            <td class="px-4 py-2 text-right">{{ peso(row.bucket_31_60) }}</td>
                            <td class="px-4 py-2 text-right">{{ peso(row.bucket_61_90) }}</td>
                            <td class="px-4 py-2 text-right">{{ peso(row.bucket_90_plus) }}</td>
                            <td class="px-4 py-2 text-right font-bold">{{ peso(row.total) }}</td>
                        </tr>
                        <tr v-if="!agingData || agingData.length === 0">
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No payables found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
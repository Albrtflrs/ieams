<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';
import { useDateFormat } from '@/composables/useDateFormat';

const props = defineProps({
    quotation: Object,
});

const { currency } = useSettings();
const { formatDate } = useDateFormat();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const statusBadgeClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        accepted: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        expired: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        converted: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};

const printPage = () => window.print();
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-4xl mx-auto">

            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('quotations.index')" class="hover:underline">Quotations</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ quotation.quotation_number }}</span>
            </div>

            <div class="flex flex-wrap justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Quotation #{{ quotation.quotation_number }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Issued: {{ formatDate(quotation.date_issued) }}</p>
                </div>
                <div class="flex gap-2">
                    <button @click="printPage" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-sm transition">
                        🖨️ Print
                    </button>
                    <Link :href="route('quotations.edit', quotation.id)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-sm transition">
                        Edit
                    </Link>
                    <Link :href="route('quotations.index')" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-3 py-1.5 rounded text-sm transition">
                        ← Back
                    </Link>
                </div>
            </div>

            <!-- Client Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Client</p>
                        <p class="font-semibold">{{ quotation.client_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Client Address</p>
                        <p class="font-semibold">{{ quotation.client_address || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Valid Until</p>
                        <p class="font-semibold">{{ quotation.valid_until ? formatDate(quotation.valid_until) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                        <span class="px-2 py-1 rounded-full text-xs font-medium capitalize" :class="statusBadgeClass(quotation.status)">
                            {{ quotation.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-4">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cost Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Selling Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(item, idx) in quotation.items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2">{{ idx + 1 }}</td>
                            <td class="px-4 py-2">{{ item.description }}</td>
                            <td class="px-4 py-2">{{ item.quantity }}</td>
                            <td class="px-4 py-2">{{ peso(item.unit_price) }}</td>
                            <td class="px-4 py-2">{{ peso(item.selling_price) }}</td>
                            <td class="px-4 py-2 font-semibold">{{ peso(item.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4">
                <div class="flex justify-end space-x-8">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Subtotal</p>
                        <p class="font-semibold">{{ peso(quotation.subtotal) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Discount</p>
                        <p class="font-semibold">{{ peso(quotation.discount) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tax</p>
                        <p class="font-semibold">{{ peso(quotation.tax) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Amount</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ peso(quotation.total_amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="quotation.notes" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Notes</p>
                <p class="text-sm">{{ quotation.notes }}</p>
            </div>

            <div class="mt-4 text-xs text-gray-400 dark:text-gray-500 text-center border-t border-gray-200 dark:border-gray-700 pt-3">
                💡 Quotation #{{ quotation.quotation_number }} generated on {{ formatDate(quotation.created_at) }}
            </div>
        </div>
    </AppLayout>
</template>
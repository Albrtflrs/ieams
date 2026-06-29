<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    quotation: Object,
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// Helper to format date strings
const formatDate = (date) => {
    if (!date) return 'N/A';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

const formatDateTime = (date) => {
    if (!date) return 'N/A';
    const d = new Date(date);
    return d.toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

// Export functions
const exportPDF = () => {
    window.open(route('quotations.export.pdf', props.quotation.id), '_blank');
};

const exportCSV = () => {
    window.open(route('quotations.export.csv', props.quotation.id), '_blank');
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <!-- Breadcrumbs -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('quotations.index')" class="hover:underline">Quotations</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">View</span>
            </div>

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Quotation #{{ quotation.quotation_number }}
                </h1>
                <div class="flex gap-2">
                    <Link
                        :href="route('quotations.edit', quotation.id)"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition"
                    >
                        Edit
                    </Link>
                    <button
                        @click="exportPDF"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition"
                    >
                        PDF
                    </button>
                    <button
                        @click="exportCSV"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition"
                    >
                        CSV
                    </button>
                    <Link
                        :href="route('quotations.index')"
                        class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded transition"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <!-- Quotation Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4">
                <!-- Header Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 border-b pb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Number</label>
                        <p class="font-semibold">{{ quotation.quotation_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Date Issued</label>
                        <p>{{ formatDate(quotation.date_issued) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Valid Until</label>
                        <p>{{ formatDate(quotation.valid_until) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                              :class="{
                                  'bg-gray-100 text-gray-800': quotation.status === 'draft',
                                  'bg-blue-100 text-blue-800': quotation.status === 'sent',
                                  'bg-green-100 text-green-800': quotation.status === 'accepted',
                                  'bg-red-100 text-red-800': quotation.status === 'rejected',
                                  'bg-yellow-100 text-yellow-800': quotation.status === 'expired',
                                  'bg-purple-100 text-purple-800': quotation.status === 'converted',
                              }">
                            {{ quotation.status }}
                        </span>
                    </div>
                </div>

                <!-- Client -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Client</label>
                    <p class="font-medium">{{ quotation.client_name }}</p>
                    <p v-if="quotation.client_address" class="text-sm text-gray-600 dark:text-gray-400">{{ quotation.client_address }}</p>
                </div>

                <!-- Items Table -->
                <div>
                    <h3 class="text-lg font-semibold mb-2">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Description</th>
                                    <th class="px-4 py-2 text-right">Qty</th>
                                    <th class="px-4 py-2 text-right">Unit Price</th>
                                    <th class="px-4 py-2 text-right">Selling Price</th>
                                    <th class="px-4 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in quotation.items" :key="item.id" class="border-b">
                                    <td class="px-4 py-2">{{ item.description }}</td>
                                    <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                    <td class="px-4 py-2 text-right">{{ peso(item.unit_price) }}</td>
                                    <td class="px-4 py-2 text-right">{{ peso(item.selling_price) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">{{ peso(item.total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Subtotal</td>
                                    <td class="px-4 py-2 text-right">{{ peso(quotation.subtotal) }}</td>
                                </tr>
                                <tr v-if="quotation.discount > 0">
                                    <td colspan="4" class="px-4 py-2 text-right">Discount ({{ quotation.discount }}%)</td>
                                    <td class="px-4 py-2 text-right">-{{ peso(quotation.subtotal * quotation.discount / 100) }}</td>
                                </tr>
                                <tr v-if="quotation.tax > 0">
                                    <td colspan="4" class="px-4 py-2 text-right">Tax ({{ quotation.tax }}%)</td>
                                    <td class="px-4 py-2 text-right">{{ peso((quotation.subtotal - (quotation.subtotal * quotation.discount / 100)) * quotation.tax / 100) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Total Amount</td>
                                    <td class="px-4 py-2 text-right">{{ peso(quotation.total_amount) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="quotation.notes">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                    <p class="text-sm">{{ quotation.notes }}</p>
                </div>

                <!-- Created By -->
                <div class="text-xs text-gray-400 border-t pt-2">
                    Created by {{ quotation.created_by }} on {{ formatDateTime(quotation.created_at) }}
                </div>
            </div>
        </div>
    </AppLayout>
</template>
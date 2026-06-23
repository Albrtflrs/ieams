<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    quotation: Object,
    clients: Array,
});

const { currency } = useSettings();

const form = useForm({
    client_id: props.quotation.client_id,
    client_name: props.quotation.client_name,
    client_address: props.quotation.client_address,
    date_issued: props.quotation.date_issued,
    valid_until: props.quotation.valid_until || '',
    currency: props.quotation.currency || '₱',
    subtotal: props.quotation.subtotal,
    discount: props.quotation.discount,
    tax: props.quotation.tax,
    total_amount: props.quotation.total_amount,
    markup_percentage: props.quotation.markup_percentage,
    markup_amount: props.quotation.markup_amount,
    status: props.quotation.status,
    notes: props.quotation.notes || '',
    items: props.quotation.items.map(item => ({
        id: item.id,
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        selling_price: item.selling_price,
        discount: item.discount || 0,
        total: item.total,
        category: item.category || '',
        markup_percentage: item.markup_percentage || 0,
        markup_amount: item.markup_amount || 0,
    })),
});

const addItem = () => {
    form.items.push({
        description: '',
        quantity: 1,
        unit_price: 0,
        selling_price: 0,
        discount: 0,
        total: 0,
        category: '',
        markup_percentage: 0,
        markup_amount: 0,
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
    recalculateTotals();
};

const recalculateTotals = () => {
    let subtotal = 0;
    form.items.forEach(item => {
        if (item.unit_price > 0 && item.markup_percentage > 0) {
            const markupAmount = item.unit_price * (item.markup_percentage / 100);
            item.markup_amount = markupAmount;
            item.selling_price = item.unit_price + markupAmount;
        } else if (item.selling_price > 0) {
            const diff = item.selling_price - item.unit_price;
            item.markup_amount = diff > 0 ? diff : 0;
            item.markup_percentage = item.unit_price > 0 ? (diff / item.unit_price) * 100 : 0;
        }
        const total = (item.selling_price || 0) * item.quantity;
        item.total = total;
        subtotal += total;
    });
    form.subtotal = subtotal;
    form.markup_amount = subtotal - form.items.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
    form.total_amount = subtotal - form.discount + form.tax;
};

const updateItem = (index) => recalculateTotals();

const submit = () => {
    if (!form.client_id && !form.client_name) {
        alert('Please select a client or enter client name.');
        return;
    }
    if (form.items.length === 0) {
        alert('Add at least one item.');
        return;
    }
    form.put(route('quotations.update', props.quotation.id));
};
</script>

<template>
    <AppLayout>
        <Head title="Edit Quotation" />

        <div class="p-6 max-w-5xl mx-auto">

            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('quotations.index')" class="hover:underline">Quotations</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Edit</span>
            </div>

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Quotation</h1>
                <Link :href="route('quotations.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">

                <!-- Client Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                        <select v-model="form.client_id" @change="form.client_name = form.client_id ? clients.find(c => c.id == form.client_id)?.name || '' : ''" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                            <option value="">Select Client</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Name</label>
                        <input v-model="form.client_name" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Address</label>
                        <textarea v-model="form.client_address" rows="2" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quotation Number</label>
                        <input :value="props.quotation.quotation_number" class="w-full border rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:border-gray-600" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Issued</label>
                        <input type="date" v-model="form.date_issued" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valid Until</label>
                        <input type="date" v-model="form.valid_until" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                </div>

                <!-- Items Table (same as Create) -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Items</label>
                        <button type="button" @click="addItem" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm">
                            + Add Item
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-2 py-1 text-left">Description</th>
                                    <th class="px-2 py-1 text-left">Qty</th>
                                    <th class="px-2 py-1 text-left">Cost Price</th>
                                    <th class="px-2 py-1 text-left">Markup %</th>
                                    <th class="px-2 py-1 text-left">Selling Price</th>
                                    <th class="px-2 py-1 text-left">Total</th>
                                    <th class="px-2 py-1 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.items" :key="index" class="border-b">
                                    <td class="px-2 py-1">
                                        <input v-model="item.description" class="w-full border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600" placeholder="Description" />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" min="1" v-model="item.quantity" @input="updateItem(index)" class="w-16 border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600" />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" step="0.01" v-model="item.unit_price" @input="updateItem(index)" class="w-24 border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600" />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" step="0.1" v-model="item.markup_percentage" @input="updateItem(index)" class="w-16 border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600" />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" step="0.01" v-model="item.selling_price" @input="updateItem(index)" class="w-24 border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600" />
                                    </td>
                                    <td class="px-2 py-1 font-semibold">{{ currency }}{{ item.total.toFixed(2) }}</td>
                                    <td class="px-2 py-1">
                                        <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">✕</button>
                                    </td>
                                </tr>
                                <tr v-if="form.items.length === 0">
                                    <td colspan="7" class="px-2 py-4 text-center text-gray-500 text-sm">No items added.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t dark:border-gray-700">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</label>
                        <input :value="currency + form.subtotal.toFixed(2)" class="w-full border rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount</label>
                        <input type="number" step="0.01" v-model="form.discount" @input="recalculateTotals" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax</label>
                        <input type="number" step="0.01" v-model="form.tax" @input="recalculateTotals" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Amount</label>
                        <input :value="currency + form.total_amount.toFixed(2)" class="w-full border rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 font-bold" readonly />
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                        <option value="converted">Converted</option>
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                        Update Quotation
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
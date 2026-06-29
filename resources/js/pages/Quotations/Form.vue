<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSettings } from '@/composables/useSettings';

// ─── Props ──────────────────────────────
const props = defineProps({
    clients: Array,
    items: Array,
    quotation_number: String,
    default_currency: String,
    user_role: String,
    categories: Array,
    quotation: {
        type: Object,
        default: null,
    },
});

const { currency } = useSettings();
const isEdit = computed(() => !!props.quotation);

// ─── Form initialisation ──────────────────
const form = useForm({
    client_id: props.quotation?.client_id ?? '',
    client_name: props.quotation?.client_name ?? '',
    client_address: props.quotation?.client_address ?? '',
    date_issued: props.quotation?.date_issued ?? new Date().toISOString().slice(0, 10),
    valid_until: props.quotation?.valid_until ?? '',
    currency: (props.quotation?.currency ?? props.default_currency) || '₱',
    subtotal: props.quotation?.subtotal ?? 0,
    discount: props.quotation?.discount ?? 0,
    tax: props.quotation?.tax ?? 0,
    total_amount: props.quotation?.total_amount ?? 0,
    markup_percentage: props.quotation?.markup_percentage ?? 0,
    markup_amount: props.quotation?.markup_amount ?? 0,
    status: props.quotation?.status ?? 'draft',
    notes: props.quotation?.notes ?? '',
    items: props.quotation?.items?.map(item => ({
        id: item.id ?? null,
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        selling_price: item.selling_price,
        discount: item.discount ?? 0,
        total: item.total,
        category: item.category ?? '',
        markup_percentage: item.markup_percentage ?? 0,
        markup_amount: item.markup_amount ?? 0,
        selected_item_id: item.item_id ?? null,
    })) ?? [],
});

// ─── Helper: fill client fields when selection changes ──
const updateClientFields = () => {
    if (form.client_id) {
        const client = props.clients.find(c => c.id == form.client_id);
        if (client) {
            form.client_name = client.name;
            form.client_address = client.address || '';
        }
    } else {
        form.client_name = '';
        form.client_address = '';
    }
};

// ─── Permissions ──────────────────────────
const canAddItems = computed(() => ['super_admin', 'admin'].includes(props.user_role));

// ─── Item helpers ──────────────────────────
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
        selected_item_id: null,
    });
    recalculateTotals();
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

    const discountPercent = parseFloat(form.discount) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const taxPercent = parseFloat(form.tax) || 0;
    const taxable = subtotal - discountAmount;
    const taxAmount = taxable * (taxPercent / 100);

    form.total_amount = taxable + taxAmount;
};

const updateItem = (index) => recalculateTotals();

// ─── Item selection from catalog ────────────
const selectItem = (index, itemId) => {
    if (!itemId) return;
    const selected = props.items.find(i => i.id == itemId);
    if (selected) {
        const item = form.items[index];
        item.description = selected.name;
        item.unit_price = selected.default_cost_price || 0;
        item.markup_percentage = selected.default_markup_percentage || 0;
        item.selling_price = selected.default_selling_price || 0;
        if (selected.category) {
            item.category = selected.category;
        }
        updateItem(index);
    }
};

// ─── Submit ───────────────────────────
const submit = () => {
    if (!form.client_id && !form.client_name) {
        alert('Please select a client or enter client name.');
        return;
    }
    if (form.items.length === 0) {
        alert('Add at least one item.');
        return;
    }

    const url = isEdit.value
        ? route('quotations.update', props.quotation.id)
        : route('quotations.store');

    const method = isEdit.value ? 'put' : 'post';

    const payload = {
        client_id: form.client_id,
        client_name: form.client_name,
        client_address: form.client_address,
        date_issued: form.date_issued,
        valid_until: form.valid_until,
        currency: form.currency,
        subtotal: form.subtotal,
        discount: form.discount,
        tax: form.tax,
        total_amount: form.total_amount,
        markup_percentage: form.markup_percentage,
        markup_amount: form.markup_amount,
        status: form.status,
        notes: form.notes,
        items: form.items.map(({ selected_item_id, ...rest }) => rest),
    };

    form[method](url, payload);
};
</script>

<template>
    <AppLayout>
        <Head :title="isEdit ? 'Edit Quotation' : 'New Quotation'" />

        <div class="p-6 max-w-5xl mx-auto">
            <!-- Breadcrumbs -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('quotations.index')" class="hover:underline">Quotations</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ isEdit ? 'Edit' : 'Create' }}
                </span>
            </div>

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ isEdit ? 'Edit Quotation' : 'New Quotation' }}
                </h1>
                <Link :href="route('quotations.index')" class="text-gray-600 dark:text-gray-400 hover:underline">
                    ← Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">

                <!-- Client Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                        <select
                            v-model="form.client_id"
                            @change="updateClientFields"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        >
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
                        <input :value="quotation_number" class="w-full border rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:border-gray-600" readonly />
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

                <!-- Items Table -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Items</label>
                        <button
                            type="button"
                            @click="addItem"
                            class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm transition"
                            :disabled="!canAddItems"
                            :class="!canAddItems ? 'opacity-50 cursor-not-allowed' : ''"
                            :title="!canAddItems ? 'Only Admin can add new items' : 'Add a new item row'"
                        >
                            + Add Item
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-2 py-1 text-left">Item</th>
                                    <th class="px-2 py-1 text-left">Qty</th>
                                    <th class="px-2 py-1 text-left">Cost Price</th>
                                    <th class="px-2 py-1 text-left">Markup %</th>
                                    <th class="px-2 py-1 text-left">Selling Price</th>
                                    <th class="px-2 py-1 text-left">Category</th>
                                    <th class="px-2 py-1 text-left">Total</th>
                                    <th class="px-2 py-1 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.items" :key="index" class="border-b">
                                    <td class="px-2 py-1">
                                        <select
                                            v-model="item.selected_item_id"
                                            @change="selectItem(index, item.selected_item_id)"
                                            class="w-full border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600"
                                        >
                                            <option value="">Select Item</option>
                                            <option v-for="i in props.items" :key="i.id" :value="i.id">
                                                {{ i.name }}
                                            </option>
                                        </select>
                                        <input
                                            v-if="canAddItems && !item.selected_item_id"
                                            v-model="item.description"
                                            @input="updateItem(index)"
                                            class="w-full border rounded px-2 py-1 mt-1 dark:bg-gray-700 dark:border-gray-600 text-xs"
                                            placeholder="Type custom description"
                                        />
                                        <div v-else-if="item.description" class="text-xs text-gray-500 mt-1 truncate" :title="item.description">
                                            {{ item.description }}
                                        </div>
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
                                    <td class="px-2 py-1">
                                        <select
                                            v-model="item.category"
                                            @change="updateItem(index)"
                                            class="w-full border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600"
                                        >
                                            <option value="">Select Category</option>
                                            <option v-for="cat in props.categories" :key="cat" :value="cat">
                                                {{ cat }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-1 font-semibold">{{ currency }}{{ item.total.toFixed(2) }}</td>
                                    <td class="px-2 py-1">
                                        <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">✕</button>
                                    </td>
                                </tr>
                                <tr v-if="form.items.length === 0">
                                    <td colspan="8" class="px-2 py-4 text-center text-gray-500 text-sm">No items added.</td>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount %</label>
                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            max="100"
                            v-model="form.discount"
                            @input="recalculateTotals"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax %</label>
                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            max="100"
                            v-model="form.tax"
                            @input="recalculateTotals"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        />
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
                        {{ isEdit ? 'Update Quotation' : 'Save Quotation' }}
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
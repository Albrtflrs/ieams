<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import MunicipalityBarangaySelect from '@/Components/MunicipalityBarangaySelect.vue';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    transaction: Object,
    clients: Array,
    municipalities: Array,
    categories: Array,
});

const { currency } = useSettings();

// Pre‑fill the form with existing transaction data
const form = useForm({
    item_no: props.transaction.item_no || '',
    client_id: props.transaction.client_id || '',
    agency_department: props.transaction.agency_department || '',
    municipal_barangay: props.transaction.municipal_barangay || '',
    particulars: props.transaction.particulars || '',
    date_delivered: props.transaction.date_delivered || '',
    amount_paid: props.transaction.amount_paid || 0,
    date_paid: props.transaction.date_paid || '',
    receipt_number: props.transaction.receipt_number || '',
    gross_price: props.transaction.gross_price || 0,
    royalty_percent: props.transaction.royalty_percent || 0,
    deductions: props.transaction.deductions || 0,
    category: props.transaction.category || '',
    withdrawn: props.transaction.withdrawn || false,
    status: props.transaction.status || 'Unpaid',
    remarks: props.transaction.remarks || '',
    is_miscellaneous: props.transaction.is_miscellaneous || false,
});

// Pre‑fill location from the existing string (split by comma)
const locationValue = computed({
    get: () => {
        const parts = form.municipal_barangay.split(',').map(s => s.trim());
        return { municipality: parts[0] || '', barangay: parts[1] || '' };
    },
    set: (val) => {
        form.municipal_barangay = [val.municipality, val.barangay]
            .filter(Boolean)
            .join(', ');
    },
});

const submit = () => {
    form.put(route('income.update', props.transaction.id));
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-2xl">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('income.index')" class="hover:underline">Income</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Edit</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Edit Income</h1>
                <Link :href="route('income.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <!-- ─── Global Error Alert ─────────────────────────────── -->
            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                <p class="text-red-600 dark:text-red-400 font-medium">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-500 dark:text-red-300 mt-1">
                    <li v-for="(error, field) in form.errors" :key="field">
                        {{ field }}: {{ error }}
                    </li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                <!-- Item No. – auto-generated, read-only -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">
                        Item No. <span class="text-xs text-gray-500">(auto-generated)</span>
                    </label>
                    <input v-model="form.item_no" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 cursor-not-allowed" disabled />
                </div>

                <!-- Client -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Client</label>
                    <select v-model="form.client_id" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Select Client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <div v-if="form.errors.client_id" class="text-red-500 text-sm mt-1">{{ form.errors.client_id }}</div>
                </div>

                <!-- Agency / Department -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Agency / Department *</label>
                    <input v-model="form.agency_department" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required />
                    <div v-if="form.errors.agency_department" class="text-red-500 text-sm mt-1">{{ form.errors.agency_department }}</div>
                </div>

                <!-- Municipal / Barangay -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Municipal / Barangay</label>
                    <MunicipalityBarangaySelect :municipalities="municipalities" v-model="locationValue" />
                    <div v-if="form.errors.municipal_barangay" class="text-red-500 text-sm mt-1">{{ form.errors.municipal_barangay }}</div>
                </div>

                <!-- Particulars -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Particulars *</label>
                    <textarea v-model="form.particulars" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" rows="2" required></textarea>
                    <div v-if="form.errors.particulars" class="text-red-500 text-sm mt-1">{{ form.errors.particulars }}</div>
                </div>

                <!-- Date Delivered -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Date Delivered</label>
                    <input v-model="form.date_delivered" type="date" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.date_delivered" class="text-red-500 text-sm mt-1">{{ form.errors.date_delivered }}</div>
                </div>

                <!-- Amount Paid & Date Paid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300">Amount Paid ({{ currency }})</label>
                        <input v-model.number="form.amount_paid" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                        <div v-if="form.errors.amount_paid" class="text-red-500 text-sm mt-1">{{ form.errors.amount_paid }}</div>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300">Date Paid</label>
                        <input v-model="form.date_paid" type="date" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                        <div v-if="form.errors.date_paid" class="text-red-500 text-sm mt-1">{{ form.errors.date_paid }}</div>
                    </div>
                </div>

                <!-- Receipt Number -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">
                        Receipt Number
                        <span v-if="form.status === 'Paid'" class="text-red-500 text-xs">* required when status is Paid</span>
                    </label>
                    <input v-model="form.receipt_number" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.receipt_number" class="text-red-500 text-sm mt-1">{{ form.errors.receipt_number }}</div>
                </div>

                <!-- Gross Price, Royalty, Deductions -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Gross Price ({{ currency }}) *</label>
                    <input v-model.number="form.gross_price" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required />
                    <div v-if="form.errors.gross_price" class="text-red-500 text-sm mt-1">{{ form.errors.gross_price }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300">Royalty (%)</label>
                        <input v-model.number="form.royalty_percent" type="number" step="0.1" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                        <div v-if="form.errors.royalty_percent" class="text-red-500 text-sm mt-1">{{ form.errors.royalty_percent }}</div>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300">Deductions ({{ currency }})</label>
                        <input v-model.number="form.deductions" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                        <div v-if="form.errors.deductions" class="text-red-500 text-sm mt-1">{{ form.errors.deductions }}</div>
                    </div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Category *</label>
                    <select v-model="form.category" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required>
                        <option value="">Select Category</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <div v-if="form.errors.category" class="text-red-500 text-sm mt-1">{{ form.errors.category }}</div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Status *</label>
                    <select v-model="form.status" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600">
                        <option>Paid</option>
                        <option>Unpaid</option>
                        <option>Cash On Hold</option>
                        <option>Paid Royalty</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                </div>

                <!-- Checkboxes -->
                <div class="flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                        <input type="checkbox" v-model="form.withdrawn" />
                        Withdrawn (Done)
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                        <input type="checkbox" v-model="form.is_miscellaneous" />
                        Miscellaneous Income
                    </label>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300">Remarks</label>
                    <textarea v-model="form.remarks" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" rows="2"></textarea>
                    <div v-if="form.errors.remarks" class="text-red-500 text-sm mt-1">{{ form.errors.remarks }}</div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                        Update
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
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

            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                <!-- Item No. (auto‑generated, displayed as plain text) -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Auto‑generated item number.">
                        Item No.
                    </label>
                    <input v-model="form.item_no" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" disabled />
                </div>

                <!-- Client -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Select the client or agency for this transaction.">
                        Client
                    </label>
                    <select v-model="form.client_id" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Select Client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <!-- Agency / Department -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Enter the agency or department name (if different from client).">
                        Agency / Department *
                    </label>
                    <input v-model="form.agency_department" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required />
                </div>

                <!-- Municipal / Barangay -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1" title="Select the municipality and barangay where the service/product was delivered.">
                        Municipal / Barangay
                    </label>
                    <MunicipalityBarangaySelect
                        :municipalities="municipalities"
                        v-model="locationValue"
                    />
                </div>

                <!-- Particulars -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Detailed description of the income transaction.">
                        Particulars *
                    </label>
                    <textarea v-model="form.particulars" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" rows="2" required></textarea>
                </div>

                <!-- Payment fields (only in edit) -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Date when the goods/services were delivered.">
                        Date Delivered
                    </label>
                    <input v-model="form.date_delivered" type="date" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300" :title="`Amount actually paid by the client (${currency}).`">
                            Amount Paid ({{ currency }})
                        </label>
                        <input v-model.number="form.amount_paid" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300" title="Date when the payment was received.">
                            Date Paid
                        </label>
                        <input v-model="form.date_paid" type="date" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Official receipt number for the payment.">
                        Receipt Number
                    </label>
                    <input v-model="form.receipt_number" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <!-- Gross Price, Royalty, Deductions -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" :title="`Total amount before deductions and royalty (${currency}).`">
                        Gross Price ({{ currency }}) *
                    </label>
                    <input v-model.number="form.gross_price" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300" title="Percentage of gross price to be paid as royalty.">
                            Royalty (%)
                        </label>
                        <input v-model.number="form.royalty_percent" type="number" step="0.1" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300" :title="`Amount to be deducted from gross price (${currency}).`">
                            Deductions ({{ currency }})
                        </label>
                        <input v-model.number="form.deductions" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" />
                    </div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Select the category that best describes this income.">
                        Category *
                    </label>
                    <select v-model="form.category" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required>
                        <option value="">Select Category</option>
                        <option>CCTV AND SUPPLIES</option>
                        <option>OFFICE SUPPLIES</option>
                        <option>IT EQUIPMENT</option>
                        <option>SOFTWARE</option>
                        <option>ELECTRONICS/AIRCON</option>
                        <option>FURNITURE</option>
                        <option>KITCHENWARE</option>
                        <option>SOLAR</option>
                        <option>OTHERS</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Current payment status of this transaction.">
                        Status *
                    </label>
                    <select v-model="form.status" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600">
                        <option>Paid</option>
                        <option>Unpaid</option>
                        <option>Cash On Hold</option>
                        <option>Paid Royalty</option>
                    </select>
                </div>

                <!-- Checkboxes -->
                <div class="flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300" title="Check if the amount has been withdrawn.">
                        <input type="checkbox" v-model="form.withdrawn" />
                        Withdrawn (Done)
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300" title="Check if this is a miscellaneous income (not regular sales).">
                        <input type="checkbox" v-model="form.is_miscellaneous" />
                        Miscellaneous Income
                    </label>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Additional notes or comments about this transaction.">
                        Remarks
                    </label>
                    <textarea v-model="form.remarks" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" rows="2"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                        Update
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 px-4 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
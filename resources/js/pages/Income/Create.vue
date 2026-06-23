<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';
import MunicipalityBarangaySelect from '@/Components/MunicipalityBarangaySelect.vue';
import { computed } from 'vue';

const props = defineProps({
    clients: Array,
    municipalities: Array,
    categories: Array,
    default_royalty_rate: Number,
});

const { currency } = useSettings();

const form = useForm({
    client_id: '',
    agency_department: '',
    municipal_barangay: '',
    particulars: '',
    gross_price: 0,
    royalty_percent: props.default_royalty_rate ?? 0,
    deductions: 0,
    category: '',
    withdrawn: false,
    status: 'Unpaid',
    remarks: '',
    is_miscellaneous: false,
});

const locationValue = computed({
    get: () => ({ municipality: '', barangay: '' }),
    set: (val) => {
        form.municipal_barangay = [val.municipality, val.barangay].filter(Boolean).join(', ');
    },
});

const submit = () => form.post(route('income.store'));
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
                <span class="font-medium text-gray-700 dark:text-gray-300">Create</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Add Income</h1>
                <Link :href="route('income.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
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
                    <MunicipalityBarangaySelect :municipalities="municipalities" v-model="locationValue" />
                </div>

                <!-- Particulars -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" title="Detailed description of the income transaction.">
                        Particulars *
                    </label>
                    <textarea v-model="form.particulars" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" rows="2" required></textarea>
                </div>

                <!-- Gross Price -->
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300" :title="`Total amount before deductions and royalty (${currency}).`">
                        Gross Price ({{ currency }}) *
                    </label>
                    <input v-model.number="form.gross_price" type="number" step="0.01" class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600" required />
                </div>

                <!-- Royalty & Deductions -->
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
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
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
                    <button type="submit" :disabled="form.processing" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg transition">
                        Save
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 px-4 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    suppliers: Array,
});

const { currency } = useSettings();

const form = useForm({
    supplier_id: null,
    date: new Date().toISOString().slice(0, 10),
    amount: '',
    category: '',
    description: '',
    receipt_number: '',
    payment_method: '',
    is_miscellaneous: false,
    status: 'Unpaid', // 👈 NEW
});

function submit() {
    form.post(route('expenses.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="New Expense" />

        <div class="p-6 max-w-2xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('expenses.index')" class="hover:underline">Expenses</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Create</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Expense</h1>
                <Link :href="route('expenses.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">
                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Select the supplier for this expense.">
                        Supplier
                    </label>
                    <select v-model="form.supplier_id" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option :value="null">None</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <div v-if="form.errors.supplier_id" class="text-red-500 text-sm">{{ form.errors.supplier_id }}</div>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Date of the expense.">
                        Date
                    </label>
                    <input type="date" v-model="form.date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.date" class="text-red-500 text-sm">{{ form.errors.date }}</div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" :title="`Amount of the expense (${currency}).`">
                        Amount ({{ currency }})
                    </label>
                    <input type="number" step="0.01" v-model="form.amount" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm">{{ form.errors.amount }}</div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Select a category that best describes this expense.">
                        Category
                    </label>
                    <input type="text" v-model="form.category" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.category" class="text-red-500 text-sm">{{ form.errors.category }}</div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Detailed description of the expense.">
                        Description
                    </label>
                    <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</div>
                </div>

                <!-- Receipt Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Official receipt number for this expense.">
                        Receipt Number
                    </label>
                    <input type="text" v-model="form.receipt_number" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="How the expense was paid (e.g., cash, bank transfer).">
                        Payment Method
                    </label>
                    <input type="text" v-model="form.payment_method" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <!-- Status 👈 NEW -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Payment status of this expense.">
                        Status *
                    </label>
                    <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="Unpaid">Unpaid</option>
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm">{{ form.errors.status }}</div>
                </div>

                <!-- Miscellaneous Checkbox -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.is_miscellaneous" id="misc" />
                    <label for="misc" class="text-sm text-gray-700 dark:text-gray-300" title="Check if this is a miscellaneous expense (not regular recurring).">
                        Miscellaneous expense
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg transition">
                        Save
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
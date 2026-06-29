<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    expense: Object,
    suppliers: Array,
    categories: Array, // now passed from controller
});

const { currency } = useSettings();

const form = useForm({
    supplier_id: props.expense.supplier_id,
    date: props.expense.date,
    amount: props.expense.amount,
    category: props.expense.category,
    description: props.expense.description || '',
    receipt_number: props.expense.receipt_number || '',
    payment_method: props.expense.payment_method || '',
    is_miscellaneous: props.expense.is_miscellaneous || false,
    status: props.expense.status || 'Unpaid',
});

function submit() {
    form.put(route('expenses.update', props.expense.id));
}
</script>

<template>
    <AppLayout>
        <Head title="Edit Expense" />

        <div class="p-6 max-w-2xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('expenses.index')" class="hover:underline">Expenses</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Edit</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Expense</h1>
                <Link :href="route('expenses.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <select v-model="form.supplier_id" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option :value="null">None</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <div v-if="form.errors.supplier_id" class="text-red-500 text-sm">{{ form.errors.supplier_id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input type="date" v-model="form.date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.date" class="text-red-500 text-sm">{{ form.errors.date }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" :title="`Amount of the expense (${currency}).`">
                        Amount ({{ currency }})
                    </label>
                    <input type="number" step="0.01" v-model="form.amount" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm">{{ form.errors.amount }}</div>
                </div>

                <!-- Category dropdown with new categories -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select v-model="form.category" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Select Category</option>
                        <option v-for="cat in props.categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <div v-if="form.errors.category" class="text-red-500 text-sm">{{ form.errors.category }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt Number</label>
                    <input type="text" v-model="form.receipt_number" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                    <input type="text" v-model="form.payment_method" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                    <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="Unpaid">Unpaid</option>
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm">{{ form.errors.status }}</div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.is_miscellaneous" id="misc" />
                    <label for="misc" class="text-sm text-gray-700 dark:text-gray-300">Miscellaneous expense</label>
                </div>

                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg transition">
                        Update
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
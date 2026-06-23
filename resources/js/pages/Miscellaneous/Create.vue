<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    transaction: Object,
});

const { currency } = useSettings();

const form = useForm({
    type: props.transaction?.type || 'income',
    date: new Date().toISOString().slice(0, 10),
    amount: '',
    category: '',
    description: '',
    reference_number: '',
});

function submit() {
    form.post(route('misc.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="New Miscellaneous" />

        <div class="p-6 max-w-2xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('misc.index')" class="hover:underline">Miscellaneous</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Create</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Miscellaneous Transaction</h1>
                <Link :href="route('misc.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">
                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Select whether this is income or expense.">
                        Type *
                    </label>
                    <select v-model="form.type" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    <div v-if="form.errors.type" class="text-red-500 text-sm">{{ form.errors.type }}</div>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Date of the transaction.">
                        Date *
                    </label>
                    <input type="date" v-model="form.date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.date" class="text-red-500 text-sm">{{ form.errors.date }}</div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" :title="`Amount of the transaction (${currency}).`">
                        Amount ({{ currency }}) *
                    </label>
                    <input type="number" step="0.01" v-model="form.amount" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm">{{ form.errors.amount }}</div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Optional category to help organize transactions.">
                        Category
                    </label>
                    <input type="text" v-model="form.category" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Detailed description of the transaction.">
                        Description
                    </label>
                    <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>

                <!-- Reference # -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Optional reference number for tracking.">
                        Reference #
                    </label>
                    <input type="text" v-model="form.reference_number" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg transition">
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
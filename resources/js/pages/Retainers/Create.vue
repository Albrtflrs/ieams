<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';

const props = defineProps({
    clients: Array,
    reference_number: String,
});

const { currency } = useSettings();

const form = useForm({
    client_id: '',
    total_amount: '',
    start_date: new Date().toISOString().slice(0, 10),
    end_date: '',
    status: 'active',
    description: '',
});

const submit = () => {
    form.post(route('retainers.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="New Retainer" />

        <div class="p-6 max-w-2xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('retainers.index')" class="hover:underline">Retainers</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Create</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add New Retainer</h1>
                <Link :href="route('retainers.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">

                <!-- Client -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Select the client for this retainer.">
                        Client *
                    </label>
                    <select v-model="form.client_id" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required>
                        <option value="">Select Client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <div v-if="form.errors.client_id" class="text-red-500 text-sm">{{ form.errors.client_id }}</div>
                </div>

                <!-- Reference Number (auto-generated) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Auto-generated reference number.">
                        Reference Number
                    </label>
                    <input :value="props.reference_number" class="w-full border rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:border-gray-600" readonly />
                    <p class="text-xs text-gray-500 mt-1">Auto‑generated</p>
                </div>

                <!-- Total Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" :title="`Total retainer amount (${currency}).`">
                        Total Amount ({{ currency }}) *
                    </label>
                    <input v-model.number="form.total_amount" type="number" step="0.01" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                    <div v-if="form.errors.total_amount" class="text-red-500 text-sm">{{ form.errors.total_amount }}</div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Date when the retainer starts.">
                            Start Date *
                        </label>
                        <input v-model="form.start_date" type="date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                        <div v-if="form.errors.start_date" class="text-red-500 text-sm">{{ form.errors.start_date }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Date when the retainer expires (optional).">
                            End Date
                        </label>
                        <input v-model="form.end_date" type="date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        <div v-if="form.errors.end_date" class="text-red-500 text-sm">{{ form.errors.end_date }}</div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Current status of the retainer.">
                        Status
                    </label>
                    <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="active">Active</option>
                        <option value="used_up">Used Up</option>
                        <option value="expired">Expired</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm">{{ form.errors.status }}</div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Additional notes or description for this retainer.">
                        Description
                    </label>
                    <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
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
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
    // New fields
    billing_frequency: '',
    payment_terms: '',
    auto_renew: false,
    allocated_hours: null,
    overage_hourly_rate: null,
    rollover_allowed: false,
    sla_tier: '',
    contract_path: '',
    services: [],
});

const submit = () => {
    form.post(route('retainers.store'));
};

// Add/remove service lines
const addService = () => {
    form.services.push({ name: '', description: '' });
};
const removeService = (index) => {
    form.services.splice(index, 1);
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client *</label>
                    <select v-model="form.client_id" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required>
                        <option value="">Select Client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <div v-if="form.errors.client_id" class="text-red-500 text-sm">{{ form.errors.client_id }}</div>
                </div>

                <!-- Reference Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number</label>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date *</label>
                        <input v-model="form.start_date" type="date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                        <div v-if="form.errors.start_date" class="text-red-500 text-sm">{{ form.errors.start_date }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input v-model="form.end_date" type="date" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        <div v-if="form.errors.end_date" class="text-red-500 text-sm">{{ form.errors.end_date }}</div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="active">Active</option>
                        <option value="used_up">Used Up</option>
                        <option value="expired">Expired</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm">{{ form.errors.status }}</div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea v-model="form.description" rows="2" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>

                <!-- ─── NEW FIELDS ─── -->
                <hr class="border-gray-200 dark:border-gray-700" />

                <!-- Billing Frequency -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Frequency</label>
                    <select v-model="form.billing_frequency" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">— Select —</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annually">Annually</option>
                    </select>
                </div>

                <!-- Payment Terms -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Terms</label>
                    <select v-model="form.payment_terms" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">— Select —</option>
                        <option value="due_on_receipt">Due on Receipt</option>
                        <option value="net_10">Net 10</option>
                        <option value="net_30">Net 30</option>
                    </select>
                </div>

                <!-- Auto-Renew -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.auto_renew" id="auto_renew" class="h-4 w-4 rounded border-gray-300 text-purple-600" />
                    <label for="auto_renew" class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto‑Renew</label>
                </div>

                <!-- Allocated Hours -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allocated Hours (per cycle)</label>
                    <input v-model.number="form.allocated_hours" type="number" min="0" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" placeholder="e.g. 10" />
                </div>

                <!-- Overage Rate -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" :title="`Overage hourly rate (${currency})`">
                        Overage Hourly Rate ({{ currency }})
                    </label>
                    <input v-model.number="form.overage_hourly_rate" type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" placeholder="e.g. 1500" />
                </div>

                <!-- Rollover Allowed -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.rollover_allowed" id="rollover_allowed" class="h-4 w-4 rounded border-gray-300 text-purple-600" />
                    <label for="rollover_allowed" class="text-sm font-medium text-gray-700 dark:text-gray-300">Allow Rollover of Unused Hours</label>
                </div>

                <!-- SLA Tier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SLA Tier</label>
                    <select v-model="form.sla_tier" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">— Select —</option>
                        <option value="bronze">Bronze (48h response)</option>
                        <option value="silver">Silver (24h response)</option>
                        <option value="gold">Gold (4h response)</option>
                    </select>
                </div>

                <!-- Contract File -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract File</label>
                    <input type="file" @change="handleFileUpload" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" accept=".pdf,.doc,.docx" />
                    <p class="text-xs text-gray-500 mt-1">Upload signed PDF or Word document (max 5MB).</p>
                    <div v-if="form.contract_path" class="text-xs text-gray-500 mt-1">Current: {{ form.contract_path }}</div>
                </div>

                <!-- Services / Line Items -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Services Included</label>
                    <div v-for="(service, index) in form.services" :key="index" class="flex gap-2 items-center mb-2">
                        <input v-model="service.name" placeholder="Service name" class="flex-1 border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        <input v-model="service.description" placeholder="Description (optional)" class="flex-1 border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        <button type="button" @click="removeService(index)" class="text-red-500 hover:text-red-700">✕</button>
                    </div>
                    <button type="button" @click="addService" class="text-sm text-purple-600 dark:text-purple-400 hover:underline">+ Add Service</button>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-4">
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
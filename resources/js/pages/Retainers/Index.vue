<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useSettings } from '@/composables/useSettings';
import { useDateFormat } from '@/composables/useDateFormat';

const props = defineProps({
    retainers: Object,
    summary: Object,
});

const { currency } = useSettings();
const { formatDate } = useDateFormat();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const deleteRetainer = (id) => {
    if (confirm('Delete this retainer? This action cannot be undone.')) {
        router.delete(route('retainers.destroy', id));
    }
};

const statusColors = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    used_up: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    expired: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};
</script>

<template>
    <AppLayout>
        <div class="p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Retainers</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Retainers</h1>
                <Link :href="route('retainers.create')" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition">
                    + Add Retainer
                </Link>
            </div>

            <!-- Summary Cards with tooltips -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-lg p-4 shadow"
                     title="Total number of retainers.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Retainers</p>
                    <p class="text-2xl font-bold">{{ summary.count ?? 0 }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-4 shadow"
                     title="Total remaining active value across all retainers.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Active Value</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_active) }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-lg p-4 shadow"
                     title="Total amount already used from retainers.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Used</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_used) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-cyan-600 text-white rounded-lg p-4 shadow"
                     title="Total remaining balance across all retainers.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Remaining</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_remaining) }}</p>
                </div>
            </div>

            <!-- Retainers Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Used</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Remaining</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Start</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">End</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="r in retainers.data" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2 font-mono">{{ r.reference_number }}</td>
                            <td class="px-4 py-2">
                                <Link :href="route('clients.show', r.client_id)" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ r.client_name }}
                                </Link>
                            </td>
                            <td class="px-4 py-2">{{ peso(r.total_amount) }}</td>
                            <td class="px-4 py-2">{{ peso(r.used_amount) }}</td>
                            <td class="px-4 py-2 font-semibold"
                                :class="r.remaining_balance > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500'">
                                {{ peso(r.remaining_balance) }}
                            </td>
                            <td class="px-4 py-2">{{ formatDate(r.start_date) }}</td>
                            <td class="px-4 py-2">{{ formatDate(r.end_date) || '—' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    :class="statusColors[r.status]">
                                    {{ r.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <Link :href="route('retainers.edit', r.id)" class="text-blue-600 dark:text-blue-400 hover:underline mr-2">Edit</Link>
                                <button @click="deleteRetainer(r.id)" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!retainers.data || retainers.data.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">No retainers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="retainers.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in retainers.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
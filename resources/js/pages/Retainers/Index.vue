<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSettings } from '@/composables/useSettings';
import { useDateFormat } from '@/composables/useDateFormat';

const props = defineProps({
    retainers: Object,
    summary: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const canViewTrash = computed(() => user.value && ['super_admin', 'admin'].includes(user.value.role));

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

const servicesList = (services) => {
    if (!services || !services.length) return '—';
    return services.map(s => s.name).join(', ');
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
                <div class="flex items-center gap-3">
                    <!-- 👇 Trash button (admin only) -->
                    <Link
                        v-if="canViewTrash"
                        href="/retainers/trash-bin"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-800/30 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Trash
                    </Link>
                    <Link :href="route('retainers.create')" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition">
                        + Add Retainer
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Retainers</p>
                    <p class="text-2xl font-bold">{{ summary.count ?? 0 }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Active Value</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_active) }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Used</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_used) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-cyan-600 text-white rounded-lg p-4 shadow">
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
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Billing</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hours</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SLA</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Services</th>
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
                            <td class="px-4 py-2">{{ r.billing_frequency || '—' }}</td>
                            <td class="px-4 py-2">{{ r.allocated_hours ? r.allocated_hours + 'h' : '—' }}</td>
                            <td class="px-4 py-2">{{ r.sla_tier || '—' }}</td>
                            <td class="px-4 py-2 text-xs">{{ servicesList(r.services) }}</td>
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
                            <td colspan="13" class="px-4 py-8 text-center text-gray-500">No retainers found.</td>
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
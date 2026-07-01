<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    retainers: Object,
});

const restore = (id) => {
    if (confirm('Restore this retainer?')) {
        router.patch(route('retainers.restore', id));
    }
};

const forceDelete = (id) => {
    if (confirm('Permanently delete this retainer? This cannot be undone.')) {
        router.delete(route('retainers.force-delete', id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Trash – Retainers</h1>
                <Link :href="route('retainers.index')" class="text-blue-600 hover:underline">← Back to Retainers</Link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Used</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deleted At</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="r in retainers.data" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">{{ r.reference_number }}</td>
                            <td class="px-4 py-3">{{ r.client_name || '—' }}</td>
                            <td class="px-4 py-3">₱{{ Number(r.total_amount).toLocaleString() }}</td>
                            <td class="px-4 py-3">₱{{ Number(r.used_amount).toLocaleString() }}</td>
                            <td class="px-4 py-3 font-medium" :class="r.remaining_balance > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500'">
                                ₱{{ Number(r.remaining_balance).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': r.status === 'active',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': r.status === 'used_up',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': r.status === 'expired',
                                    }">
                                    {{ r.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ r.deleted_at }}</td>
                            <td class="px-4 py-3">
                                <button @click="restore(r.id)" class="text-green-600 dark:text-green-400 hover:underline mr-3 text-sm font-medium">Restore</button>
                                <button @click="forceDelete(r.id)" class="text-red-600 dark:text-red-400 hover:underline text-sm font-medium">Permanently Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!retainers.data || retainers.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">The trash is empty.</td>
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
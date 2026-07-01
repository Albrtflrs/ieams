<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    quotations: Object,
});

const restore = (id) => {
    if (confirm('Restore this quotation?')) {
        router.patch(route('quotations.restore', id));
    }
};

const forceDelete = (id) => {
    if (confirm('Permanently delete this quotation? This cannot be undone.')) {
        router.delete(route('quotations.force-delete', id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Trash – Quotations</h1>
                <Link :href="route('quotations.index')" class="text-blue-600 hover:underline">← Back to Quotations</Link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quotation #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deleted At</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="q in quotations.data" :key="q.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">{{ q.quotation_number }}</td>
                            <td class="px-4 py-3">{{ q.client_name || '—' }}</td>
                            <td class="px-4 py-3 font-medium">₱{{ Number(q.total_amount).toLocaleString() }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    :class="{
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': q.status === 'draft',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': q.status === 'sent',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': q.status === 'accepted',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': q.status === 'rejected',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': q.status === 'converted',
                                    }">
                                    {{ q.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ q.deleted_at }}</td>
                            <td class="px-4 py-3">
                                <button @click="restore(q.id)" class="text-green-600 dark:text-green-400 hover:underline mr-3 text-sm font-medium">Restore</button>
                                <button @click="forceDelete(q.id)" class="text-red-600 dark:text-red-400 hover:underline text-sm font-medium">Permanently Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!quotations.data || quotations.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">The trash is empty.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="quotations.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in quotations.links" :key="link.label">
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
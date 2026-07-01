<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Object,
});

const restore = (id) => {
    if (confirm('Restore this record?')) {
        router.patch(route('income.restore', id));
    }
};

const forceDelete = (id) => {
    if (confirm('Permanently delete this record? This cannot be undone.')) {
        router.delete(route('income.force-delete', id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Trash – Income</h1>
                <Link :href="route('income.index')" class="text-blue-600 hover:underline">← Back to Income</Link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deleted At</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="t in transactions.data" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">{{ t.item_no }}</td>
                            <td class="px-4 py-3">{{ t.client_name || '—' }}</td>
                            <td class="px-4 py-3 font-medium">₱{{ Number(t.amount_paid).toLocaleString() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ t.deleted_at }}</td>
                            <td class="px-4 py-3">
                                <button @click="restore(t.id)" class="text-green-600 dark:text-green-400 hover:underline mr-3 text-sm font-medium">Restore</button>
                                <button @click="forceDelete(t.id)" class="text-red-600 dark:text-red-400 hover:underline text-sm font-medium">Permanently Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!transactions.data || transactions.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">The trash is empty.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in transactions.links" :key="link.label">
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
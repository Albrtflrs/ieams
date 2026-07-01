<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    logs: Object,
});

const eventColors = {
    created: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    updated: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    deleted: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    restored: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    'force-deleted': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    login: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    logout: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
};

const getEventColor = (event) => eventColors[event] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

const formatChanges = (changes) => {
    if (!changes || Object.keys(changes).length === 0) return '—';
    return Object.entries(changes).map(([key, value]) => `${key}: ${value}`).join(', ');
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Audit Log</h1>
                <Link :href="route('dashboard')" class="text-blue-600 hover:underline">← Dashboard</Link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ log.created_at }}</td>
                            <td class="px-4 py-3 text-sm">{{ log.causer_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize" :class="getEventColor(log.event)">
                                    {{ log.event }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ log.description }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ log.subject_type }} #{{ log.subject_id }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" :title="formatChanges(log.changes)">
                                {{ formatChanges(log.changes) }}
                            </td>
                        </tr>
                        <tr v-if="!logs.data || logs.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No activity logged yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in logs.links" :key="link.label">
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
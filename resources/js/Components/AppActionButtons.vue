<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    editRoute: String,
    deleteAction: Function,
    viewRoute: String,
    showView: { type: Boolean, default: false },
    size: { type: String, default: 'sm' }, // sm, md
});

const sizeClasses = {
    sm: 'px-2.5 py-1 text-xs gap-1.5',
    md: 'px-4 py-1.5 text-sm gap-2',
};
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- View -->
        <Link
            v-if="viewRoute"
            :href="viewRoute"
            class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 font-medium text-gray-700 dark:text-gray-300 shadow-sm transition-all hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500/40"
            :class="sizeClasses[size]"
        >
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            View
        </Link>

        <!-- Edit -->
        <Link
            v-if="editRoute"
            :href="editRoute"
            class="inline-flex items-center rounded-md border border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/30 font-medium text-blue-700 dark:text-blue-400 shadow-sm transition-all hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500/40"
            :class="sizeClasses[size]"
        >
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Edit
        </Link>

        <!-- Delete -->
        <button
            v-if="deleteAction"
            @click="deleteAction"
            class="inline-flex items-center rounded-md border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/30 font-medium text-red-700 dark:text-red-400 shadow-sm transition-all hover:bg-red-100 dark:hover:bg-red-800/40 hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500/40"
            :class="sizeClasses[size]"
        >
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Delete
        </button>
    </div>
</template>
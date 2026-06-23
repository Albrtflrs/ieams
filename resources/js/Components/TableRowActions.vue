<script setup>
import { Link } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2 } from 'lucide-vue-next';

defineProps({
    editRoute: String,
    deleteAction: Function,
    viewRoute: String,
    showView: { type: Boolean, default: false },
    size: { type: String, default: 'sm' }, // sm, md
});

const sizeConfig = {
    sm: { pad: 'p-1.5', icon: 14 },
    md: { pad: 'p-2', icon: 16 },
};
</script>

<template>
    <div class="flex items-center gap-1">
        <Link
            v-if="viewRoute"
            :href="viewRoute"
            :class="sizeConfig[size].pad"
            class="group relative inline-flex items-center justify-center rounded-md text-slate-500 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 dark:text-slate-400 dark:hover:bg-slate-700/60 dark:hover:text-slate-200"
        >
            <Eye :size="sizeConfig[size].icon" :stroke-width="2" />
            <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 dark:bg-slate-700">
                View
            </span>
        </Link>

        <Link
            v-if="editRoute"
            :href="editRoute"
            :class="sizeConfig[size].pad"
            class="group relative inline-flex items-center justify-center rounded-md text-blue-600 transition-colors duration-150 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 dark:text-blue-400 dark:hover:bg-blue-500/10 dark:hover:text-blue-300"
        >
            <Pencil :size="sizeConfig[size].icon" :stroke-width="2" />
            <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 dark:bg-slate-700">
                Edit
            </span>
        </Link>

        <button
            v-if="deleteAction"
            type="button"
            @click="deleteAction"
            :class="sizeConfig[size].pad"
            class="group relative inline-flex items-center justify-center rounded-md text-red-500 transition-colors duration-150 hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300"
        >
            <Trash2 :size="sizeConfig[size].icon" :stroke-width="2" />
            <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 dark:bg-slate-700">
                Delete
            </span>
        </button>
    </div>
</template>
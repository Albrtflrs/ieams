<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav v-if="links.length" aria-label="Pagination" class="mt-4 flex w-full justify-center overflow-x-auto pb-1">
        <div class="flex items-center gap-1.5">
            <template v-for="link in links" :key="link.label">
                <button
                    v-if="link.url"
                    type="button"
                    @click="router.visit(link.url)"
                    :aria-current="link.active ? 'page' : undefined"
                    :aria-label="link.label.replace(/<[^>]*>/g, '')"
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    :class="link.active
                        ? 'border-emerald-600 bg-emerald-600 text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'"
                    v-html="link.label"
                />
                <span
                    v-else
                    aria-disabled="true"
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-gray-200 px-3 text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500"
                    v-html="link.label"
                />
            </template>
        </div>
    </nav>
</template>
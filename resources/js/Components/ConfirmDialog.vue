<script setup>
import { useConfirm } from '@/composables/useConfirm';

const { request, close } = useConfirm();
</script>

<template>
    <Teleport to="body">
        <div v-if="request" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/40 p-4 backdrop-blur-sm" @keydown.esc="close(false)">
            <section role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message" class="w-full max-w-sm rounded-xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </span>
                    <div>
                        <h2 id="confirm-title" class="text-base font-semibold text-gray-900 dark:text-white">{{ request.title }}</h2>
                        <p id="confirm-message" class="mt-1 text-sm leading-5 text-gray-600 dark:text-gray-300">{{ request.message }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="close(false)" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                    <button type="button" @click="close(true)" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">Confirm</button>
                </div>
            </section>
        </div>
    </Teleport>
</template>
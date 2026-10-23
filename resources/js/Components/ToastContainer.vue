<!-- resources/js/Components/ToastContainer.vue -->
<script setup>
import { useToast } from '@/composables/useToast';

const { toasts, removeToast } = useToast();

const toastClasses = (type) => {
  const colors = {
    success: 'border-emerald-200 dark:border-emerald-800',
    error: 'border-red-200 dark:border-red-800',
    warning: 'border-amber-200 dark:border-amber-800',
    info: 'border-teal-200 dark:border-teal-800',
  };
  return colors[type] || colors.info;
};
</script>

<template>
  <div class="fixed bottom-5 right-5 z-50 flex w-[min(24rem,calc(100vw-2.5rem))] flex-col gap-2 pointer-events-none" aria-live="polite">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="pointer-events-auto flex items-start gap-3 rounded-lg border bg-white p-4 text-gray-800 shadow-xl ring-1 ring-black/5 transition-all dark:bg-gray-800 dark:text-gray-100"
      :class="toastClasses(toast.type)"
      role="status"
    >
      <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full" :class="{ 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300': toast.type === 'success', 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300': toast.type === 'error', 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300': toast.type === 'warning', 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300': toast.type === 'info' }">
        <svg v-if="toast.type === 'success'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <svg v-else-if="toast.type === 'error'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </span>
      <span class="flex-1 pt-1 text-sm font-medium">{{ toast.message }}</span>
      <button @click="removeToast(toast.id)" aria-label="Dismiss notification" class="rounded p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>
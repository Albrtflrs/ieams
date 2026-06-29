<!-- resources/js/Components/ToastContainer.vue -->
<script setup>
import { useToast } from '@/composables/useToast';
import { computed } from 'vue';

const { toasts, removeToast } = useToast();

const toastClasses = (type) => {
  const base = 'transform transition-all duration-300 ease-in-out';
  const colors = {
    success: 'bg-emerald-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-amber-500 text-white',
    info: 'bg-blue-500 text-white',
  };
  return `${base} ${colors[type] || colors.info}`;
};
</script>

<template>
  <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="pointer-events-auto p-4 rounded-lg shadow-lg flex items-center justify-between"
      :class="toastClasses(toast.type)"
    >
      <span class="text-sm font-medium">{{ toast.message }}</span>
      <button @click="removeToast(toast.id)" class="text-white/70 hover:text-white transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>
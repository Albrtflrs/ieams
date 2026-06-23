<script setup>
defineProps({
    text: String,
});
</script>

<template>
    <div class="relative group inline-block">
        <button type="button" class="text-white/60 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>
        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-2 text-xs bg-gray-900 dark:bg-gray-700 text-white rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition pointer-events-none z-10 whitespace-normal text-center">
            {{ text }}
        </div>
    </div>
</template>
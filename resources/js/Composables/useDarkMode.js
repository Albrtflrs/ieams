// resources/js/composables/useDarkMode.js
import { ref, watch } from 'vue';

// ← These are OUTSIDE the function — shared singleton
const stored = localStorage.getItem('ieams-theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const isDark = ref(stored !== null ? stored === 'dark' : prefersDark);

watch(isDark, (val) => {
    document.documentElement.classList.toggle('dark', val);
    localStorage.setItem('ieams-theme', val ? 'dark' : 'light');
}, { immediate: true });

export function useDarkMode() {
    const toggle = () => { isDark.value = !isDark.value; };
    return { isDark, toggle };
}
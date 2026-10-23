// resources/js/composables/useDarkMode.js
import { computed, ref, watch } from 'vue';

const storedTheme = localStorage.getItem('ieams-theme');
const initialTheme = storedTheme === 'light' ? 'teal' : storedTheme;
const theme = ref(['light', 'dark', 'teal'].includes(initialTheme) ? initialTheme : 'teal');
const isDark = computed(() => theme.value === 'dark');
const isTeal = computed(() => theme.value === 'teal');

watch(theme, (value) => {
    document.documentElement.classList.toggle('dark', value === 'dark');
    document.documentElement.classList.toggle('teal', value === 'teal');
    localStorage.setItem('ieams-theme', value);
}, { immediate: true });

export function useDarkMode() {
    const toggle = () => { theme.value = isDark.value ? 'light' : 'dark'; };
    return { theme, isDark, isTeal, toggle };
}
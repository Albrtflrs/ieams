import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useSettings() {
    const page = usePage();
    const settings = computed(() => page.props.settings || {});
    const currency = computed(() => settings.value.currency_symbol || '₱');
    const dateFormat = computed(() => settings.value.date_format || 'Y-m-d');
    const perPage = computed(() => settings.value.rows_per_page || 20);

    return { settings, currency, dateFormat, perPage };
}
import { useSettings } from './useSettings';

export function useDateFormat() {
    const { dateFormat } = useSettings();

    const formatDate = (date) => {
        if (!date) return '';
        const d = new Date(date);
        const fmt = dateFormat.value;
        return fmt
            .replace('Y', d.getFullYear())
            .replace('m', String(d.getMonth() + 1).padStart(2, '0'))
            .replace('d', String(d.getDate()).padStart(2, '0'));
    };

    return { formatDate };
}
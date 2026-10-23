// resources/js/composables/useToast.js
import { toastBus } from '@/eventBus';

export function useToast() {
    return {
        toasts: toastBus.toasts,
        addToast: (message, type, duration) => toastBus.addToast({ message, type, duration }),
        removeToast: toastBus.removeToast,
        success: toastBus.success,
        error: toastBus.error,
        info: toastBus.info,
        warning: toastBus.warning,
    };
}
// resources/js/eventBus.js
import { ref } from 'vue'

const toasts = ref([])
let id = 0

export const toastBus = {
    toasts,
    addToast({ message, type = 'info', duration = 5000 }) {
        const toast = { id: ++id, message, type, duration }
        toasts.value.push(toast)
        setTimeout(() => {
            const index = toasts.value.findIndex(t => t.id === toast.id)
            if (index !== -1) toasts.value.splice(index, 1)
        }, duration)
        return toast.id
    },
    removeToast(id) {
        const index = toasts.value.findIndex(t => t.id === id)
        if (index !== -1) toasts.value.splice(index, 1)
    },
    success(message, duration) { return this.addToast({ message, type: 'success', duration }) },
    error(message, duration) { return this.addToast({ message, type: 'error', duration }) },
    info(message, duration) { return this.addToast({ message, type: 'info', duration }) },
    warning(message, duration) { return this.addToast({ message, type: 'warning', duration }) },
}
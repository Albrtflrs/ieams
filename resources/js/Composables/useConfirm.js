import { ref } from 'vue';

const request = ref(null);
let resolveRequest = null;

export function useConfirm() {
    const confirm = (message, title = 'Please confirm') => {
        if (resolveRequest) resolveRequest(false);
        request.value = { message, title };
        return new Promise((resolve) => {
            resolveRequest = resolve;
        });
    };

    const close = (result) => {
        request.value = null;
        resolveRequest?.(result);
        resolveRequest = null;
    };

    return { request, confirm, close };
}
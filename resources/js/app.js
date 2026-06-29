import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { toastBus } from './eventBus';

createInertiaApp({
    title: (title) => `${title} - Accounting`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // ---- Toast flash messages ----
        router.on('success', (event) => {
            const flash = event.detail.page.props.flash || {};
            if (flash.success) toastBus.success(flash.success);
            if (flash.error) toastBus.error(flash.error);
        });

        // Initial flash (if any)
        const initialFlash = props.initialPage?.props?.flash || {};
        if (initialFlash.success) {
            setTimeout(() => toastBus.success(initialFlash.success), 300);
        }
        if (initialFlash.error) {
            setTimeout(() => toastBus.error(initialFlash.error), 300);
        }

        return app;
    },
});
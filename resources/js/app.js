import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { InertiaProgress } from '@inertiajs/progress';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import Components from '@/plugins/components';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Core plugins
        app.use(plugin);
        app.use(ZiggyVue);

        // Custom components
        app.use(Components);

        // Toast notifications
        app.use(Toast, {
            position: 'top-right',
            timeout: 3000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: true,
            closeButton: false,
            icon: true,
            rtl: false,
            transition: 'Vue-Toastification__bounce',
            maxToasts: 20,
            newestOnTop: true,
            toastClassName: 'bg-background-secondary border border-border-primary text-white rounded-lg shadow-lg',
            bodyClassName: 'text-sm font-medium',
        });

        return app.mount(el);
    },
    progress: {
        color: '#2563eb',
        showSpinner: true,
        delay: 0,
    },
});

// Configure progress bar
InertiaProgress.init({
    color: '#2563eb',
    showSpinner: true,
    includeCSS: true,
});

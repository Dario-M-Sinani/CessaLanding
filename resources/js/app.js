import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import MiniAsistente from './Components/MiniAsistente.vue';

const appName = import.meta.env.VITE_APP_NAME || 'CESSA';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // MiniAsistente va acá (fuera de <App>), no en AppLayout: cada página
        // envuelve su propio <AppLayout> (no hay layout persistente de
        // Inertia configurado), así que si viviera ahí adentro se destruiría
        // y se volvería a crear en cada navegación -- el botón "desaparecía"
        // al navegar. Montado acá sobrevive toda la sesión de la SPA.
        return createApp({ render: () => [h(App, props), h(MiniAsistente)] })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#0284c7',
    },
});

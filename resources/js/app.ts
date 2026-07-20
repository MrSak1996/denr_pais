import '../css/app.css';
import 'primeicons/primeicons.css';

import { createInertiaApp } from '@inertiajs/vue3';
import Aura from '@primeuix/themes/aura';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import Chart from 'primevue/chart';
import Fieldset from 'primevue/fieldset';
import Card from 'primevue/card';
import ToastService from 'primevue/toastservice';

import Toast from 'primevue/toast';
import FloatLabel from 'primevue/floatlabel';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import ConfirmationService from 'primevue/confirmationservice'
import DialogService from 'primevue/dialogservice'
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { lettersOnlyUppercase, lettersNumbersDashUppercase } from './composables/useUppercaseLettersOnly'


const appName = import.meta.env.VITE_APP_NAME || 'DENR Protected Area Information System';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });


        app.directive('letters-only-uppercase', lettersOnlyUppercase);
        app.directive('letters-numbers-dash-uppercase', lettersNumbersDashUppercase);

        app.use(plugin)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: false || 'none',
                    },
                },
            })
            .use(ZiggyVue)
            .use(ToastService)
            .use(DialogService)
            .use(ConfirmationService)
            .component('Toast', Toast)
            .component('FloatLabel', FloatLabel)
            .component('InputText', InputText)
            .component('Textarea', Textarea)
            .component('InputNumber', InputNumber)
            .component('Chart', Chart)
            .component('Card', Card)
            .component('Fieldset', Fieldset)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Light/dark mode setup
initializeTheme();

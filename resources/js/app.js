import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Swal from 'sweetalert2';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import { Toast, ToastService } from 'primevue';
import 'primeicons/primeicons.css';
import theme from 'tailwindcss/defaultTheme';

import Button from "primevue/button"        
    import Toolbar from 'primevue/toolbar';
    import DataTable from 'primevue/datatable';
    import Column from 'primevue/column';
    import Dialog from 'primevue/dialog';
    import InputText from 'primevue/inputtext';
    import IconField from 'primevue/iconfield';
    import InputIcon from 'primevue/inputicon';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            app.use(PrimeVue,{
                theme:{
                    preset: Aura
                }
            });
            
            //configuracion global de Sweetalert2
            app.config.globalProperties.$swal = Swal;
            app.use(PrimeVue);
            app.use(ToastService);
            //definimo  los componentes de primevue d forma global
            app.component('Toast',Toast);
            app.component('Button',Button);
            app.component('Toolbar',Toolbar);
            app.component('DataTable',DataTable);
            app.component('Column',Column);
            app.component('Dialog',Dialog);
            app.component('InputText',InputText);
            app.component('IconField',IconField);
            app.component('InputIcon',InputIcon);



            app.mount(el);
            return app;
            
    },
    progress: {
        color: '#4B5563',
    },
});

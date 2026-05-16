import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import VuetifyMoney from 'vuetify-money-3'
import ConfirmDialog from './Components/ConfirmDialog.vue'
import i18n from './Locales/i18n'
import toast from './Plugins/toast'
import vuetify from './Plugins/vuetify'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'To the Future'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .use(toast)
            .use(i18n)
            .use(VuetifyMoney)
            .component('ConfirmDialog', ConfirmDialog)
            .mount(el)
    },
    progress: {
        color: '#4CAF50',
    },
})

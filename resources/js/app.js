import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import vuetify from './Plugins/vuetify'
import toast from './Plugins/toast'
import i18n from './Locales/i18n'
import { useCurrencyInput as CurrencyInput } from 'vue-currency-input'
import money from 'v-money3'

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
            .use(CurrencyInput)
            .use(money)
            .mount(el)
    },
    progress: {
        color: '#4CAF50',
    },
})

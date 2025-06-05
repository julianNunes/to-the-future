import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as labsComponents from 'vuetify/labs/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import MomentAdapter from '@date-io/moment'

const vuetify = createVuetify({
    components: {
        ...components,
        ...labsComponents,
    },
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
    theme: {
        defaultTheme: 'light',
    },
    date: {
        adapter: MomentAdapter,
        locale: {
            pt: 'pt',
        },
    },
})

export default vuetify

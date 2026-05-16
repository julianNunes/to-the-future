import MomentAdapter from '@date-io/moment'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { VDateInput } from 'vuetify/labs/VDateInput'
import 'vuetify/styles'

const vuetify = createVuetify({
    components: {
        VDateInput,
    },
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

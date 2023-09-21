import { createI18n } from 'vue-i18n'
import pt from '../Locales/pt.json'
import en from '../Locales/en.json'

const i18n = new createI18n({
    locale: 'pt', // set locale
    fallbackLocale: 'en',
    eager: true,
    messages: { pt, en },
})

export default i18n

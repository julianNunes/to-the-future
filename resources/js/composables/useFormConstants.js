import i18n from '@/Locales/i18n'

const { t } = i18n.global

export function useValidationRules() {
    return {
        textFieldRules: [(v) => !!v || t('rules.required-text-field')],
        currencyFieldRules: [
            (value) => {
                if (!value) return t('rules.required-text-field')
                const numValue =
                    typeof value === 'string'
                        ? parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.'))
                        : Number(value)
                if (numValue <= 0) return t('rules.required-currency-field')
                return true
            },
        ],
        booleanFieldRules: [(v) => v !== null || t('rules.required-text-field')],
        selectFieldRules: [(v) => !!v || t('rules.required-text-field')],
        numberFieldRules: [
            (value) => {
                if (!value) return t('rules.required-text-field')
                if (Number(value) <= 0) return t('rules.required-currency-field')
                return true
            },
        ],
        digitsFieldRules: [
            (value) => {
                if (!value) return t('rules.required-text-field')
                if (!/^\d+$/.test(value)) return t('rules.only-numbers')
                if (String(value).length !== 4) return t('rules.required-text-field')

                return true
            },
        ],
    }
}

export function useGroupList() {
    return [
        { name: t('default.in-installments'), value: 'PORTION' },
        { name: t('default.monthly'), value: 'MONTHLY' },
        { name: t('default.week-1'), value: 'WEEK_1' },
        { name: t('default.week-2'), value: 'WEEK_2' },
        { name: t('default.week-3'), value: 'WEEK_3' },
        { name: t('default.week-4'), value: 'WEEK_4' },
    ]
}

export function useCurrencyConfig() {
    return {
        locale: 'pt-BR',
        prefix: 'R$',
        suffix: '',
        length: 11,
        precision: 2,
    }
}

export function useDaysList() {
    return Array.from({ length: 31 }, (_, i) => String(i + 1).padStart(2, '0'))
}

export function useIsActiveOptions() {
    return [
        { name: t('default.yes'), value: 1 },
        { name: t('default.no'), value: 0 },
    ]
}

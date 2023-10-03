import i18n from '@/Locales/i18n'

const { t } = i18n.global
/**
 * Metodo utilizado para somar valores em tabelas
 * @param {Array} data
 * @param {*} key
 * @returns
 */
export function sumField(data, key) {
    if (!data || !data.length) return 0

    let total = data.reduce((a, b) => (parseFloat(a) + (parseFloat(b[key]) || 0)).toFixed(2), 0)

    total = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(total)
    return total
}

/**
 * Metodo utilizado para somar valores em grupos de tabelas
 * @param {Array} data
 * @param {*} group
 * @param {*} key Campo a ser contabilizado no data
 * @returns
 */
export function sumGroup(data, groupName, groupValue, key) {
    if (!data || !data.length) return 0

    let total = data
        .filter((x) => x[groupName] === groupValue)
        .reduce((a, b) => (parseFloat(a) + (parseFloat(b[key]) || 0)).toFixed(2), 0)

    total = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(total)
    return total
}

/**
 *
 * @param {*} value
 * @returns
 */
export function currencyField(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value)
}

export function upperCase(value) {
    return value ? value.toUpperCase() : ''
}

export const MONTHS = [
    {
        name: t('default.month-01'),
        value: '01',
    },
    {
        name: t('default.month-02'),
        value: '02',
    },
    {
        name: t('default.month-03'),
        value: '03',
    },
    {
        name: t('default.month-04'),
        value: '04',
    },
    {
        name: t('default.month-05'),
        value: '05',
    },
    {
        name: t('default.month-06'),
        value: '06',
    },
    {
        name: t('default.month-07'),
        value: '07',
    },
    {
        name: t('default.month-08'),
        value: '08',
    },
    {
        name: t('default.month-09'),
        value: '09',
    },
    {
        name: t('default.month-10'),
        value: '10',
    },
    {
        name: t('default.month-11'),
        value: '11',
    },
    {
        name: t('default.month-12'),
        value: '12',
    },
]

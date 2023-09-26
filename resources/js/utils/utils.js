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

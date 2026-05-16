import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

async function getActiveDialog(page) {
    const dialog = page.locator('[role="dialog"]:visible').last()
    await expect(dialog).toBeVisible()
    return dialog
}

async function setMonthValue(dialog, value) {
    const monthInput = dialog.locator('input[type="month"]').first()

    await monthInput.evaluate((element, newValue) => {
        element.value = newValue
        element.dispatchEvent(new Event('input', { bubbles: true }))
        element.dispatchEvent(new Event('change', { bubbles: true }))
    }, value)

    await expect(monthInput).toHaveValue(value)
}

async function openInvoiceIndexForCard(page, cardName = 'E2E Credit Card') {
    await page.goto('/credit-card')
    await expect(page.locator('#app')).toBeVisible()

    const row = page.locator('tbody tr', { hasText: cardName }).first()

    await expect(row).toBeVisible({ timeout: 10000 })

    const href = await row.locator('a[href$="/invoice"]').first().getAttribute('href')

    if (!href) {
        throw new Error(`Credit card invoice link not found for ${cardName}`)
    }

    await page.goto(href)
    await expect(page).toHaveURL(/\/credit-card\/\d+\/invoice/)
}

async function findAvailableInvoicePeriod(page, year) {
    const dueDay = (await page.getByLabel('Dia do vencimento da fatura').inputValue()).padStart(2, '0')

    for (let month = 1; month <= 12; month++) {
        const monthValue = String(month).padStart(2, '0')
        const dueDisplay = `${dueDay}/${monthValue}/${year}`
        const row = page.locator('tbody tr', { hasText: dueDisplay }).first()

        if ((await row.count()) === 0) {
            return {
                year,
                month: monthValue,
                dueDisplay,
            }
        }
    }

    throw new Error(`No available invoice period found for ${year}`)
}

async function submitDialog(dialog) {
    try {
        await dialog.locator('button:has-text("Salvar")').first().click({ force: true })
    } catch (error) {
        if (!/detached from the DOM|Element is not attached/i.test(String(error))) {
            throw error
        }
    }
}

test('credit card invoice create and show flow works correctly', async ({ page }, testInfo) => {
    await login(page)
    await openInvoiceIndexForCard(page)

    const targetPeriod = await findAvailableInvoicePeriod(page, '2099')

    await page.getByRole('button', { name: 'Novo' }).first().click()
    const dialog = await getActiveDialog(page)

    await setMonthValue(dialog, `${targetPeriod.year}-${targetPeriod.month}`)

    const createResponsePromise = page
        .waitForResponse(
            (response) => response.request().method() === 'POST' && response.url().includes('/credit-card/invoice'),
            { timeout: 5000 }
        )
        .catch(() => null)

    await submitDialog(dialog)
    const createResponse = await createResponsePromise

    if (!createResponse) {
        const messages = await dialog.locator('.v-messages__message, [role="alert"]').allInnerTexts()

        throw new Error(`Invoice create request was not sent. Visible messages: ${messages.join(' | ') || 'none'}`)
    }

    expect(createResponse.status()).toBeLessThan(500)

    const createdRow = page.locator('tbody tr', { hasText: targetPeriod.dueDisplay }).first()
    await expect(createdRow).toBeVisible({ timeout: 10000 })

    const showHref = await createdRow.locator('a[href*="/credit-card/invoice/"]').first().getAttribute('href')

    if (!showHref) {
        throw new Error(`Invoice show link not found for ${targetPeriod.dueDisplay}`)
    }

    await page.goto(showHref)

    await expect(page).toHaveURL(/\/credit-card\/invoice\/\d+/)
    await expect(page.getByText('Fatura do Cartão de Crédito').first()).toBeVisible({ timeout: 10000 })
    await expect(page.getByRole('button', { name: 'Importar Excel' })).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-credit-card-invoice-smoke.png')
})

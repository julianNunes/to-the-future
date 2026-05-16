import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

async function getActiveDialog(page) {
    const dialog = page.locator('[role="dialog"]:visible').last()
    await expect(dialog).toBeVisible()
    return dialog
}

async function openInvoiceShow(page, cardName = 'E2E Credit Card') {
    await page.goto('/credit-card')
    await expect(page.locator('#app')).toBeVisible()

    const row = page.locator('tbody tr', { hasText: cardName }).first()
    await expect(row).toBeVisible({ timeout: 10000 })

    const invoiceIndexHref = await row.locator('a[href$="/invoice"]').first().getAttribute('href')

    if (!invoiceIndexHref) {
        throw new Error(`Invoice index link not found for ${cardName}`)
    }

    await page.goto(invoiceIndexHref)
    await expect(page).toHaveURL(/\/credit-card\/\d+\/invoice/)

    const showHref = await page.locator('a[href*="/credit-card/invoice/"]').first().getAttribute('href')

    if (!showHref) {
        throw new Error('Invoice show link not found on invoice index page')
    }

    await page.goto(showHref)
    await expect(page).toHaveURL(/\/credit-card\/invoice\/\d+/)
}

test('invoice expense show page opens the new expense dialog correctly', async ({ page }, testInfo) => {
    await login(page)
    await openInvoiceShow(page)

    const openInvoiceButton = page.getByRole('button', { name: 'Abrir Fatura' })
    if (await openInvoiceButton.count()) {
        await openInvoiceButton.click()
        await page.waitForResponse(
            (response) => response.request().method() === 'PUT' && /\/credit-card\/invoice\/\d+$/.test(response.url()),
            { timeout: 5000 }
        )
    }

    await page.getByRole('button', { name: 'Novo' }).first().click()
    const dialog = await getActiveDialog(page)

    await expect(dialog.locator('button:has-text("Salvar")').first()).toBeVisible({ timeout: 10000 })
    await expect(dialog.locator('button:has-text("Cancelar")').first()).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-invoice-expense-dialog-smoke.png')
})

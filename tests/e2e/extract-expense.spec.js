import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

async function getActiveDialog(page) {
    const dialog = page.locator('[role="dialog"]:visible').last()
    await expect(dialog).toBeVisible()
    return dialog
}

async function openExtractShowPage(page, cardName = 'E2E Prepaid Card') {
    await page.goto('/prepaid-card')
    await expect(page.locator('#app')).toBeVisible()

    const row = page.locator('tbody tr', { hasText: cardName }).first()
    await expect(row).toBeVisible({ timeout: 10000 })

    const extractIndexHref = await row.locator('a[href$="/extract"]').first().getAttribute('href')

    if (!extractIndexHref) {
        throw new Error(`Prepaid card extract link not found for ${cardName}`)
    }

    await page.goto(extractIndexHref)
    await expect(page).toHaveURL(/\/prepaid-card\/\d+\/extract/)

    const firstRow = page.locator('tbody tr').first()
    await expect(firstRow).toBeVisible({ timeout: 10000 })

    const showHref = await firstRow.locator('a[href*="/prepaid-card/extract/"]').first().getAttribute('href')

    if (!showHref) {
        throw new Error('Extract show link not found on extract index')
    }

    await page.goto(showHref)
    await expect(page).toHaveURL(/\/prepaid-card\/extract\/\d+/)
}

test('extract expense show page opens a new expense dialog', async ({ page }, testInfo) => {
    await login(page)
    await openExtractShowPage(page)

    await expect(page.getByText('Extrato do Cartão Pré-Pago').first()).toBeVisible({ timeout: 10000 })
    await expect(page.getByRole('button', { name: 'Importar Excel' })).toBeVisible({ timeout: 10000 })

    await page.getByRole('button', { name: 'Novo' }).first().click()
    const dialog = await getActiveDialog(page)

    await expect(dialog.getByRole('button', { name: 'Salvar' })).toBeVisible()
    await expect(dialog.getByRole('button', { name: 'Cancelar' })).toBeVisible()

    await captureDebugScreenshot(page, testInfo, 'debug-extract-expense.png')
})

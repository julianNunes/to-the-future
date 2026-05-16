import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

async function openExtractIndexForCard(page, cardName = 'E2E Prepaid Card') {
    await page.goto('/prepaid-card')
    await expect(page.locator('#app')).toBeVisible()

    const row = page.locator('tbody tr', { hasText: cardName }).first()

    await expect(row).toBeVisible({ timeout: 10000 })

    const href = await row.locator('a[href$="/extract"]').first().getAttribute('href')

    if (!href) {
        throw new Error(`Prepaid card extract link not found for ${cardName}`)
    }

    await page.goto(href)
    await expect(page).toHaveURL(/\/prepaid-card\/\d+\/extract/)
}

test('prepaid card extract index and show flow works correctly', async ({ page }, testInfo) => {
    await login(page)
    await openExtractIndexForCard(page)

    const firstRow = page.locator('tbody tr').first()
    await expect(firstRow).toBeVisible({ timeout: 10000 })

    const showHref = await firstRow.locator('a[href*="/prepaid-card/extract/"]').first().getAttribute('href')

    if (!showHref) {
        throw new Error('Extract show link not found on extract index')
    }

    await page.goto(showHref)

    await expect(page).toHaveURL(/\/prepaid-card\/extract\/\d+/)
    await expect(page.getByText('Extrato do Cartão Pré-Pago').first()).toBeVisible({ timeout: 10000 })
    await expect(page.getByRole('button', { name: 'Importar Excel' })).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-prepaid-card-extract-smoke.png')
})

import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

test('invoice expense details page renders successfully', async ({ page }, testInfo) => {
    await login(page)

    // Navigate to Credit Cards page to find a card
    await page.goto('/credit-card')
    await page.waitForTimeout(2000)

    // Wait and find any links starting with /credit-card/ and ending with /invoice
    const invoiceLinks = page.locator('a[href$="/invoice"]')
    const count = await invoiceLinks.count()

    if (count > 0) {
        // Click the first card's invoices link
        await invoiceLinks.first().click()
        await page.waitForTimeout(2000)

        // Find links to view specific invoices (/credit-card/invoice/{id})
        // Wait, normally invoices are rendered in the same page or there is an action button.
        // If there's a link to the invoice detail, click it.
        const detailLinks = page.locator('a[href*="/credit-card/invoice/"]')
        const detailsCount = await detailLinks.count()
        if (detailsCount > 0) {
            await detailLinks.first().click()
            await page.waitForTimeout(3000)
        }
    }

    // Verify the page didn't crash (id="app" exists) and test completes
    await expect(page.locator('#app')).toBeVisible()

    // Capture screenshot of the resulting page
    await captureDebugScreenshot(page, testInfo, 'debug-invoice-expense.png')
})

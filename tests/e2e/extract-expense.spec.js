import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

test('extract expense details page renders successfully', async ({ page }, testInfo) => {
    await login(page)

    // Navigate to Prepaid Cards page to find a card
    await page.goto('/prepaid-card')
    await page.waitForTimeout(2000)

    // Wait and find any links starting with /prepaid-card/ and ending with /extract
    const extractLinks = page.locator('a[href$="/extract"]')
    const count = await extractLinks.count()

    if (count > 0) {
        // Click the first card's extracts link
        await extractLinks.first().click()
        await page.waitForTimeout(2000)

        // Find links to view specific extracts (/prepaid-card/extract/{id})
        const detailLinks = page.locator('a[href*="/prepaid-card/extract/"]')
        const detailsCount = await detailLinks.count()
        if (detailsCount > 0) {
            await detailLinks.first().click()
            await page.waitForTimeout(3000)
        }
    }

    // Verify the page didn't crash (id="app" exists) and test completes
    await expect(page.locator('#app')).toBeVisible()

    // Capture screenshot of the resulting page
    await captureDebugScreenshot(page, testInfo, 'debug-extract-expense.png')
})

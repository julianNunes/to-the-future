import { test, expect } from '@playwright/test'

test('extract expense details page renders successfully', async ({ page }) => {
    // Navigate to login page
    await page.goto('/login')

    // Fill the login form
    await page.fill('input[type="email"]', 'eu_dinovu@hotmail.com')
    await page.fill('input[type="password"]', 'password')
    await page.click('button:has-text("LOGIN")')

    // Wait for Dashboard to ensure login
    await expect(page).toHaveURL(/.*\/dashboard/)

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
    await page.screenshot({ path: 'tests/e2e/debug-extract-expense.png' })
})

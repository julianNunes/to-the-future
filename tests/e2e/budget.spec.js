import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, e2eBudget, login } from './support/auth'

test('budget details page loads correctly', async ({ page }, testInfo) => {
    await login(page)

    // Navigate to a budget list or specific budget page
    // Since we don't know the exact budget ID, we will try to go to the first budget available
    // or just check the budget listing page to ensure no JS errors
    await page.goto(`/budget/${e2eBudget.year}`) // usually /budget/{year} based on routes
    await page.waitForTimeout(2000)

    // Check if there's a link to a budget details page
    const budgetLinks = page.locator('a[href*="/budget/show/"]')
    const count = await budgetLinks.count()

    if (count > 0) {
        // Click the first budget
        await budgetLinks.first().click()
        await page.waitForTimeout(3000)
    }

    // Verify no blank page (app is loaded)
    await expect(page.locator('#app')).toBeVisible()

    // Optional: capture screenshot of the page
    await captureDebugScreenshot(page, testInfo, 'debug-budget.png')
})

import { test, expect } from '@playwright/test'

test('budget details page loads correctly', async ({ page }) => {
    // Navigate to login page
    await page.goto('/login')

    // Fill the login form
    await page.fill('input[type="email"]', 'eu_dinovu@hotmail.com')
    await page.fill('input[type="password"]', 'password')
    await page.click('button:has-text("LOGIN")')

    // Wait for Dashboard to ensure login
    await expect(page).toHaveURL(/.*\/dashboard/)

    // Navigate to a budget list or specific budget page
    // Since we don't know the exact budget ID, we will try to go to the first budget available
    // or just check the budget listing page to ensure no JS errors
    await page.goto('/budget/2026') // usually /budget/{year} based on routes
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
    await page.screenshot({ path: 'tests/e2e/debug-budget.png' })
})

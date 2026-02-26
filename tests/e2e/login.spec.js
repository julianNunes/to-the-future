import { test, expect } from '@playwright/test'

test('login flow works correctly', async ({ page }) => {
    page.on('requestfailed', (request) => {
        console.log(`Failed request: ${request.url()} - ${request.failure()?.errorText}`)
    })

    // Navigate to login page
    await page.goto('/login')
    await page.waitForTimeout(5000)
    await page.screenshot({ path: 'tests/e2e/debug-login.png' })

    // Fill the login form
    await page.fill('input[type="email"]', 'eu_dinovu@hotmail.com')
    await page.fill('input[type="password"]', 'password')

    // Submit
    await page.click('button:has-text("LOGIN")')

    // Allow time to redirect
    await page.waitForTimeout(3000)

    const currentUrl = page.url()
    console.log(`Current URL after login attempt: ${currentUrl}`)

    await expect(page).toHaveURL(/.*\/dashboard/)
    await expect(page.locator('text="Dashboard"').first()).toBeVisible()
})

import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

test('login flow works correctly', async ({ page }, testInfo) => {
    page.on('requestfailed', (request) => {
        console.log(`Failed request: ${request.url()} - ${request.failure()?.errorText}`)
    })

    await login(page)
    await captureDebugScreenshot(page, testInfo, 'debug-login.png')

    const currentUrl = page.url()
    console.log(`Current URL after login attempt: ${currentUrl}`)

    await expect(page).toHaveURL(/.*\/dashboard/)
    await expect(page.locator('text="Dashboard"').first()).toBeVisible()
})

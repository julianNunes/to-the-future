import { expect } from '@playwright/test'

export const e2eCredentials = {
    email: process.env.PLAYWRIGHT_E2E_EMAIL ?? 'e2e@example.com',
    password: process.env.PLAYWRIGHT_E2E_PASSWORD ?? 'password',
}

export const e2eBudget = {
    year: String(process.env.PLAYWRIGHT_E2E_BUDGET_YEAR ?? '2026').padStart(4, '0'),
    month: String(process.env.PLAYWRIGHT_E2E_BUDGET_MONTH ?? '05').padStart(2, '0'),
}

export async function login(page) {
    await page.goto('/login')
    await page.fill('input[type="email"]', e2eCredentials.email)
    await page.fill('input[type="password"]', e2eCredentials.password)
    await page.click('button:has-text("LOGIN")')
    await expect(page).toHaveURL(/.*\/dashboard/)
}

export async function captureDebugScreenshot(page, testInfo, fileName) {
    await page.screenshot({ path: testInfo.outputPath(fileName) })
}

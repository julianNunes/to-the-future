import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

async function getActiveDialog(page) {
    const dialog = page.getByRole('dialog').last()
    await expect(dialog).toBeVisible()
    return dialog
}

async function pickComboboxOption(dialog, page, index, optionText, expectedValue = optionText) {
    const select = dialog.locator('.v-select').nth(index)
    const combobox = select.locator('input[role="combobox"]').first()
    const field = select.locator('.v-field')

    await field.click({ force: true })
    const menuId = await combobox.getAttribute('aria-controls')
    const option = page.locator(`#${menuId} .v-list-item`, { hasText: new RegExp(`^${optionText}$`) }).first()

    await expect(page.locator(`#${menuId}`)).toBeVisible()
    await option.click({ force: true })

    if (!(await field.textContent())?.includes(expectedValue)) {
        await field.click({ force: true })
        await combobox.pressSequentially(optionText)
        await combobox.press('Enter')
    }

    await expect(field).toContainText(expectedValue)
}

async function submitDialog(dialog) {
    try {
        await dialog.locator('button:has-text("Salvar")').first().click({ force: true })
    } catch (error) {
        if (!/detached from the DOM|Element is not attached/i.test(String(error))) {
            throw error
        }
    }
}

test('credit card create flow works correctly', async ({ page }, testInfo) => {
    const uniqueName = `E2E CARD ${Date.now()}`

    await login(page)
    await page.goto('/credit-card')
    await expect(page.locator('#app')).toBeVisible()

    await page.locator('button:has-text("Novo")').first().click()
    const dialog = await getActiveDialog(page)

    const inputs = dialog.locator('input')
    await inputs.nth(0).fill(uniqueName)
    await inputs.nth(1).fill('5151')
    await pickComboboxOption(dialog, page, 0, '5')
    await pickComboboxOption(dialog, page, 1, '25')
    await pickComboboxOption(dialog, page, 2, 'Sim')

    const createResponsePromise = page
        .waitForResponse(


                (response) => response.request().method() === 'POST' && response.url().includes('/credit-card'),

           {
                timeout: 5000,
            }
        )
        .catch(() => null)

    await submitDialog(dialog)
    const createResponse = await createResponsePromise

    if (!createResponse) {
        const messages = await dialog.locator('.v-messages__message, [role="alert"]').allInnerTexts()

        throw new Error(`Credit card create request was not sent. Visible messages: ${messages.join(' | ') || 'none'}`)
    }

    expect(createResponse.status()).toBeLessThan(500)
    await expect(page.getByText(uniqueName).first()).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-credit-card-smoke.png')
})

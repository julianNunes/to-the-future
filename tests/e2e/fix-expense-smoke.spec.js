import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

function escapeRegExp(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

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
    await combobox.pressSequentially(optionText)
    await combobox.press('ArrowDown')
    await combobox.press('Enter')

    if (!(await field.textContent())?.includes(expectedValue)) {
        const option = page
            .locator('.v-overlay__content:visible .v-list-item', {
                hasText: new RegExp(`^${escapeRegExp(optionText)}$`),
            })
            .first()

        await field.click({ force: true })
        await option.click({ force: true })
    }

    await expect(field).toContainText(expectedValue)
}

async function submitDialog(dialog) {
    await dialog.locator('button:has-text("Salvar")').first().click({ force: true })
}

test('fix expense create flow works correctly', async ({ page }, testInfo) => {
    const uniqueName = `E2E FIX ${Date.now()}`

    await login(page)
    await page.goto('/fix-expense')
    await expect(page.locator('#app')).toBeVisible()

    await page.locator('button:has-text("Novo")').first().click()
    const dialog = await getActiveDialog(page)

    const inputs = dialog.locator('input')
    await inputs.nth(0).fill(uniqueName)
    await inputs.nth(1).fill('150,25')
    await pickComboboxOption(dialog, page, 0, '05')

    await submitDialog(dialog)

    await expect(dialog).not.toBeVisible({ timeout: 10000 })
    await expect(page.getByText(uniqueName).first()).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-fix-expense-smoke.png')
})

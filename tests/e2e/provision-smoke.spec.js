import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, login } from './support/auth'

async function getActiveDialog(page) {
    const dialog = page.locator('[role="dialog"]:visible').last()
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

test('provision create flow works correctly', async ({ page }, testInfo) => {
    const description = `E2E Provision ${Date.now()}`

    await login(page)
    await page.goto('/provision')
    await expect(page.locator('#app')).toBeVisible()

    await page.locator('button:has-text("Novo")').first().click()
    const dialog = await getActiveDialog(page)

    await dialog.getByLabel('Descrição').fill(description)
    await dialog.getByLabel('Valor', { exact: true }).fill('123,45')
    await pickComboboxOption(dialog, page, 0, 'Mensal')

    const createProvisionResponsePromise = page
        .waitForResponse(
            (response) => response.request().method() === 'POST' && response.url().includes('/provision'),
            { timeout: 5000 }
        )
        .catch(() => null)

    await submitDialog(dialog)
    const createProvisionResponse = await createProvisionResponsePromise

    await expect(dialog).not.toBeVisible({ timeout: 10000 })

    if (!createProvisionResponse) {
        const messages = await dialog.locator('.v-messages__message, [role="alert"]').allInnerTexts()

        throw new Error(`Provision create request was not sent. Visible messages: ${messages.join(' | ') || 'none'}`)
    }

    expect(createProvisionResponse.status()).toBeLessThan(500)
    await captureDebugScreenshot(page, testInfo, 'debug-provision-smoke.png')
})

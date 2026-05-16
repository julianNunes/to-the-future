import { expect, test } from '@playwright/test'
import { login } from './support/auth'

async function getActiveDialog(page) {
    const dialog = page.locator('[role="dialog"]:visible').last()
    await expect(dialog).toBeVisible()
    return dialog
}

async function getFinancingInstallmentHref(page) {
    const row = page.locator('tbody tr', { hasText: 'E2E Financing' }).first()

    await expect(row).toBeVisible({ timeout: 10000 })

    const href = await row.locator('a[href$="/installment"]').first().getAttribute('href')

    if (!href) {
        throw new Error('Financing installment link not found for E2E Financing')
    }

    return href
}

test('financing installments page opens the first installment edit dialog', async ({ page }) => {
    await login(page)
    await page.goto('/financing')
    await expect(page.locator('#app')).toBeVisible()

    const installmentHref = await getFinancingInstallmentHref(page)

    await page.goto(installmentHref)
    await expect(page).toHaveURL(/\/financing\/\d+\/installment/)

    const firstInstallmentRow = page.locator('tbody tr').first()
    await expect(firstInstallmentRow).toBeVisible({ timeout: 10000 })

    await firstInstallmentRow.locator('.mdi-pencil').first().click({ force: true })

    const dialog = await getActiveDialog(page)

    await expect(dialog.locator('button:has-text("Salvar")').first()).toBeVisible()
    await expect(dialog.locator('button:has-text("Cancelar")').first()).toBeVisible()
})

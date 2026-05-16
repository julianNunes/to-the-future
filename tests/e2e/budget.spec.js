import { expect, test } from '@playwright/test'
import { captureDebugScreenshot, e2eBudget, login } from './support/auth'

test.describe.configure({ mode: 'serial' })

async function getActiveDialog(page) {
    const dialog = page.locator('[role="dialog"]:visible').last()
    await expect(dialog).toBeVisible()
    return dialog
}

async function submitDialog(dialog) {
    await dialog.locator('button:has-text("Salvar")').first().click({ force: true })
}

async function setMonthValue(dialog, value) {
    const monthInput = dialog.locator('input[type="month"]').first()

    await monthInput.evaluate((element, newValue) => {
        element.value = newValue
        element.dispatchEvent(new Event('input', { bubbles: true }))
        element.dispatchEvent(new Event('change', { bubbles: true }))
    }, value)

    await expect(monthInput).toHaveValue(value)
}

async function findAvailableBudgetPeriod(page, year) {
    await page.goto(`/budget/${year}`)
    await expect(page.locator('#app')).toBeVisible()

    const currentMonth = new Date().getMonth()

    for (let offset = 0; offset < 12; offset++) {
        const month = String(((currentMonth + offset) % 12) + 1).padStart(2, '0')
        const display = `${month}/${year}`
        const row = page.locator('tbody tr', { hasText: display })

        if ((await row.count()) === 0) {
            return { year, month, display }
        }
    }

    throw new Error(`No available budget period found for ${year}`)
}

async function openBudgetShow(page, year, month) {
    await page.goto(`/budget/${year}`)
    await expect(page.locator('#app')).toBeVisible()

    const display = `${month}/${year}`
    const sourceRow = page.locator('tbody tr', { hasText: display }).first()

    await expect(sourceRow).toBeVisible({ timeout: 10000 })

    const showHref = await sourceRow.locator('a[href*="/budget/show/"]').first().getAttribute('href')

    if (!showHref) {
        throw new Error(`Budget show link not found for ${display}`)
    }

    await page.goto(showHref)
    await expect(page).toHaveURL(/\/budget\/show\//)
}

test('budget create and show flow works correctly', async ({ page }, testInfo) => {
    await login(page)
    const targetPeriod = await findAvailableBudgetPeriod(page, '2050')

    await page.goto(`/budget/${e2eBudget.year}`)
    await expect(page.locator('#app')).toBeVisible()

    await page.locator('button:has-text("Novo")').first().click()
    const dialog = await getActiveDialog(page)

    await setMonthValue(dialog, `${targetPeriod.year}-${targetPeriod.month}`)
    const createResponsePromise = page
        .waitForResponse((response) => response.request().method() === 'POST' && response.url().includes('/budget'), {
            timeout: 5000,
        })
        .catch(() => null)
    await submitDialog(dialog)
    const createResponse = await createResponsePromise

    if (!createResponse) {
        const messages = await dialog.locator('.v-messages__message, [role="alert"]').allInnerTexts()

        throw new Error(`Budget create request was not sent. Visible messages: ${messages.join(' | ') || 'none'}`)
    }

    const createStatus = createResponse.status()

    expect(createStatus).toBeLessThan(500)
    await page.waitForTimeout(1000)

    await page.goto(`/budget/${targetPeriod.year}`)
    const createdBudgetLabel = page.locator('tbody').getByText(targetPeriod.display).first()

    await expect(createdBudgetLabel).toBeVisible({ timeout: 10000 })

    const showHref = await page.locator('tbody a[href*="/budget/show/"]').first().getAttribute('href')

    if (!showHref) {
        throw new Error(`Budget show link not found for ${targetPeriod.display}`)
    }

    await page.goto(showHref)

    await expect(page).toHaveURL(/\/budget\/show\//)
    await expect(page.getByText('Meu Orçamento').first()).toBeVisible({ timeout: 10000 })
    await expect(page.getByRole('button', { name: 'Incluir Despesas Fixas' })).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-budget-create-show.png')
})

test('budget clone flow works correctly', async ({ page }, testInfo) => {
    await login(page)
    const targetPeriod = await findAvailableBudgetPeriod(page, '2049')

    await page.goto(`/budget/${e2eBudget.year}`)
    await expect(page.locator('#app')).toBeVisible()

    const sourceRow = page.locator('tbody tr', { hasText: `${e2eBudget.month}/${e2eBudget.year}` }).first()
    await expect(sourceRow).toBeVisible({ timeout: 10000 })

    await page.locator('.mdi-content-copy').first().click({ force: true })

    const dialog = await getActiveDialog(page)
    await setMonthValue(dialog, `${targetPeriod.year}-${targetPeriod.month}`)
    const cloneResponsePromise = page
        .waitForResponse(
            (response) => response.request().method() === 'PUT' && response.url().includes('/budget/clone/'),
            { timeout: 5000 }
        )
        .catch(() => null)
    await submitDialog(dialog)
    const cloneResponse = await cloneResponsePromise

    if (!cloneResponse) {
        const messages = await dialog.locator('.v-messages__message, [role="alert"]').allInnerTexts()

        throw new Error(`Budget clone request was not sent. Visible messages: ${messages.join(' | ') || 'none'}`)
    }

    const cloneStatus = cloneResponse.status()

    expect(cloneStatus).toBeLessThan(500)
    await page.waitForTimeout(1000)

    await page.goto(`/budget/${targetPeriod.year}`)
    const clonedBudgetLabel = page.locator('tbody').getByText(targetPeriod.display).first()

    await expect(clonedBudgetLabel).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-budget-clone.png')
})

test('budget show creates an income entry through the form', async ({ page }, testInfo) => {
    await login(page)
    await openBudgetShow(page, e2eBudget.year, e2eBudget.month)

    const incomePanel = page.locator('.v-expansion-panel', { hasText: 'Receitas' }).first()
    await expect(incomePanel).toBeVisible({ timeout: 10000 })
    const incomePanelTitle = incomePanel.locator('.v-expansion-panel-title').first()

    const newIncomeButton = incomePanel.getByRole('button', { name: 'Novo' }).first()

    if (!(await newIncomeButton.isVisible())) {
        await incomePanelTitle.click()
    }

    await expect(newIncomeButton).toBeVisible({ timeout: 10000 })
    await newIncomeButton.evaluate((element) => element.click())

    const dialog = await getActiveDialog(page)
    const description = `Receita E2E ${Date.now()}`

    await dialog.getByLabel('Descrição').fill(description)
    await dialog.getByLabel('Valor').fill('123,45')

    const createIncomeResponsePromise = page
        .waitForResponse(
            (response) => response.request().method() === 'POST' && response.url().includes('/budget-income'),
            { timeout: 5000 }
        )
        .catch(() => null)

    await submitDialog(dialog)
    const createIncomeResponse = await createIncomeResponsePromise

    if (!createIncomeResponse) {
        const messages = await dialog.locator('.v-messages__message, [role="alert"]').allInnerTexts()

        throw new Error(
            `Budget income create request was not sent. Visible messages: ${messages.join(' | ') || 'none'}`
        )
    }

    expect(createIncomeResponse.status()).toBeLessThan(500)
    await expect(page.getByText(description).first()).toBeVisible({ timeout: 10000 })
    await captureDebugScreenshot(page, testInfo, 'debug-budget-income-create.png')
})

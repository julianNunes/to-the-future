const { chromium } = require('playwright')

;(async () => {
    const browser = await chromium.launch()
    const context = await browser.newContext()
    const page = await context.newPage()

    try {
        await page.goto('http://nginx/login')
        await page.fill('input[name="email"]', 'e2e@example.com')
        await page.fill('input[name="password"]', 'password')
        await page.click('button[type="submit"]')
        await page.waitForURL('**/dashboard')

        await page.goto('http://nginx/credit-card')
        await page.click('button:has-text("Novo")')

        const modal = page.locator('.modal-content, [role="dialog"]')
        await page.fill('input[name="name"]', 'Test Card')
        await page.fill('input[name="digits"]', '1234')

        // Selecting dates might depend on the component, assuming standard selects or inputs
        await page.selectOption('select[name="due_date"]', '5')
        await page.selectOption('select[name="closing_date"]', '25')
        await page.selectOption('select[name="active"]', '1') // 'Sim' usually maps to 1

        await page.click('button:has-text("Salvar")')

        // Wait a bit for potential response/redirect
        await page.waitForTimeout(2000)

        const currentUrl = page.url()
        const isVisible = await modal.isVisible()
        const errorMessages = await page.locator('.text-danger, .invalid-feedback, [role="alert"]').allInnerTexts()
        const nameFound = await page
            .locator('body')
            .innerText()
            .then((text) => text.includes('Test Card'))

        console.log(
            JSON.stringify(
                {
                    currentUrl,
                    modalVisible: isVisible,
                    errorMessages: errorMessages.filter((t) => t.trim() !== ''),
                    nameAppears: nameFound,
                },
                null,
                2
            )
        )
    } catch (err) {
        console.error(err)
    } finally {
        await browser.close()
    }
})()

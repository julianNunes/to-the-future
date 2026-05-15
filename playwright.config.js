// playwright.config.js
import { defineConfig, devices } from '@playwright/test'
import { existsSync } from 'node:fs'

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    outputDir: './.playwright/test-results',
    reporter: [['html', { outputFolder: './.playwright/report', open: 'never' }]],
    use: {
        baseURL: 'http://nginx:80', // In Docker network, the nginx container is 'nginx' on port 80 (mapped to 8080 host)
        trace: 'on-first-retry',
        launchOptions: {
            executablePath: [
                process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH,
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
            ].find((candidate) => candidate && existsSync(candidate)),
            args: ['--no-sandbox', '--disable-dev-shm-usage'],
        },
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
})

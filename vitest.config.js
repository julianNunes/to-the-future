import path from 'path'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vitest/config'

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    test: {
        globals: true,
        environment: 'jsdom',
        include: ['resources/js/**/*.{test,spec}.{js,ts}', 'tests/frontend/**/*.{test,spec}.{js,ts}'],
    },
})
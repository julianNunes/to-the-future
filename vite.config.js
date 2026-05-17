import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'
import vuetify from 'vite-plugin-vuetify'
// import eslintPlugin from 'vite-plugin-eslint' // Temporariamente desabilitado
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({ autoImport: true }),
        // eslintPlugin(), // Temporariamente desabilitado
    ].filter(Boolean),
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            moment: path.resolve(__dirname, 'resources/js/utils/date.js'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            clientPort: 5173,
        },
        watch: {
            usePolling: true,
        },
    },
    build: {
        minify: 'esbuild',
        target: 'es2015',
        sourcemap: false,
        chunkSizeWarningLimit: 650,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return undefined
                    }

                    if (id.includes('vuetify') || id.includes('@mdi') || id.includes('vuetify-money-3')) {
                        return 'vuetify-vendor'
                    }

                    if (id.includes('vue3-apexcharts')) {
                        return 'charts-wrapper'
                    }

                    if (id.includes('node_modules/apexcharts/')) {
                        return 'charts-core'
                    }

                    if (id.includes('read-excel-file') || id.includes('write-excel-file')) {
                        return 'excel-vendor'
                    }

                    if (id.includes('@inertiajs') || id.includes('vue') || id.includes('axios')) {
                        return 'app-vendor'
                    }

                    return 'vendor'
                },
            },
        },
    },
})

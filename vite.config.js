import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
// import eslintPlugin from 'vite-plugin-eslint' // Temporariamente desabilitado
import path from 'path'

const isProduction = process.env.NODE_ENV === 'production'

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
        // !isProduction && eslintPlugin(), // Temporariamente desabilitado
    ].filter(Boolean),
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
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
    },
})

import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import eslintPlugin from 'vite-plugin-eslint'

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
        eslintPlugin(),
    ],
    server: {
        host: '0.0.0.0', // Isso faz com que o Vite escute em todas as interfaces
        port: 5173, // Garante que a porta seja 5173
        hmr: {
            host: 'localhost', // Use 'localhost' para o HMR no navegador
            clientPort: 5173,
        },
        watch: {
            usePolling: true // Necessário para alguns sistemas de arquivos em Docker (ex: WSL2, macOS)
        }
    },
})

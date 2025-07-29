const globals = require("globals");
const js = require("@eslint/js");
const vue = require("eslint-plugin-vue");
const prettier = require("eslint-plugin-prettier");

module.exports = [
    js.configs.recommended,
    ...vue.configs["flat/recommended"],
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                _: true,
                route: true,  // Laravel route helper
                axios: true,  // Global axios
            },
        },

        plugins: {
            vue,
            prettier,
        },

        rules: {
            "prettier/prettier": ["error"],
            "vue/require-default-prop": "off",
            "vue/multi-word-component-names": "off",
            "vue/no-v-html": "off", // Para Inertia.js às vezes é necessário
            "no-unused-vars": ["error", { "argsIgnorePattern": "^_" }],
            "no-console": "warn",
            "no-debugger": "warn"
        },

        ignores: [
            "node_modules/**",
            "vendor/**",
            "public/build/**",
            "storage/**",
            "bootstrap/cache/**"
        ]
    }
];

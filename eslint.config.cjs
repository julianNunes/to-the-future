const {
    defineConfig,
} = require("eslint/config");

const globals = require("globals");
const prettier = require("eslint-plugin-prettier");
const js = require("@eslint/js");

const {
    FlatCompat,
} = require("@eslint/eslintrc");

const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
});

module.exports = defineConfig([{
    languageOptions: {
        globals: {
            ...globals.browser,
            ...globals.node,
            _: true,
            route: true,  // Laravel route helper
            axios: true,  // Global axios
        },
    },

    extends: compat.extends(
        "eslint:recommended", 
        "plugin:vue/vue3-recommended", 
        "prettier"
    ),

    plugins: {
        prettier,
    },

    rules: {
        "prettier/prettier": ["error"],
        "vue/require-default-prop": "off",
        "vue/multi-word-component-names": "off",
        "vue/no-v-html": "off", // Para Inertia.js às vezes é necessário
        "vue/script-setup-uses-vars": "error",
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
}]);

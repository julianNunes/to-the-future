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
        },
    },

    extends: compat.extends("eslint:recommended", "plugin:vue/recommended", "prettier"),

    plugins: {
        prettier,
    },

    rules: {
        "prettier/prettier": ["error"],
        "vue/require-default-prop": "off",
    },
}]);

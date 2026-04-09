import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";

export default [
    {
        ignores: [
            "vendor/**",
            "node_modules/**",
            "public/build/**",
            "bootstrap/**",
            "storage/**",
            "coverage/**",
        ],
    },
    js.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    {
        files: ["resources/js/**/*.{js,vue}"],
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                ...globals.browser,
            },
        },
        rules: {
            "no-console": "warn",
            "no-debugger": "warn",
            "vue/multi-word-component-names": "off",
        },
    },
];

// vite.config.js

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/

const path = require("path");
export default defineConfig({
    plugins: [vue()],
    resolve: {
        extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
        alias: {
            "@": path.resolve(__dirname, "./src"),
        },
    },
    base: '/typo3conf/ext/ibcontent/Resources/Public/build/osmmap/',
    build: {
        outDir: '../../build/osmmap/',
        rollupOptions: {
            output: {
                entryFileNames: `js/osmMapApp.js`,
                chunkFileNames: `js/osmMapApp.js`,
                assetFileNames: `assets/[name].[ext]`
            }
        }
    }
})

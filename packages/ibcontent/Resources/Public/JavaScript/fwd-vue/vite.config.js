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
    base: '/typo3conf/ext/ibcontent/Resources/Public/build/fwd/',
    build: {
        outDir: '../../build/fwd/',
        rollupOptions: {
            output: {
                entryFileNames: `js/fwd.js`,
                chunkFileNames: `js/fwd.js`,
                assetFileNames: `assets/[name].[ext]`
            }
        }
    }
})

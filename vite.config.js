import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';
/**
 * ============================================================
 * Vite configuration file for Laravel + Vue + Tailwind setup
 * ------------------------------------------------------------
 * - Integrates Laravel's asset bundler with Vite
 * - Enables Vue Single File Components (SFC)
 * - Applies TailwindCSS styling
 * - Defines path aliases for cleaner imports
 * ============================================================
 */
export default defineConfig({
    plugins: [
        // Laravel Vite plugin: handles asset compilation and auto-refresh on changes
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),

        // TailwindCSS integration
        tailwindcss(),

        // Vue plugin: enables Vue 3 support with Single File Components
        vue()
    ],
    resolve: {
        alias: {
            // Shortcut '@' → points to 'resources/js' for cleaner imports
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',            // listen on all interfaces inside the container
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',        // HMR URL seen by the browser
            port: 5173,
            protocol: 'ws'
        },
        proxy: {
            '/api': 'http://localhost:8000' // forward API calls to Laravel in Docker
        }
    }
});

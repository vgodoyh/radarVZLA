import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/css/app_public.css',
                'resources/css/public-hero.css',
                'resources/css/public-hero-v2.css',
                'resources/css/public-hero-v3.css',
                'resources/js/app.js',
                'resources/js/admin-analytics.js',
                'resources/js/app_public.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                }
            }
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '@assets': '/resources/assets',
            '@scss': '/resources/scss',
            '@components': '/resources/js/components',
            '@layouts': '/resources/js/layouts',
            '@pages': '/resources/js/pages',
            '@stores': '/resources/js/stores',
            '@utils': '/resources/js/utils',
        },
    },
});

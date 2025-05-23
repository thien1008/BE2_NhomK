import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import autoprefixer from 'autoprefixer';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/login-register.css',
                'resources/css/styles-home.css',
                'resources/css/styles-checkout.css',
                'resources/css/styles-ctsp.css',
                'resources/js/scripts-home.js',
                'resources/js/scripts-ctsp.js',
                'resources/js/scripts-cart.js',
                'resources/js/cart-shared.js',
                'resources/js/scripts-checkout.js'
            ],
            refresh: true,
        }),
    ],
    css: {
        postcss: {
            plugins: [
                autoprefixer(),
            ],
        },
    },
});
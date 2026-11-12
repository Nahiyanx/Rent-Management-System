import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Bind to every interface so the built assets (and dev server) can be
        // reached from a phone on the same network.
        host: '0.0.0.0',
        // Browsers reject "0.0.0.0" as an address, so the hot file that Laravel
        // reads must point at a real host. Keep hot reload on localhost; use
        // `npm run build` when testing over the LAN.
        hmr: {
            host: 'localhost',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

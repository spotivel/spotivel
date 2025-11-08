import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/themes/admin/theme.css'],
            refresh: true,
        }),
    ],
});

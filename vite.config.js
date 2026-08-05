import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/admin/theme.css'],
            refresh: true,
            fonts: [
                // Poppins es la tipografía principal del manual de marca de
                // Asobares Colombia. Los pesos son los cuatro que documenta:
                // Light, Medium, Bold y Black (más Regular y SemiBold de apoyo).
                bunny('Poppins', {
                    weights: [300, 400, 500, 600, 700, 900],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

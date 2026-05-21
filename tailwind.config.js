import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                // Sombra suave y amplia de la tarjeta principal
                'medical-card': '0 22px 70px 4px rgba(0,0,0,0.06)',
                // Sombra de resplandor para el botón
                'medical-btn': '0 8px 30px rgb(37,99,235,0.2)',
            },
            ringWidth: {
                // Soporte para ring-opacity (se usa en los inputs)
                DEFAULT: '2px',
                'ring-opacity': '0.5',
            },
        },
    },

    plugins: [forms],
};
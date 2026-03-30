import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
                heading: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#000000', // Pure Black
                    light: '#1f1f1f'
                },
                accent: {
                    DEFAULT: '#064E3B', // Dark Green
                    hover: '#022C22',
                    light: '#F0FDF4' // Light Green
                },
                surface: {
                    DEFAULT: '#ffffff',
                    fade: '#FAFAFA',
                },
                textBase: '#333333',
                textMuted: '#666666',
            },
            boxShadow: {
                'luxury': '0 10px 40px -10px rgba(0, 0, 0, 0.1)',
            }
        },
    },

    plugins: [forms],
};

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                background: {
                    primary: '#020817',
                    secondary: '#0B0F1A',
                },
                border: {
                    primary: '#1f2937', // gray-800
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            ringColor: {
                'blue-500/20': 'rgb(59 130 246 / 0.2)',
            },
        },
    },

    plugins: [forms({
        strategy: 'class',
    })],
};

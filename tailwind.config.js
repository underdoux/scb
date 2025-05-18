import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                background: {
                    primary: '#020817',
                    secondary: '#0B0F1A',
                },
                border: {
                    primary: '#1f2937',
                },
            },
            fontFamily: {
                sans: ['Inter var', ...defaultTheme.fontFamily.sans],
            },
            transitionTimingFunction: {
                'out-back': 'cubic-bezier(0.34, 1.56, 0.64, 1)',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'fade-out': {
                    '0%': { opacity: '1' },
                    '100%': { opacity: '0' },
                },
                'slide-in': {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                'slide-out': {
                    '0%': { transform: 'translateY(0)', opacity: '1' },
                    '100%': { transform: 'translateY(10px)', opacity: '0' },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.2s ease-out',
                'fade-out': 'fade-out 0.2s ease-in',
                'slide-in': 'slide-in 0.2s ease-out',
                'slide-out': 'slide-out 0.2s ease-in',
            },
        },
    },
    plugins: [
        forms,
        typography,
    ],
};

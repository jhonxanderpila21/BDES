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
            colors: {
                bdes: {
                    navy: '#003366',
                    'royal-blue': '#2563EB',
                    green: '#2E7D32',
                    amber: '#F59E0B',
                    red: '#DC2626',
                    'light-blue': '#60A5FA',
                    charcoal: '#1F2937',
                    'slate-gray': '#6B7280',
                    'light-gray': '#E5E7EB',
                    'soft-white': '#FAFAF9',
                    white: '#FFFFFF',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};

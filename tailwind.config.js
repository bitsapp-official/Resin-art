import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Enums/**/*.php',
        './app/Filament/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                serif: ['Cormorant Garamond', 'Playfair Display', ...defaultTheme.fontFamily.serif],
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                atelier: {
                    50: '#FDFBF7',
                    100: '#F7F3EB',
                    200: '#EFE8DA',
                    300: '#DFD4C0',
                    400: '#C7B69C',
                    500: '#AD9575',
                    600: '#8E7558',
                    700: '#6E5842',
                    800: '#4D3C2C',
                    900: '#2A2016',
                    950: '#140E0A',
                },
                accent: {
                    olive: '#4A5D4E',
                    bronze: '#9A7B4F',
                    clay: '#C28B6A',
                }
            },
            boxShadow: {
                'atelier-soft': '0 20px 40px -15px rgba(42, 32, 22, 0.05)',
                'atelier-card': '0 10px 30px -10px rgba(0, 0, 0, 0.04)',
                'atelier-hover': '0 25px 50px -12px rgba(42, 32, 22, 0.08)',
            },
            borderRadius: {
                '4xl': '2rem',
            }
        },
    },
    plugins: [forms, typography],
};

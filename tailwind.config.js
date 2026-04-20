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
                sans: ['Figtree', 'system-ui', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                /**
                 * Skala fluid: vw disesuaikan agar tidak “membesar” berdasarkan lebar viewport penuh
                 * saat konten sebenarnya lebih sempit (tablet, split view). Max tetap nyaman di desktop.
                 */
                'fluid-xs': ['clamp(0.6875rem, 0.65rem + 0.2vw, 0.75rem)', { lineHeight: '1.4' }],
                'fluid-sm': ['clamp(0.8125rem, 0.76rem + 0.25vw, 0.875rem)', { lineHeight: '1.45' }],
                'fluid-base': ['clamp(0.9375rem, 0.85rem + 0.35vw, 1rem)', { lineHeight: '1.5' }],
                'fluid-lg': ['clamp(1rem, 0.92rem + 0.45vw, 1.125rem)', { lineHeight: '1.45' }],
                'fluid-xl': ['clamp(1.125rem, 1rem + 0.55vw, 1.375rem)', { lineHeight: '1.35' }],
                'fluid-2xl': ['clamp(1.25rem, 1.05rem + 0.75vw, 1.75rem)', { lineHeight: '1.3' }],
                'fluid-3xl': ['clamp(1.375rem, 1.1rem + 1vw, 2.125rem)', { lineHeight: '1.25' }],
                /** Judul halaman: lebih konservatif agar tidak memaksa pecah kata di tablet */
                'fluid-page': ['clamp(1.0625rem, 0.95rem + 0.5vw, 1.625rem)', { lineHeight: '1.25' }],
            },
            minHeight: {
                touch: '2.75rem',
            },
            minWidth: {
                touch: '2.75rem',
            },
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                success: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                warning: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                    800: '#92400e',
                    900: '#78350f',
                },
                danger: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
            },
        },
    },

    plugins: [forms],
};

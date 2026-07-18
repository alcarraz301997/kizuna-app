import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: 'rgb(var(--color-primary) / <alpha-value>)',
                    hover: 'rgb(var(--color-primary-hover) / <alpha-value>)',
                    light: 'rgb(var(--color-primary-light) / <alpha-value>)',
                    foreground: 'rgb(var(--color-primary-foreground) / <alpha-value>)',
                },
                secondary: {
                    DEFAULT: 'rgb(var(--color-secondary) / <alpha-value>)',
                    light: 'rgb(var(--color-secondary-light) / <alpha-value>)',
                    foreground: 'rgb(var(--color-secondary-foreground) / <alpha-value>)',
                },
                accent: {
                    DEFAULT: 'rgb(var(--color-accent) / <alpha-value>)',
                    light: 'rgb(var(--color-accent-light) / <alpha-value>)',
                },
                surface: {
                    DEFAULT: 'rgb(var(--color-surface) / <alpha-value>)',
                    alt: 'rgb(var(--color-surface-alt) / <alpha-value>)',
                    elevated: 'rgb(var(--color-surface-elevated) / <alpha-value>)',
                },
                muted: {
                    DEFAULT: 'rgb(var(--color-muted) / <alpha-value>)',
                    foreground: 'rgb(var(--color-muted-foreground) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--color-success) / <alpha-value>)',
                    light: 'rgb(var(--color-success-light) / <alpha-value>)',
                },
                warning: {
                    DEFAULT: 'rgb(var(--color-warning) / <alpha-value>)',
                    light: 'rgb(var(--color-warning-light) / <alpha-value>)',
                },
                danger: {
                    DEFAULT: 'rgb(var(--color-danger) / <alpha-value>)',
                    light: 'rgb(var(--color-danger-light) / <alpha-value>)',
                },
                border: {
                    DEFAULT: 'rgb(var(--color-border) / <alpha-value>)',
                    light: 'rgb(var(--color-border-light) / <alpha-value>)',
                },
            },
            minHeight: {
                touch: '44px',
            },
            fontSize: {
                'base-mobile': ['1rem', { lineHeight: '1.5rem' }],
            },
        },
    },

    plugins: [forms],
};

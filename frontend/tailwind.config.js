/** @type {import('tailwindcss').Config} */
import primeui from 'tailwindcss-primeui';

export default {
  content: ['./index.html', './src/**/*.{vue,js,ts}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Noto Sans Thai', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
      },
      // Legacy design tokens — mirror resources/views/layouts/main.php
      colors: {
        'dark-bg': '#0f172a',
        'dark-card': '#1e293b',
        'dark-text': '#f1f5f9',
        'dark-muted': '#94a3b8',
        'dark-border': '#334155',
        primary: {
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0369a1',
          700: '#075985',
        },
      },
    },
  },
  plugins: [primeui],
}

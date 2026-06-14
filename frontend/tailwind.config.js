/** @type {import('tailwindcss').Config} */
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
          600: '#0284c7',
        },
      },
    },
  },
  plugins: [require('tailwindcss-primeui')],
}

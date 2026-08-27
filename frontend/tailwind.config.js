/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Vazirmatn', 'system-ui', 'sans-serif'],
      },
      colors: {
        molido: {
          cyan: '#00e5ff',
          purple: '#a78bfa',
          dark: '#07070c',
        },
      },
    },
  },
  plugins: [],
}

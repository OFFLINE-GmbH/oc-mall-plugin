
import Colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './src/localize/client/index.html',
        './src/localize/client/**/*.vue'
    ],
    darkMode: 'class',
    theme: {
        extend: { 
            colors: {
                gray: Colors.neutral,
                primary: Colors.lime,
                danger: Colors.red,
                warning: Colors.amber,
                success: Colors.emerald,
                info: Colors.sky
            },
        }
    },
    plugins: []
};

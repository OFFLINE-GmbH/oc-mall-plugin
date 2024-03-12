module.exports = {
    plugins: [
        require('postcss-import')(),
        require('tailwindcss/nesting')(),
        require('tailwindcss')({
            config: 'src/localize/tailwind.config.js',
        }),
        require('autoprefixer')(),
    ]
};
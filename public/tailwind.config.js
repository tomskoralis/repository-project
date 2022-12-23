/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        '../views/**/*.twig',
        './scripts/scripts.js'
    ],
    plugins: [
        require('flowbite/plugin')
    ],
    darkMode: 'class'
}
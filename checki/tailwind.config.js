/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./admin/views/**/*.php",
    "./admin/index.php",
  ],
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["night", "nord", "business"],
    darkTheme: "night",
    base: true,
    styled: true,
    utils: true,
  },
}

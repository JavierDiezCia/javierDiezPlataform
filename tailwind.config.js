/** @type {import('tailwindcss').Config} */
import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

module.exports = {
  content: ["./src/**/*.{html,js,php}", "./src/public/*.php"],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Figtree", ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [forms],
};

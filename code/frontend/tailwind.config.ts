import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: "#EA580C", // Orange-600
          hover: "#C2410C",   // Orange-700
          light: "#FFF7ED",   // Orange-50
        },
        kds: {
          green: "#16A34A",
          yellow: "#D97706",
          red: "#DC2626",
          blue: "#2563EB",
        }
      },
      fontFamily: {
        sans: ["var(--font-inter)", "sans-serif"],
      },
    },
  },
  plugins: [],
};
export default config;

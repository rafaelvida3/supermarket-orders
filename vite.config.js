import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";
import { defineConfig } from "vite";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true,
    }),
    tailwindcss(),
    vue(),
  ],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "resources/js"),
    },
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    strictPort: true,
    watch: {
      usePolling: true,
      interval: 1000,
    },
    hmr: {
      host: "localhost",
      port: 5173,
      protocol: "ws",
    },
    proxy: {
      "/api": "http://app:8000",
    },
  },
});

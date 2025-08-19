// vite.config.js
import { defineConfig } from "vite";
import path from "node:path";

export default defineConfig({
    root: process.cwd(),
    build: {
        outDir: "resources/dist",
        emptyOutDir: false,
        sourcemap: true,
        lib: {
            entry: "resources/js/components/searchable-input.js",
            name: "SearchableInput",
            formats: ["es"],
            fileName: () => "filament-searchable-input.js"
        },
        rollupOptions: {
            external: []
        }
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js")
        }
    }
});

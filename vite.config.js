import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig(({ command, mode }) => {
    const config = {
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                ssr: 'resources/js/ssr.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
                '~': fileURLToPath(new URL('./resources', import.meta.url)),
            },
        },
        build: {
            chunkSizeWarningLimit: 1000,
        },
    };

    if (command === 'build' && !mode === 'ssr') {
        config.build.rollupOptions = {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules')) {
                        if (id.includes('lucide-vue-next')) {
                            return 'vendor-icons';
                        }
                        return 'vendor';
                    }
                },
            },
        };
    }

    if (mode === 'ssr') {
        config.ssr = {
            noExternal: ['@inertiajs/server', '@vue/server-renderer', 'lucide-vue-next'],
        };
    }

    return config;
});

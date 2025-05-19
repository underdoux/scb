import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 8000,
    strictPort: false,
    hmr: {
      protocol: 'ws',
      host: 'localhost',
      port: 8015
    },
    watch: {
      usePolling: true,
      interval: 1000
    },
    proxy: {
      '^/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false
      },
      '^/oauth': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false
      }
    }
  },
  optimizeDeps: {
    include: ['vue', 'vue-router']
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets'
  }
})

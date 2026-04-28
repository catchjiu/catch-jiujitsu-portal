import path from 'path';
import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, '.', '');
    const apiUrl = env.VITE_API_URL || 'http://localhost:8000';
    return {
      base: process.env.VITE_BASE_PATH || '/portal/',
      server: {
        port: 3000,
        host: '0.0.0.0',
        proxy: {
          '/api': {
            target: apiUrl,
            changeOrigin: true,
          },
        },
        watch: {
          ignored: ['**/laravel/**', '**/node_modules/**'],
        },
      },
      build: {
        outDir: 'public/portal',
        emptyOutDir: true,
        rollupOptions: {
          input: ['index.html', 'checkin.html'],
        },
      },
      plugins: [react()],
      define: {
        'process.env.API_KEY': JSON.stringify(env.GEMINI_API_KEY),
        'process.env.GEMINI_API_KEY': JSON.stringify(env.GEMINI_API_KEY)
      },
      resolve: {
        alias: {
          '@': path.resolve(__dirname, '.'),
        }
      }
    };
});

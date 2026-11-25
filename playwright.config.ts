import { defineConfig } from '@playwright/test';

/**
 * Playwright API-Level E2E Configuration.
 * Notice: This is strictly an API test suite (no browser binary required).
 * It sends real HTTP requests over the network to a running Laravel API server.
 */
export default defineConfig({
  testDir: './tests-e2e',
  timeout: 30000,
  fullyParallel: false, // Run journeys sequentially to avoid database lock collisions
  retries: 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'on-failure', outputFolder: 'tests-e2e/report' }]],
  use: {
    baseURL: process.env.API_URL || 'http://127.0.0.1:8000',
    extraHTTPHeaders: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    ignoreHTTPSErrors: true,
    trace: 'retain-on-failure',
  },
});


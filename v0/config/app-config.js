// Application Configuration
export const AppConfig = {
  name: "MPSM Dashboard",
  version: "1.0.0",

  // API Configuration
  api: {
    baseUrl: "/api/v3",
    timeout: 5000,
    retryAttempts: 3,
  },

  // Dashboard Configuration
  dashboard: {
    refreshInterval: 30000, // 30 seconds
    maxCards: 12,
    animationDuration: 300,
  },

  // Feature Flags
  features: {
    realTimeUpdates: true,
    darkModeOnly: true,
    responsiveLayout: true,
    mockApi: true,
  },

  // Debug Settings
  debug: {
    enabled: true,
    logLevel: "info", // 'debug', 'info', 'warn', 'error'
  },
}

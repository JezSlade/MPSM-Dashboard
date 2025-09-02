// Application Constants
export const API_ENDPOINTS = {
  DASHBOARD: "/api/v3/Dashboard/Get",
  CUSTOMERS: "/api/v3/Customer/GetCustomers",
  CUSTOMER_DETAIL: "/api/v3/Customer/GetCustomer",
  DEVICES: "/api/v3/Device/GetDevices",
  DEVICE_DETAIL: "/api/v3/Device/GetDevice",
  ALERTS: "/api/v3/Alert/GetAlerts",
  ALERT_ACKNOWLEDGE: "/api/v3/Alert/Acknowledge",
}

export const STATUS_TYPES = {
  ONLINE: "online",
  OFFLINE: "offline",
  MAINTENANCE: "maintenance",
  ACTIVE: "active",
  INACTIVE: "inactive",
  PENDING: "pending",
}

export const SEVERITY_LEVELS = {
  CRITICAL: "critical",
  WARNING: "warning",
  INFO: "info",
  LOW: "low",
  MEDIUM: "medium",
  HIGH: "high",
}

export const DEVICE_TYPES = {
  PRINTER: "printer",
  SCANNER: "scanner",
  COPIER: "copier",
  FAX: "fax",
  MULTIFUNCTION: "multifunction",
}

export const CUSTOMER_TIERS = {
  BASIC: "basic",
  PROFESSIONAL: "professional",
  ENTERPRISE: "enterprise",
}

export const UI_CONSTANTS = {
  ANIMATION_DURATION: 300,
  DEBOUNCE_DELAY: 500,
  TOAST_DURATION: 5000,
  REFRESH_INTERVALS: {
    FAST: 10000, // 10 seconds
    NORMAL: 30000, // 30 seconds
    SLOW: 60000, // 1 minute
  },
}

export const ERROR_MESSAGES = {
  NETWORK_ERROR: "Network connection error. Please check your internet connection.",
  API_ERROR: "Server error. Please try again later.",
  DATA_LOAD_ERROR: "Failed to load data. Please refresh the page.",
  PERMISSION_ERROR: "You don't have permission to perform this action.",
  VALIDATION_ERROR: "Please check your input and try again.",
}

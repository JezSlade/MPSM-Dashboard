// App Controller - Main application controller that coordinates all other controllers
import { DashboardController } from "./dashboard-controller.js"
import { DeviceController } from "./device-controller.js"
import { AlertController } from "./alert-controller.js"
import { CustomerController } from "./customer-controller.js"

export class AppController {
  constructor() {
    this.dashboard = new DashboardController()
    this.devices = new DeviceController()
    this.alerts = new AlertController()
    this.customers = new CustomerController()

    this.isInitialized = false
    this.setupEventListeners()
  }

  async initialize() {
    if (this.isInitialized) {
      console.log("App controller already initialized")
      return
    }

    try {
      console.log("🎮 Initializing app controllers...")

      // Load initial data in parallel
      await Promise.all([
        this.dashboard.loadDashboard(),
        this.devices.loadDevices(),
        this.alerts.loadAlerts({ status: "active" }),
        this.customers.loadCustomers(),
      ])

      this.isInitialized = true
      console.log("✅ App controllers initialized successfully")

      // Notify that app is ready
      this.notifyAppReady()
    } catch (error) {
      console.error("❌ Failed to initialize app controllers:", error)
      throw error
    }
  }

  setupEventListeners() {
    // Listen for dashboard data updates
    document.addEventListener("dashboardDataUpdated", (event) => {
      console.log("📊 Dashboard data updated", event.detail)
    })

    // Listen for visibility changes to pause/resume updates
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        this.dashboard.stopAutoRefresh()
      } else {
        this.dashboard.startAutoRefresh()
      }
    })

    // Listen for online/offline events
    window.addEventListener("online", () => {
      console.log("🌐 Connection restored")
      this.handleConnectionRestore()
    })

    window.addEventListener("offline", () => {
      console.log("📴 Connection lost")
      this.handleConnectionLoss()
    })
  }

  async handleConnectionRestore() {
    try {
      // Refresh all data when connection is restored
      await this.refreshAllData()
    } catch (error) {
      console.error("Failed to refresh data after connection restore:", error)
    }
  }

  handleConnectionLoss() {
    // Stop auto-refresh to prevent failed requests
    this.dashboard.stopAutoRefresh()
  }

  async refreshAllData() {
    console.log("🔄 Refreshing all data...")

    try {
      await Promise.all([
        this.dashboard.refreshDashboard(),
        this.devices.loadDevices(),
        this.alerts.loadAlerts({ status: "active" }),
        this.customers.loadCustomers(),
      ])

      console.log("✅ All data refreshed successfully")
    } catch (error) {
      console.error("❌ Failed to refresh all data:", error)
      throw error
    }
  }

  notifyAppReady() {
    const event = new CustomEvent("appReady", {
      detail: {
        dashboard: this.dashboard.getDashboardData(),
        devices: this.devices.devices,
        alerts: this.alerts.alerts,
        customers: this.customers.customers,
      },
    })
    document.dispatchEvent(event)
  }

  getSystemOverview() {
    return {
      devices: this.devices.getDeviceStats(),
      alerts: this.alerts.getAlertStats(),
      customers: this.customers.getCustomerStats(),
      lastUpdated: new Date().toISOString(),
    }
  }

  destroy() {
    this.dashboard.destroy()
    this.isInitialized = false
    console.log("🎮 App controllers destroyed")
  }
}

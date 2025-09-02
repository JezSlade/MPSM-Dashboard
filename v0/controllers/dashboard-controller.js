// Dashboard Controller - Manages dashboard data and state
import { liveAPI } from "../api/live-api-engine.js"
import { AppConfig } from "../config/app-config.js"

export class DashboardController {
  constructor() {
    this.config = AppConfig
    this.dashboardData = null
    this.refreshTimer = null
    this.isLoading = false
    this.customers = []
    this.devices = []
    this.alerts = []
    this.sdsActions = []
  }

  async loadDashboard() {
    if (this.isLoading) {
      console.log("Dashboard already loading...")
      return this.dashboardData
    }

    this.isLoading = true

    try {
      console.log("📊 Loading dashboard data...")

      const [customersData, alertsData, sdsActionsData] = await Promise.all([
        liveAPI.getCustomers(),
        liveAPI.getAlerts(),
        liveAPI.getSdsActions(),
      ])

      this.customers = customersData || []
      this.alerts = alertsData || []
      this.sdsActions = sdsActionsData || []

      this.dashboardData = {
        summary: {
          totalCustomers: this.customers.length,
          totalDevices: 0, // Will be updated when devices are loaded
          activeAlerts: this.alerts.filter((alert) => !alert.IsHidden).length,
          sdsServiceRequests: this.sdsActions.filter((action) => action.CurrentState === 1).length,
        },
        recentAlerts: this.alerts.slice(0, 10),
        criticalSdsActions: this.sdsActions.filter((action) => action.Severity >= 2).slice(0, 5),
        customerOverview: this.customers.slice(0, 20),
        lastUpdated: new Date().toISOString(),
      }

      console.log("✅ Dashboard data loaded successfully")

      // Start auto-refresh if enabled
      if (this.config.features.realTimeUpdates) {
        this.startAutoRefresh()
      }

      return this.dashboardData
    } catch (error) {
      console.error("❌ Failed to load dashboard:", error)
      throw error
    } finally {
      this.isLoading = false
    }
  }

  async refreshDashboard() {
    console.log("🔄 Refreshing dashboard data...")
    return await this.loadDashboard()
  }

  startAutoRefresh() {
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer)
    }

    this.refreshTimer = setInterval(async () => {
      try {
        await this.refreshDashboard()
        this.notifyDataUpdate()
      } catch (error) {
        console.error("Auto-refresh failed:", error)
      }
    }, this.config.dashboard.refreshInterval)

    console.log(`🔄 Auto-refresh started (${this.config.dashboard.refreshInterval}ms interval)`)
  }

  stopAutoRefresh() {
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer)
      this.refreshTimer = null
      console.log("⏹️ Auto-refresh stopped")
    }
  }

  notifyDataUpdate() {
    // Dispatch custom event for UI updates
    const event = new CustomEvent("dashboardDataUpdated", {
      detail: { data: this.dashboardData },
    })
    document.dispatchEvent(event)
  }

  getDashboardData() {
    return this.dashboardData
  }

  getWidgetData(widgetId) {
    if (!this.dashboardData || !this.dashboardData.widgets) {
      return null
    }

    return this.dashboardData.widgets.find((widget) => widget.id === widgetId)
  }

  async getCustomerDashboard(customerCode) {
    try {
      const [customerDetails, devices, alerts, sdsActions] = await Promise.all([
        liveAPI.getCustomerDetails(customerCode),
        liveAPI.getDevices([customerCode]),
        liveAPI.getAlerts(customerCode),
        liveAPI.getSdsActions(customerCode),
      ])

      return {
        customer: customerDetails,
        devices: devices || [],
        alerts: alerts || [],
        sdsActions: sdsActions || [],
        summary: {
          deviceCount: devices?.length || 0,
          alertCount: alerts?.length || 0,
          sdsActionCount: sdsActions?.length || 0,
        },
      }
    } catch (error) {
      console.error("❌ Failed to load customer dashboard:", error)
      throw error
    }
  }

  destroy() {
    this.stopAutoRefresh()
    this.dashboardData = null
  }
}

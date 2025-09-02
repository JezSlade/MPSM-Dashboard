// Dashboard Handler - Handles /Dashboard/* endpoints
import { MockData } from "../data/mock-data.js"

export class DashboardHandler {
  constructor() {
    this.supportedMethods = ["GET", "POST"]
    this.mockData = MockData
  }

  // GET /api/v3/Dashboard/Get
  async get(request, endpoint) {
    const { params } = endpoint

    // Simulate loading delay
    await this.delay(200)

    // Get dashboard data
    const dashboardData = {
      id: "dashboard-001",
      title: "MPSM Dashboard",
      lastUpdated: new Date().toISOString(),
      summary: {
        totalDevices: this.mockData.devices.length,
        activeAlerts: this.mockData.alerts.filter((a) => a.status === "active").length,
        totalCustomers: this.mockData.customers.length,
        systemHealth: "healthy",
      },
      widgets: [
        {
          id: "devices-overview",
          type: "devices",
          title: "Device Overview",
          data: this.getDeviceOverview(),
        },
        {
          id: "alerts-summary",
          type: "alerts",
          title: "Active Alerts",
          data: this.getAlertsOverview(),
        },
        {
          id: "customer-stats",
          type: "customers",
          title: "Customer Statistics",
          data: this.getCustomerStats(),
        },
        {
          id: "system-metrics",
          type: "metrics",
          title: "System Metrics",
          data: this.getSystemMetrics(),
        },
      ],
    }

    return dashboardData
  }

  getDeviceOverview() {
    const devices = this.mockData.devices
    return {
      total: devices.length,
      online: devices.filter((d) => d.status === "online").length,
      offline: devices.filter((d) => d.status === "offline").length,
      maintenance: devices.filter((d) => d.status === "maintenance").length,
      byType: this.groupBy(devices, "type"),
    }
  }

  getAlertsOverview() {
    const alerts = this.mockData.alerts.filter((a) => a.status === "active")
    return {
      total: alerts.length,
      critical: alerts.filter((a) => a.severity === "critical").length,
      warning: alerts.filter((a) => a.severity === "warning").length,
      info: alerts.filter((a) => a.severity === "info").length,
      recent: alerts.slice(0, 5),
    }
  }

  getCustomerStats() {
    const customers = this.mockData.customers
    return {
      total: customers.length,
      active: customers.filter((c) => c.status === "active").length,
      inactive: customers.filter((c) => c.status === "inactive").length,
      byTier: this.groupBy(customers, "tier"),
    }
  }

  getSystemMetrics() {
    return {
      uptime: "99.9%",
      responseTime: "120ms",
      throughput: "1,250 req/min",
      errorRate: "0.1%",
      lastCheck: new Date().toISOString(),
    }
  }

  groupBy(array, key) {
    return array.reduce((groups, item) => {
      const group = item[key] || "unknown"
      groups[group] = (groups[group] || 0) + 1
      return groups
    }, {})
  }

  async delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms))
  }
}

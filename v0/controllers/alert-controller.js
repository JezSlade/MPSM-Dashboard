// Alert Controller - Manages alert data and operations
import { liveAPI } from "../api/live-api-engine.js"

export class AlertController {
  constructor() {
    this.alerts = []
    this.isLoading = false
  }

  async loadAlerts(filters = {}) {
    if (this.isLoading) {
      console.log("Alerts already loading...")
      return this.alerts
    }

    this.isLoading = true

    try {
      console.log("🚨 Loading alerts data...")

      const data = await liveAPI.getAlerts(
        filters.customerCode || null,
        filters.pageNumber || 1,
        filters.pageRows || 50,
      )

      this.alerts = data || []

      console.log(`✅ Loaded ${this.alerts.length} alerts`)
      return this.alerts
    } catch (error) {
      console.error("❌ Failed to load alerts:", error)
      throw error
    } finally {
      this.isLoading = false
    }
  }

  getActiveAlerts() {
    return this.alerts.filter((alert) => !alert.IsHidden && !alert.CanceledOn)
  }

  getAlertsBySupplyType(supplyType) {
    return this.alerts.filter((alert) => alert.SupplyType === supplyType)
  }

  getAlertsByColorType(colorType) {
    return this.alerts.filter((alert) => alert.ColorType === colorType)
  }

  getAlertsByDevice(deviceId) {
    return this.alerts.filter((alert) => alert.DeviceId === deviceId)
  }

  getAlertsByCustomer(customerCode) {
    return this.alerts.filter((alert) => alert.CustomerCode === customerCode)
  }

  getShippedAlerts() {
    return this.alerts.filter((alert) => alert.IsShipped === true)
  }

  getInstalledAlerts() {
    return this.alerts.filter((alert) => alert.TonerInstalled !== null)
  }

  getRecentAlerts(limit = 5) {
    return this.alerts.sort((a, b) => new Date(b.InitialDate) - new Date(a.InitialDate)).slice(0, limit)
  }

  getAlertStats() {
    const activeAlerts = this.getActiveAlerts()

    const stats = {
      total: activeAlerts.length,
      shipped: this.getShippedAlerts().length,
      installed: this.getInstalledAlerts().length,
      pending: activeAlerts.filter((alert) => !alert.IsShipped).length,
      recent: this.getRecentAlerts(),
      bySupplyType: {},
      byColorType: {},
      byCustomer: {},
      byBrand: {},
    }

    activeAlerts.forEach((alert) => {
      // Count by supply type
      const supplyTypeDesc = alert.MaintenanceKitType?.Description || "Toner"
      stats.bySupplyType[supplyTypeDesc] = (stats.bySupplyType[supplyTypeDesc] || 0) + 1

      // Count by color type
      const colorTypeDesc = alert.MaintenanceKitColor?.Description || "Unknown"
      stats.byColorType[colorTypeDesc] = (stats.byColorType[colorTypeDesc] || 0) + 1

      // Count by customer
      stats.byCustomer[alert.CustomerDescription] = (stats.byCustomer[alert.CustomerDescription] || 0) + 1

      // Count by brand
      stats.byBrand[alert.ProductBrand] = (stats.byBrand[alert.ProductBrand] || 0) + 1
    })

    return stats
  }

  getAlertsByDateRange(startDate, endDate) {
    const start = new Date(startDate)
    const end = new Date(endDate)

    return this.alerts.filter((alert) => {
      const alertDate = new Date(alert.InitialDate)
      return alertDate >= start && alertDate <= end
    })
  }

  getSupplyAlertsByResidual(threshold = 10) {
    return this.alerts.filter(
      (alert) => alert.ActualResidualPercentage !== null && alert.ActualResidualPercentage <= threshold,
    )
  }

  searchAlerts(query) {
    const searchTerm = query.toLowerCase()
    return this.alerts.filter(
      (alert) =>
        alert.SerialNumber?.toLowerCase().includes(searchTerm) ||
        alert.ProductModel?.toLowerCase().includes(searchTerm) ||
        alert.CustomerDescription?.toLowerCase().includes(searchTerm) ||
        alert.Warning?.toLowerCase().includes(searchTerm),
    )
  }
}

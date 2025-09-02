// Device Controller - Manages device data and operations
import { liveAPI } from "../api/live-api-engine.js"

export class DeviceController {
  constructor() {
    this.devices = []
    this.isLoading = false
  }

  async loadDevices(filters = {}) {
    if (this.isLoading) {
      console.log("Devices already loading...")
      return this.devices
    }

    this.isLoading = true

    try {
      console.log("🖥️ Loading devices data...")

      const data = await liveAPI.getDevices(
        filters.customerCodes || [],
        filters.pageNumber || 1,
        filters.pageRows || 50,
      )

      this.devices = data || []

      console.log(`✅ Loaded ${this.devices.length} devices`)
      return this.devices
    } catch (error) {
      console.error("❌ Failed to load devices:", error)
      throw error
    } finally {
      this.isLoading = false
    }
  }

  getDevicesByStatus(isOffline) {
    return this.devices.filter((device) => device.IsOffline === isOffline)
  }

  getDevicesByBrand(brand) {
    return this.devices.filter((device) => device.Product?.Brand === brand)
  }

  getDevicesByCustomer(customerCode) {
    return this.devices.filter((device) => device.CustomerCode === customerCode)
  }

  getDevicesByModel(model) {
    return this.devices.filter((device) => device.Product?.Model === model)
  }

  getDevicesWithAlerts() {
    return this.devices.filter((device) => device.IsAlertGenerator === true)
  }

  getDevicesWithSupplyManagement() {
    return this.devices.filter((device) => device.IsManageSupplies === true)
  }

  getDeviceStats() {
    const stats = {
      total: this.devices.length,
      online: 0,
      offline: 0,
      alertGenerators: 0,
      supplyManaged: 0,
      byBrand: {},
      byCustomer: {},
      totalMonoVolume: 0,
      totalColorVolume: 0,
    }

    this.devices.forEach((device) => {
      // Count by status
      if (device.IsOffline) stats.offline++
      else stats.online++

      // Count features
      if (device.IsAlertGenerator) stats.alertGenerators++
      if (device.IsManageSupplies) stats.supplyManaged++

      // Count by brand
      const brand = device.Product?.Brand || "Unknown"
      stats.byBrand[brand] = (stats.byBrand[brand] || 0) + 1

      // Count by customer
      stats.byCustomer[device.CustomerDescription] = (stats.byCustomer[device.CustomerDescription] || 0) + 1

      // Sum volumes
      stats.totalMonoVolume += device.MonthlyMonoVolume || 0
      stats.totalColorVolume += device.MonthlyColorVolume || 0
    })

    return stats
  }

  getDeviceSupplyLevels(deviceId) {
    const device = this.devices.find((d) => d.Id === deviceId)
    if (!device) return null

    return {
      blackToner: device.BlackToner,
      blackToner1: device.BlackToner1,
      blackToner2: device.BlackToner2,
      blackToner3: device.BlackToner3,
      cyanToner: device.CyanToner,
      magentaToner: device.MagentaToner,
      yellowToner: device.YellowToner,
      blackPhoto: device.BlackPhoto,
      cyanPhoto: device.CyanPhoto,
      magentaPhoto: device.MagentaPhoto,
      yellowPhoto: device.YellowPhoto,
    }
  }

  searchDevices(query) {
    const searchTerm = query.toLowerCase()
    return this.devices.filter(
      (device) =>
        device.SerialNumber?.toLowerCase().includes(searchTerm) ||
        device.SystemName?.toLowerCase().includes(searchTerm) ||
        device.Product?.Model?.toLowerCase().includes(searchTerm) ||
        device.CustomerDescription?.toLowerCase().includes(searchTerm),
    )
  }
}

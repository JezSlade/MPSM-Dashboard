// Customer Controller - Manages customer data and operations
import { liveAPI } from "../api/live-api-engine.js"

export class CustomerController {
  constructor() {
    this.customers = []
    this.isLoading = false
  }

  async loadCustomers(filters = {}) {
    if (this.isLoading) {
      console.log("Customers already loading...")
      return this.customers
    }

    this.isLoading = true

    try {
      console.log("👥 Loading customers data...")

      const data = await liveAPI.getCustomers(
        filters.filterText || null,
        filters.pageNumber || 1,
        filters.pageRows || 2147483647,
      )

      this.customers = data || []

      console.log(`✅ Loaded ${this.customers.length} customers`)
      return this.customers
    } catch (error) {
      console.error("❌ Failed to load customers:", error)
      throw error
    } finally {
      this.isLoading = false
    }
  }

  async getCustomer(customerCode) {
    try {
      const data = await liveAPI.getCustomerDetails(customerCode)
      return data
    } catch (error) {
      console.error(`❌ Failed to load customer ${customerCode}:`, error)
      throw error
    }
  }

  getCustomersByCountry(countryCode) {
    return this.customers.filter((customer) => customer.CountryCode === countryCode)
  }

  getCustomersByDealer(dealerCode) {
    return this.customers.filter((customer) => customer.DealerCode === dealerCode)
  }

  searchCustomers(query) {
    const searchTerm = query.toLowerCase()
    return this.customers.filter(
      (customer) =>
        customer.Description.toLowerCase().includes(searchTerm) || customer.Code.toLowerCase().includes(searchTerm),
    )
  }

  getCustomerStats() {
    const stats = {
      total: this.customers.length,
      byCountry: {},
      byDealer: {},
      totalDevices: 0,
    }

    this.customers.forEach((customer) => {
      stats.byCountry[customer.CountryName] = (stats.byCountry[customer.CountryName] || 0) + 1

      // Count by dealer
      stats.byDealer[customer.DealerDescription] = (stats.byDealer[customer.DealerDescription] || 0) + 1
    })

    return stats
  }

  async getCustomerDevices(customerCode) {
    try {
      const devices = await liveAPI.getDevices([customerCode])
      return devices || []
    } catch (error) {
      console.error(`❌ Failed to load devices for customer ${customerCode}:`, error)
      throw error
    }
  }

  async getCustomerAlerts(customerCode) {
    try {
      const alerts = await liveAPI.getAlerts(customerCode)
      return alerts || []
    } catch (error) {
      console.error(`❌ Failed to load alerts for customer ${customerCode}:`, error)
      throw error
    }
  }

  async getCustomerSdsActions(customerCode) {
    try {
      const sdsActions = await liveAPI.getSdsActions(customerCode)
      return sdsActions || []
    } catch (error) {
      console.error(`❌ Failed to load SDS actions for customer ${customerCode}:`, error)
      throw error
    }
  }
}

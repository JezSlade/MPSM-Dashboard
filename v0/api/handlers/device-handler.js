// Device Handler - Handles /Device/* endpoints
import { MockData } from "../data/mock-data.js"

export class DeviceHandler {
  constructor() {
    this.supportedMethods = ["GET", "POST", "PUT", "DELETE"]
    this.mockData = MockData
  }

  // GET /api/v3/Device/GetDevices
  async getDevices(request, endpoint) {
    const { params } = endpoint

    await this.delay(180)

    let devices = [...this.mockData.devices]

    // Apply filters
    if (params.status) {
      devices = devices.filter((d) => d.status === params.status)
    }

    if (params.type) {
      devices = devices.filter((d) => d.type === params.type)
    }

    if (params.customerId) {
      devices = devices.filter((d) => d.customerId === params.customerId)
    }

    return { devices }
  }

  async delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms))
  }
}

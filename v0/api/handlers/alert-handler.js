// Alert Handler - Handles /Alert/* endpoints
import { MockData } from "../data/mock-data.js"

export class AlertHandler {
  constructor() {
    this.supportedMethods = ["GET", "POST", "PUT", "DELETE"]
    this.mockData = MockData
  }

  // GET /api/v3/Alert/GetAlerts
  async getAlerts(request, endpoint) {
    const { params } = endpoint

    await this.delay(120)

    let alerts = [...this.mockData.alerts]

    // Apply filters
    if (params.status) {
      alerts = alerts.filter((a) => a.status === params.status)
    }

    if (params.severity) {
      alerts = alerts.filter((a) => a.severity === params.severity)
    }

    return { alerts }
  }

  async delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms))
  }
}

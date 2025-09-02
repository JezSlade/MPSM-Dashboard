// Customer Handler - Handles /Customer/* endpoints
import { MockData } from "../data/mock-data.js"

export class CustomerHandler {
  constructor() {
    this.supportedMethods = ["GET", "POST", "PUT", "DELETE"]
    this.mockData = MockData
  }

  // GET /api/v3/Customer/GetCustomers
  async getCustomers(request, endpoint) {
    const { params } = endpoint

    await this.delay(150)

    let customers = [...this.mockData.customers]

    // Apply filters
    if (params.status) {
      customers = customers.filter((c) => c.status === params.status)
    }

    if (params.tier) {
      customers = customers.filter((c) => c.tier === params.tier)
    }

    if (params.search) {
      const search = params.search.toLowerCase()
      customers = customers.filter(
        (c) => c.name.toLowerCase().includes(search) || c.email.toLowerCase().includes(search),
      )
    }

    // Apply pagination
    const page = Number.parseInt(params.page) || 1
    const limit = Number.parseInt(params.limit) || 10
    const offset = (page - 1) * limit

    const paginatedCustomers = customers.slice(offset, offset + limit)

    return {
      customers: paginatedCustomers,
      pagination: {
        page,
        limit,
        total: customers.length,
        pages: Math.ceil(customers.length / limit),
      },
    }
  }

  // GET /api/v3/Customer/GetCustomer/{id}
  async getCustomer(request, endpoint) {
    const customerId = endpoint.path[2]

    if (!customerId) {
      throw new Error("Customer ID is required")
    }

    await this.delay(100)

    const customer = this.mockData.customers.find((c) => c.id === customerId)

    if (!customer) {
      throw new Error("Customer not found")
    }

    return { customer }
  }

  async delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms))
  }
}

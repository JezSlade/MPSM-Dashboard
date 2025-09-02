// Mock Fetch - Overrides window.fetch for API simulation
import { ApiRouter } from "./api-router.js"

export class MockFetch {
  static isInitialized = false
  static originalFetch = null
  static router = null

  static initialize() {
    if (this.isInitialized) {
      console.warn("MockFetch already initialized")
      return
    }

    // Store original fetch
    this.originalFetch = window.fetch
    this.router = new ApiRouter()

    // Override window.fetch
    window.fetch = this.mockFetch.bind(this)
    this.isInitialized = true

    console.log("🔧 MockFetch initialized - API calls will be intercepted")
  }

  static async mockFetch(url, options = {}) {
    const method = options.method || "GET"
    const headers = options.headers || {}
    const body = options.body

    console.log(`🌐 Mock API Call: ${method} ${url}`)

    try {
      // Parse request
      const request = {
        url: url.toString(),
        method: method.toUpperCase(),
        headers: this.normalizeHeaders(headers),
        body: body ? JSON.parse(body) : null,
        timestamp: new Date().toISOString(),
      }

      // Route request through API router
      const response = await this.router.handleRequest(request)

      // Create mock Response object
      return this.createMockResponse(response)
    } catch (error) {
      console.error("❌ Mock API Error:", error)
      return this.createErrorResponse(500, "Internal Server Error")
    }
  }

  static normalizeHeaders(headers) {
    const normalized = {}
    if (headers instanceof Headers) {
      headers.forEach((value, key) => {
        normalized[key.toLowerCase()] = value
      })
    } else if (typeof headers === "object") {
      Object.entries(headers).forEach(([key, value]) => {
        normalized[key.toLowerCase()] = value
      })
    }
    return normalized
  }

  static createMockResponse(responseData) {
    const { status, statusText, headers, data } = responseData

    return new Response(JSON.stringify(data), {
      status,
      statusText,
      headers: {
        "Content-Type": "application/json",
        ...headers,
      },
    })
  }

  static createErrorResponse(status, message) {
    return new Response(
      JSON.stringify({
        error: {
          code: status,
          message,
          timestamp: new Date().toISOString(),
        },
      }),
      {
        status,
        statusText: message,
        headers: {
          "Content-Type": "application/json",
        },
      },
    )
  }

  static restore() {
    if (this.originalFetch) {
      window.fetch = this.originalFetch
      this.isInitialized = false
      console.log("🔧 MockFetch restored - using real fetch")
    }
  }
}

// API Router - Routes requests to appropriate handlers
import { CustomerHandler } from "./handlers/customer-handler.js"
import { DashboardHandler } from "./handlers/dashboard-handler.js"
import { DeviceHandler } from "./handlers/device-handler.js"
import { AlertHandler } from "./handlers/alert-handler.js"

export class ApiRouter {
  constructor() {
    this.handlers = new Map()
    this.registerHandlers()
  }

  registerHandlers() {
    // Register all API handlers
    this.handlers.set("customer", new CustomerHandler())
    this.handlers.set("dashboard", new DashboardHandler())
    this.handlers.set("device", new DeviceHandler())
    this.handlers.set("alert", new AlertHandler())

    console.log("📋 API Handlers registered:", Array.from(this.handlers.keys()))
  }

  async handleRequest(request) {
    const { url, method } = request

    try {
      // Parse URL to extract endpoint info
      const endpoint = this.parseEndpoint(url)

      if (!endpoint) {
        return this.createErrorResponse(404, "Endpoint not found")
      }

      // Get appropriate handler
      const handler = this.handlers.get(endpoint.module)

      if (!handler) {
        return this.createErrorResponse(404, `Handler not found for module: ${endpoint.module}`)
      }

      // Validate method
      if (!handler.supportedMethods.includes(method)) {
        return this.createErrorResponse(405, `Method ${method} not allowed`)
      }

      // Route to handler method
      const handlerMethod = this.getHandlerMethod(handler, endpoint.action, method)

      if (!handlerMethod) {
        return this.createErrorResponse(404, `Action not found: ${endpoint.action}`)
      }

      // Execute handler with request context
      const result = await handlerMethod.call(handler, request, endpoint)

      return this.createSuccessResponse(result)
    } catch (error) {
      console.error("🚨 API Router Error:", error)
      return this.createErrorResponse(500, error.message)
    }
  }

  parseEndpoint(url) {
    // Parse MPS Monitor API v3 URL pattern: /api/v3/{Module}/{Action}
    const urlObj = new URL(url, window.location.origin)
    const pathParts = urlObj.pathname.split("/").filter(Boolean)

    if (pathParts.length < 3 || pathParts[0] !== "api" || pathParts[1] !== "v3") {
      return null
    }

    return {
      module: pathParts[2].toLowerCase(),
      action: pathParts[3] || "index",
      params: Object.fromEntries(urlObj.searchParams),
      path: pathParts.slice(2),
    }
  }

  getHandlerMethod(handler, action, method) {
    // Convert action to method name (e.g., "GetCustomers" -> "getCustomers")
    const methodName = action.charAt(0).toLowerCase() + action.slice(1)

    // Try exact method name first
    if (typeof handler[methodName] === "function") {
      return handler[methodName]
    }

    // Try with HTTP method prefix (e.g., "getCustomers" -> "getGetCustomers")
    const prefixedMethodName = method.toLowerCase() + methodName.charAt(0).toUpperCase() + methodName.slice(1)

    if (typeof handler[prefixedMethodName] === "function") {
      return handler[prefixedMethodName]
    }

    return null
  }

  createSuccessResponse(data) {
    return {
      status: 200,
      statusText: "OK",
      headers: {
        "X-Mock-API": "true",
        "X-Response-Time": Date.now().toString(),
      },
      data,
    }
  }

  createErrorResponse(status, message) {
    return {
      status,
      statusText: this.getStatusText(status),
      headers: {
        "X-Mock-API": "true",
        "X-Error": "true",
      },
      data: {
        error: {
          code: status,
          message,
          timestamp: new Date().toISOString(),
        },
      },
    }
  }

  getStatusText(status) {
    const statusTexts = {
      400: "Bad Request",
      401: "Unauthorized",
      403: "Forbidden",
      404: "Not Found",
      405: "Method Not Allowed",
      500: "Internal Server Error",
    }
    return statusTexts[status] || "Unknown Error"
  }
}

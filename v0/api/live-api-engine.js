import { API_CONFIG, API_ENDPOINTS } from "../config/api-config.js"

class LiveAPIEngine {
  constructor() {
    this.accessToken = null
    this.tokenExpiry = null
    this.isAuthenticating = false
  }

  async authenticate() {
    if (this.isAuthenticating) {
      // Wait for ongoing authentication
      while (this.isAuthenticating) {
        await new Promise((resolve) => setTimeout(resolve, 100))
      }
      return this.accessToken
    }

    if (this.accessToken && this.tokenExpiry && Date.now() < this.tokenExpiry) {
      return this.accessToken
    }

    this.isAuthenticating = true

    try {
      const response = await fetch(API_CONFIG.TOKEN_URL, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          grant_type: "password",
          client_id: API_CONFIG.CLIENT_ID,
          client_secret: API_CONFIG.CLIENT_SECRET,
          username: API_CONFIG.USERNAME,
          password: API_CONFIG.PASSWORD,
          scope: API_CONFIG.SCOPE,
        }),
      })

      if (!response.ok) {
        throw new Error(`Authentication failed: ${response.status} ${response.statusText}`)
      }

      const tokenData = await response.json()
      this.accessToken = tokenData.access_token
      this.tokenExpiry = Date.now() + tokenData.expires_in * 1000 - 60000 // Refresh 1 minute early

      if (API_CONFIG.DEBUG_MODE) {
        console.log("[v0] Authentication successful, token expires at:", new Date(this.tokenExpiry))
      }

      return this.accessToken
    } catch (error) {
      console.error("[v0] Authentication error:", error)
      throw error
    } finally {
      this.isAuthenticating = false
    }
  }

  async makeRequest(endpoint, requestData = null, method = "GET") {
    const token = await this.authenticate()

    const url = `${API_CONFIG.BASE_URL}${endpoint}`
    const options = {
      method,
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
    }

    if (requestData && (method === "POST" || method === "PUT")) {
      options.body = JSON.stringify(requestData)
    }

    if (API_CONFIG.DEBUG_MODE) {
      console.log("[v0] API Request:", { url, method, requestData })
    }

    try {
      const response = await fetch(url, options)

      if (!response.ok) {
        if (response.status === 401) {
          // Token expired, clear it and retry once
          this.accessToken = null
          this.tokenExpiry = null
          const newToken = await this.authenticate()
          options.headers["Authorization"] = `Bearer ${newToken}`
          const retryResponse = await fetch(url, options)
          if (!retryResponse.ok) {
            throw new Error(`API request failed after retry: ${retryResponse.status} ${retryResponse.statusText}`)
          }
          return await retryResponse.json()
        }
        throw new Error(`API request failed: ${response.status} ${response.statusText}`)
      }

      const data = await response.json()

      if (API_CONFIG.DEBUG_MODE) {
        console.log("[v0] API Response:", data)
      }

      return data
    } catch (error) {
      console.error("[v0] API request error:", error)
      throw error
    }
  }

  // Customer API methods using exact SDK payload patterns
  async getCustomers(filterText = null, pageNumber = 1, pageRows = 2147483647) {
    const requestData = {
      DealerCode: API_CONFIG.DEALER_CODE,
      Code: null,
      HasHpSds: null,
      FilterText: filterText,
      PageNumber: pageNumber,
      PageRows: pageRows,
      SortColumn: "Id",
      SortOrder: 0,
    }

    return await this.makeRequest(API_ENDPOINTS.GET_CUSTOMERS, requestData, "POST")
  }

  async getCustomerDetails(customerCode) {
    const requestData = {
      Code: customerCode,
    }

    return await this.makeRequest(API_ENDPOINTS.GET_CUSTOMER_DETAILS, requestData, "POST")
  }

  async getDevices(customerCodes = [], pageNumber = 1, pageRows = 50) {
    const requestData = {
      FilterDealerId: API_CONFIG.DEALER_ID,
      FilterCustomerCodes: customerCodes,
      ProductBrand: null,
      ProductModel: null,
      OfficeId: null,
      Status: 1,
      FilterText: null,
      PageNumber: pageNumber,
      PageRows: pageRows,
      SortColumn: "Id",
      SortOrder: 0,
    }

    return await this.makeRequest(API_ENDPOINTS.GET_DEVICES, requestData, "POST")
  }

  async getAlerts(customerCode = null, pageNumber = 1, pageRows = 50) {
    const requestData = {
      DealerCode: API_CONFIG.DEALER_CODE,
      DeviceId: null,
      SerialNumber: null,
      AssetNumber: null,
      InitialFrom: null,
      InitialTo: null,
      ExhaustedFrom: null,
      ExhaustedTo: null,
      Brand: null,
      Model: null,
      OfficeDescription: null,
      SupplySetDescription: null,
      CustomerCode: customerCode,
      FilterCustomerText: null,
      ManageOption: null,
      InstallationOption: null,
      CancelOption: null,
      HiddenOption: null,
      SupplyType: null,
      ColorType: null,
      ExcludeForStockShippedSupplies: false,
      FilterText: null,
      PageNumber: pageNumber,
      PageRows: pageRows,
      SortColumn: "InitialDate",
      SortOrder: 0,
    }

    return await this.makeRequest(API_ENDPOINTS.GET_ALERTS, requestData, "POST")
  }

  async getSdsActions(customerCode = null, pageNumber = 1, pageRows = 50) {
    const requestData = {
      DeviceId: null,
      DealerId: null,
      CustomerId: null,
      DealerCode: API_CONFIG.DEALER_CODE,
      CustomerCode: customerCode,
      State: null,
      Severity: null,
      ActionType: null,
      FilterText: null,
      PageNumber: pageNumber,
      PageRows: pageRows,
      SortColumn: "Id",
      SortOrder: 0,
    }

    return await this.makeRequest(API_ENDPOINTS.GET_SDS_ACTIONS, requestData, "GET")
  }

  async getSupplies(pageNumber = 1, pageRows = 50) {
    const requestData = {
      ColorType: null,
      Language: null,
      Code: API_CONFIG.DEALER_CODE,
      FilterText: null,
      PageNumber: pageNumber,
      PageRows: pageRows,
      SortColumn: "Id",
      SortOrder: 0,
    }

    return await this.makeRequest(API_ENDPOINTS.GET_SUPPLIES, requestData, "GET")
  }
}

// Create singleton instance
export const liveAPI = new LiveAPIEngine()

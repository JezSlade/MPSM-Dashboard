// MPSM Dashboard - Main Application Bootstrap
import { AppConfig } from "./config/app-config.js"
import { ThemeConfig } from "./config/theme-config.js"
import { liveAPI } from "./api/live-api-engine.js"
import { DashboardUI } from "./ui/dashboard-ui.js"
import { AppController } from "./controllers/app-controller.js"

class MPSMApp {
  constructor() {
    this.config = AppConfig
    this.theme = ThemeConfig
    this.dashboardUI = new DashboardUI()
    this.appController = new AppController()

    this.init()
  }

  async init() {
    try {
      console.log("🚀 Initializing MPSM Dashboard...")

      await this.testAPIConnection()

      // Apply theme
      this.applyTheme()

      // Initialize controllers
      await this.appController.initialize()

      // Initialize dashboard
      await this.initializeDashboard()

      console.log("✅ MPSM Dashboard initialized successfully")
    } catch (error) {
      console.error("❌ Failed to initialize MPSM Dashboard:", error)
      this.showError(`Failed to initialize dashboard: ${error.message}`)
    }
  }

  async testAPIConnection() {
    try {
      console.log("🔗 Testing API connection...")
      await liveAPI.authenticate()
      console.log("✅ API connection established successfully")
    } catch (error) {
      console.error("❌ API connection failed:", error)
      throw new Error(`API connection failed: ${error.message}`)
    }
  }

  applyTheme() {
    const root = document.documentElement
    Object.entries(this.theme.colors).forEach(([key, value]) => {
      root.style.setProperty(`--${key}`, value)
    })
  }

  async initializeDashboard() {
    const contentElement = document.getElementById("dashboard-content")
    if (!contentElement) {
      throw new Error("Dashboard content element not found")
    }

    const dashboardData = this.appController.dashboard.getDashboardData()

    // Render dashboard UI
    this.dashboardUI.render(contentElement, dashboardData)

    this.setupDataUpdateListeners(contentElement)
  }

  setupDataUpdateListeners(contentElement) {
    // Listen for dashboard updates
    document.addEventListener("dashboardDataUpdated", (event) => {
      this.dashboardUI.render(contentElement, event.detail.data)
    })

    // Listen for app ready event
    document.addEventListener("appReady", (event) => {
      console.log("📱 App is ready with all data loaded")
    })

    document.addEventListener("apiError", (event) => {
      console.error("🚨 API Error:", event.detail.error)
      this.showError(`API Error: ${event.detail.error.message}`)
    })
  }

  showError(message) {
    const contentElement = document.getElementById("dashboard-content")
    if (contentElement) {
      contentElement.innerHTML = `
                <div class="glass-card" style="text-align: center; color: #ef4444;">
                    <div class="card-title">Error</div>
                    <div class="card-content">${message}</div>
                    <div style="margin-top: 1rem; font-size: 0.875rem; opacity: 0.8;">
                        Check console for detailed error information
                    </div>
                </div>
            `
    }
  }
}

// Initialize app when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new MPSMApp()
})

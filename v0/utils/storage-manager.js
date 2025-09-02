// Local Storage Management
export class StorageManager {
  static setItem(key, value) {
    try {
      const serializedValue = JSON.stringify(value)
      localStorage.setItem(`mpsm_${key}`, serializedValue)
      return true
    } catch (error) {
      console.error("Failed to save to localStorage:", error)
      return false
    }
  }

  static getItem(key, defaultValue = null) {
    try {
      const item = localStorage.getItem(`mpsm_${key}`)
      return item ? JSON.parse(item) : defaultValue
    } catch (error) {
      console.error("Failed to read from localStorage:", error)
      return defaultValue
    }
  }

  static removeItem(key) {
    try {
      localStorage.removeItem(`mpsm_${key}`)
      return true
    } catch (error) {
      console.error("Failed to remove from localStorage:", error)
      return false
    }
  }

  static clear() {
    try {
      const keys = Object.keys(localStorage).filter((key) => key.startsWith("mpsm_"))
      keys.forEach((key) => localStorage.removeItem(key))
      return true
    } catch (error) {
      console.error("Failed to clear localStorage:", error)
      return false
    }
  }

  static saveUserPreferences(preferences) {
    return this.setItem("user_preferences", preferences)
  }

  static getUserPreferences() {
    return this.getItem("user_preferences", {
      theme: "dark",
      autoRefresh: true,
      refreshInterval: 30000,
      notifications: true,
    })
  }

  static saveDashboardLayout(layout) {
    return this.setItem("dashboard_layout", layout)
  }

  static getDashboardLayout() {
    return this.getItem("dashboard_layout", null)
  }
}

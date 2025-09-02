// Data Processing Utilities
export class DataProcessor {
  static formatNumber(num, decimals = 0) {
    if (num === null || num === undefined) return "0"
    return Number(num).toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    })
  }

  static formatPercentage(value, total) {
    if (!total || total === 0) return "0%"
    const percentage = (value / total) * 100
    return `${percentage.toFixed(1)}%`
  }

  static formatDate(dateString) {
    if (!dateString) return "N/A"
    const date = new Date(dateString)
    return date.toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
  }

  static formatRelativeTime(dateString) {
    if (!dateString) return "N/A"
    const date = new Date(dateString)
    const now = new Date()
    const diffMs = now - date
    const diffMins = Math.floor(diffMs / 60000)
    const diffHours = Math.floor(diffMins / 60)
    const diffDays = Math.floor(diffHours / 24)

    if (diffMins < 1) return "Just now"
    if (diffMins < 60) return `${diffMins}m ago`
    if (diffHours < 24) return `${diffHours}h ago`
    if (diffDays < 7) return `${diffDays}d ago`
    return this.formatDate(dateString)
  }

  static getStatusColor(status) {
    const statusColors = {
      online: "var(--accent-success)",
      active: "var(--accent-success)",
      healthy: "var(--accent-success)",
      offline: "var(--accent-danger)",
      inactive: "var(--accent-danger)",
      error: "var(--accent-danger)",
      maintenance: "var(--accent-warning)",
      warning: "var(--accent-warning)",
      pending: "var(--accent-warning)",
      info: "var(--accent-blue)",
      unknown: "var(--text-secondary)",
    }
    return statusColors[status?.toLowerCase()] || "var(--text-secondary)"
  }

  static getSeverityColor(severity) {
    const severityColors = {
      critical: "var(--accent-danger)",
      high: "var(--accent-danger)",
      warning: "var(--accent-warning)",
      medium: "var(--accent-warning)",
      info: "var(--accent-blue)",
      low: "var(--accent-blue)",
    }
    return severityColors[severity?.toLowerCase()] || "var(--text-secondary)"
  }

  static truncateText(text, maxLength = 50) {
    if (!text || text.length <= maxLength) return text
    return text.substring(0, maxLength) + "..."
  }

  static groupBy(array, key) {
    return array.reduce((groups, item) => {
      const group = item[key] || "unknown"
      groups[group] = (groups[group] || 0) + 1
      return groups
    }, {})
  }

  static sortBy(array, key, direction = "asc") {
    return [...array].sort((a, b) => {
      const aVal = a[key]
      const bVal = b[key]

      if (aVal < bVal) return direction === "asc" ? -1 : 1
      if (aVal > bVal) return direction === "asc" ? 1 : -1
      return 0
    })
  }

  static filterBy(array, filters) {
    return array.filter((item) => {
      return Object.entries(filters).every(([key, value]) => {
        if (value === null || value === undefined || value === "") return true
        return item[key] === value
      })
    })
  }
}

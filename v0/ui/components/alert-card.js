// Alert Card Component - Displays alert information
export class AlertCard {
  create(widget) {
    const { title, data } = widget

    const card = document.createElement("div")
    card.className = "glass-card alert-card"
    this.applyGlassStyles(card)

    // Create title
    const titleElement = document.createElement("h3")
    titleElement.className = "card-title"
    titleElement.textContent = title
    titleElement.style.cssText = `
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        `

    // Create alert summary
    const summaryElement = this.createAlertSummary(data)

    // Create recent alerts list
    const recentAlertsElement = this.createRecentAlerts(data.recent)

    card.appendChild(titleElement)
    card.appendChild(summaryElement)
    card.appendChild(recentAlertsElement)

    return card
  }

  createAlertSummary(data) {
    const summary = document.createElement("div")
    summary.className = "alert-summary"
    summary.style.cssText = `
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        `

    const alerts = [
      { label: "Critical", value: data.critical, color: "var(--accent-danger)" },
      { label: "Warning", value: data.warning, color: "var(--accent-warning)" },
      { label: "Info", value: data.info, color: "var(--accent-blue)" },
    ]

    alerts.forEach((alert) => {
      const alertElement = document.createElement("div")
      alertElement.style.cssText = `
                text-align: center;
                padding: 0.75rem;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 6px;
                border: 1px solid var(--border-glass);
            `

      alertElement.innerHTML = `
                <div style="font-size: 1.25rem; font-weight: 600; color: ${alert.color}; margin-bottom: 0.25rem;">
                    ${alert.value}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;">
                    ${alert.label}
                </div>
            `

      summary.appendChild(alertElement)
    })

    return summary
  }

  createRecentAlerts(recentAlerts) {
    const container = document.createElement("div")
    container.className = "recent-alerts"

    const header = document.createElement("h4")
    header.textContent = "Recent Alerts"
    header.style.cssText = `
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        `

    const alertsList = document.createElement("div")
    alertsList.className = "alerts-list"

    if (recentAlerts && recentAlerts.length > 0) {
      recentAlerts.forEach((alert) => {
        const alertItem = this.createAlertItem(alert)
        alertsList.appendChild(alertItem)
      })
    } else {
      alertsList.innerHTML = `
                <div style="color: var(--text-secondary); font-style: italic; text-align: center; padding: 1rem;">
                    No recent alerts
                </div>
            `
    }

    container.appendChild(header)
    container.appendChild(alertsList)

    return container
  }

  createAlertItem(alert) {
    const item = document.createElement("div")
    item.className = "alert-item"
    item.style.cssText = `
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 6px;
            border-left: 3px solid ${this.getSeverityColor(alert.severity)};
        `

    item.innerHTML = `
            <div style="font-size: 0.875rem; color: var(--text-primary); margin-bottom: 0.25rem;">
                ${alert.title}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary);">
                ${alert.message}
            </div>
        `

    return item
  }

  getSeverityColor(severity) {
    const colors = {
      critical: "var(--accent-danger)",
      warning: "var(--accent-warning)",
      info: "var(--accent-blue)",
    }
    return colors[severity] || "var(--text-secondary)"
  }

  applyGlassStyles(element) {
    element.style.cssText = `
            background: var(--bg-glass);
            backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-glass);
            transition: all var(--transition-duration) ease;
        `

    element.addEventListener("mouseenter", () => {
      element.style.transform = "translateY(-4px)"
      element.style.boxShadow = "var(--shadow-glass-hover)"
      element.style.borderColor = "var(--border-glass-hover)"
    })

    element.addEventListener("mouseleave", () => {
      element.style.transform = "translateY(0)"
      element.style.boxShadow = "var(--shadow-glass)"
      element.style.borderColor = "var(--border-glass)"
    })
  }
}

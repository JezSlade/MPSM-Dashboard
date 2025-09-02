// Dashboard UI - Main dashboard rendering
import { GlassCard } from "./components/glass-card.js"
import { MetricCard } from "./components/metric-card.js"
import { AlertCard } from "./components/alert-card.js"
import { DeviceCard } from "./components/device-card.js"

export class DashboardUI {
  constructor() {
    this.components = {
      glassCard: new GlassCard(),
      metricCard: new MetricCard(),
      alertCard: new AlertCard(),
      deviceCard: new DeviceCard(),
    }
  }

  render(container, dashboardData) {
    if (!container || !dashboardData) {
      console.error("Invalid container or dashboard data")
      return
    }

    // Clear loading state
    container.innerHTML = ""

    // Create dashboard grid
    const dashboardGrid = this.createDashboardGrid(dashboardData)
    container.appendChild(dashboardGrid)

    // Add animations
    this.animateCards(container)
  }

  createDashboardGrid(data) {
    const grid = document.createElement("div")
    grid.className = "dashboard-grid"
    grid.style.cssText = `
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            grid-auto-rows: min-content;
        `

    // Render summary cards
    const summaryCard = this.createSummaryCard(data.summary)
    grid.appendChild(summaryCard)

    // Render widget cards
    data.widgets.forEach((widget) => {
      const widgetCard = this.createWidgetCard(widget)
      if (widgetCard) {
        grid.appendChild(widgetCard)
      }
    })

    return grid
  }

  createSummaryCard(summary) {
    const card = this.components.glassCard.create({
      title: "System Overview",
      className: "summary-card",
    })

    const content = document.createElement("div")
    content.className = "summary-content"
    content.style.cssText = `
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        `

    const metrics = [
      { label: "Total Devices", value: summary.totalDevices, color: "var(--accent-blue)" },
      { label: "Active Alerts", value: summary.activeAlerts, color: "var(--accent-warning)" },
      { label: "Customers", value: summary.totalCustomers, color: "var(--accent-success)" },
      { label: "System Health", value: summary.systemHealth, color: "var(--accent-success)" },
    ]

    metrics.forEach((metric) => {
      const metricElement = this.components.metricCard.create(metric)
      content.appendChild(metricElement)
    })

    card.appendChild(content)
    return card
  }

  createWidgetCard(widget) {
    switch (widget.type) {
      case "devices":
        return this.components.deviceCard.create(widget)
      case "alerts":
        return this.components.alertCard.create(widget)
      case "customers":
        return this.createCustomerCard(widget)
      case "metrics":
        return this.createMetricsCard(widget)
      default:
        return this.components.glassCard.create({
          title: widget.title,
          content: "Widget type not supported",
        })
    }
  }

  createCustomerCard(widget) {
    const card = this.components.glassCard.create({
      title: widget.title,
      className: "customer-card",
    })

    const { data } = widget
    const content = document.createElement("div")
    content.innerHTML = `
            <div class="customer-stats">
                <div class="stat-row">
                    <span class="stat-label">Total Customers:</span>
                    <span class="stat-value">${data.total}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Active:</span>
                    <span class="stat-value" style="color: var(--accent-success)">${data.active}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Inactive:</span>
                    <span class="stat-value" style="color: var(--text-secondary)">${data.inactive}</span>
                </div>
            </div>
        `

    content.style.cssText = `
            margin-top: 1rem;
        `

    const style = document.createElement("style")
    style.textContent = `
            .stat-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px solid var(--border-glass);
            }
            .stat-row:last-child {
                border-bottom: none;
            }
            .stat-label {
                color: var(--text-secondary);
                font-size: 0.875rem;
            }
            .stat-value {
                color: var(--text-primary);
                font-weight: 600;
            }
        `

    card.appendChild(style)
    card.appendChild(content)
    return card
  }

  createMetricsCard(widget) {
    const card = this.components.glassCard.create({
      title: widget.title,
      className: "metrics-card",
    })

    const { data } = widget
    const content = document.createElement("div")
    content.innerHTML = `
            <div class="metrics-grid">
                <div class="metric-item">
                    <div class="metric-label">Uptime</div>
                    <div class="metric-value" style="color: var(--accent-success)">${data.uptime}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Response Time</div>
                    <div class="metric-value">${data.responseTime}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Throughput</div>
                    <div class="metric-value">${data.throughput}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Error Rate</div>
                    <div class="metric-value" style="color: ${data.errorRate === "0.1%" ? "var(--accent-success)" : "var(--accent-danger)"}">${data.errorRate}</div>
                </div>
            </div>
        `

    const style = document.createElement("style")
    style.textContent = `
            .metrics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 1rem;
                margin-top: 1rem;
            }
            .metric-item {
                text-align: center;
                padding: 1rem;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 8px;
                border: 1px solid var(--border-glass);
            }
            .metric-label {
                font-size: 0.75rem;
                color: var(--text-secondary);
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .metric-value {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--text-primary);
            }
        `

    card.appendChild(style)
    card.appendChild(content)
    return card
  }

  animateCards(container) {
    const cards = container.querySelectorAll(".glass-card")
    cards.forEach((card, index) => {
      card.style.opacity = "0"
      card.style.transform = "translateY(20px)"
      card.style.transition = "all 0.3s ease"

      setTimeout(() => {
        card.style.opacity = "1"
        card.style.transform = "translateY(0)"
      }, index * 100)
    })
  }
}

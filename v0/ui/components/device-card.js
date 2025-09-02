// Device Card Component - Displays device information
export class DeviceCard {
  create(widget) {
    const { title, data } = widget

    const card = document.createElement("div")
    card.className = "glass-card device-card"
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

    // Create device overview
    const overviewElement = this.createDeviceOverview(data)

    // Create device types breakdown
    const typesElement = this.createDeviceTypes(data.byType)

    card.appendChild(titleElement)
    card.appendChild(overviewElement)
    card.appendChild(typesElement)

    return card
  }

  createDeviceOverview(data) {
    const overview = document.createElement("div")
    overview.className = "device-overview"
    overview.style.cssText = `
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        `

    const statuses = [
      { label: "Online", value: data.online, color: "var(--accent-success)" },
      { label: "Offline", value: data.offline, color: "var(--accent-danger)" },
      { label: "Maintenance", value: data.maintenance, color: "var(--accent-warning)" },
    ]

    statuses.forEach((status) => {
      const statusElement = document.createElement("div")
      statusElement.style.cssText = `
                text-align: center;
                padding: 0.75rem;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 6px;
                border: 1px solid var(--border-glass);
            `

      statusElement.innerHTML = `
                <div style="font-size: 1.25rem; font-weight: 600; color: ${status.color}; margin-bottom: 0.25rem;">
                    ${status.value}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;">
                    ${status.label}
                </div>
            `

      overview.appendChild(statusElement)
    })

    return overview
  }

  createDeviceTypes(byType) {
    const container = document.createElement("div")
    container.className = "device-types"

    const header = document.createElement("h4")
    header.textContent = "Device Types"
    header.style.cssText = `
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        `

    const typesList = document.createElement("div")
    typesList.className = "types-list"

    Object.entries(byType).forEach(([type, count]) => {
      const typeItem = document.createElement("div")
      typeItem.style.cssText = `
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px solid var(--border-glass);
            `

      typeItem.innerHTML = `
                <span style="color: var(--text-secondary); text-transform: capitalize;">${type}</span>
                <span style="color: var(--text-primary); font-weight: 600;">${count}</span>
            `

      typesList.appendChild(typeItem)
    })

    container.appendChild(header)
    container.appendChild(typesList)

    return container
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

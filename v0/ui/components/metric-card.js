// Metric Card Component - Small metric display
export class MetricCard {
  create(options = {}) {
    const { label, value, color = "var(--text-primary)", icon } = options

    const card = document.createElement("div")
    card.className = "metric-card"
    card.style.cssText = `
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s ease;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        `

    // Add hover effect
    card.addEventListener("mouseenter", () => {
      card.style.background = "rgba(255, 255, 255, 0.08)"
      card.style.transform = "translateY(-2px)"
    })

    card.addEventListener("mouseleave", () => {
      card.style.background = "rgba(255, 255, 255, 0.05)"
      card.style.transform = "translateY(0)"
    })

    // Create value element
    const valueElement = document.createElement("div")
    valueElement.className = "metric-value"
    valueElement.textContent = value
    valueElement.style.cssText = `
            font-size: 1.5rem;
            font-weight: 700;
            color: ${color};
            margin-bottom: 0.25rem;
        `

    // Create label element
    const labelElement = document.createElement("div")
    labelElement.className = "metric-label"
    labelElement.textContent = label
    labelElement.style.cssText = `
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        `

    card.appendChild(valueElement)
    card.appendChild(labelElement)

    return card
  }
}

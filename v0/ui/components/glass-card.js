// Glass Card Component - Base glassmorphic card
import { ThemeConfig } from "../../config/theme-config.js"

export class GlassCard {
  create(options = {}) {
    const { title, content, className = "", onClick } = options

    const card = document.createElement("div")
    card.className = `glass-card ${className}`

    // Apply glass card styles
    this.applyGlassStyles(card)

    // Add title if provided
    if (title) {
      const titleElement = this.createTitle(title)
      card.appendChild(titleElement)
    }

    // Add content if provided
    if (content) {
      const contentElement = this.createContent(content)
      card.appendChild(contentElement)
    }

    // Add click handler if provided
    if (onClick) {
      card.style.cursor = "pointer"
      card.addEventListener("click", onClick)
    }

    return card
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
            position: relative;
            overflow: hidden;
        `

    // Add hover effects
    element.addEventListener("mouseenter", () => {
      element.style.transform = `translateY(${ThemeConfig.effects["hover-lift"]})`
      element.style.boxShadow = "var(--shadow-glass-hover)"
      element.style.borderColor = "var(--border-glass-hover)"
    })

    element.addEventListener("mouseleave", () => {
      element.style.transform = "translateY(0)"
      element.style.boxShadow = "var(--shadow-glass)"
      element.style.borderColor = "var(--border-glass)"
    })
  }

  createTitle(title) {
    const titleElement = document.createElement("h3")
    titleElement.className = "card-title"
    titleElement.textContent = title
    titleElement.style.cssText = `
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
            margin: 0 0 1rem 0;
        `
    return titleElement
  }

  createContent(content) {
    const contentElement = document.createElement("div")
    contentElement.className = "card-content"
    contentElement.style.cssText = `
            color: var(--text-secondary);
            line-height: 1.6;
        `

    if (typeof content === "string") {
      contentElement.innerHTML = content
    } else {
      contentElement.appendChild(content)
    }

    return contentElement
  }
}

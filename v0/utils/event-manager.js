// Event Management Utilities
export class EventManager {
  constructor() {
    this.listeners = new Map()
  }

  on(eventName, callback) {
    if (!this.listeners.has(eventName)) {
      this.listeners.set(eventName, [])
    }
    this.listeners.get(eventName).push(callback)
  }

  off(eventName, callback) {
    if (!this.listeners.has(eventName)) return

    const callbacks = this.listeners.get(eventName)
    const index = callbacks.indexOf(callback)
    if (index > -1) {
      callbacks.splice(index, 1)
    }
  }

  emit(eventName, data) {
    if (!this.listeners.has(eventName)) return

    const callbacks = this.listeners.get(eventName)
    callbacks.forEach((callback) => {
      try {
        callback(data)
      } catch (error) {
        console.error(`Error in event listener for ${eventName}:`, error)
      }
    })
  }

  clear(eventName) {
    if (eventName) {
      this.listeners.delete(eventName)
    } else {
      this.listeners.clear()
    }
  }

  static createCustomEvent(name, detail) {
    return new CustomEvent(name, { detail })
  }

  static dispatchGlobalEvent(name, detail) {
    const event = this.createCustomEvent(name, detail)
    document.dispatchEvent(event)
  }
}

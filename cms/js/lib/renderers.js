/**
 * WIDGET DASHBOARD APPLICATION
 * * This file contains the rendering system for the dashboard.
 * It defines the renderer classes for different widget types.
 */

// ===== RENDERING SYSTEM =====

/**
 * Abstract renderer interface
 * Defines contract for all renderer implementations
 * * @abstract
 * @class IRenderer
 */
class IRenderer {
    render(data) {
        throw new Error('render() method must be implemented by subclass');
    }

    update(element, data) {
        throw new Error('update() method must be implemented by subclass');
    }

    destroy(element) {
        throw new Error('destroy() method must be implemented by subclass');
    }
}

/**
 * Base widget renderer class
 * Provides common rendering functionality
 * * @class BaseWidgetRenderer
 * @extends IRenderer
 */
class BaseWidgetRenderer extends IRenderer {
    /**
     * Renders widget controls
     * @param {Object} widget - Widget instance
     * @returns {string} Controls HTML
     * @protected
     */
    renderControls(widget) {
        return `
            <div class="widget-controls">
                <button class="widget-control-btn widget-control-info" onclick="app.showWidgetInfo('${widget.id}')" title="Widget Info">ℹ️</button>
                <button class="widget-control-btn widget-control-edit" onclick="app.editWidget('${widget.id}')" title="Edit Widget">✏️</button>
                <button class="widget-control-btn widget-control-delete" onclick="app.removeWidgetFromArea('${widget.id}')" title="Remove from Area">🗑️</button>
            </div>
        `;
    }

    /**
     * Escapes HTML characters
     * @param {string} unsafe - Unsafe string
     * @returns {string} Escaped string
     * @protected
     */
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Processes widget content for display
     * @param {string} content - Raw content
     * @param {Object} widget - Widget instance
     * @returns {string} Processed content
     * @protected
     */
    processContent(content, widget) {
        // Basic content processing - can be extended
        return this.escapeHtml(content);
    }
}

/**
 * Widget renderer factory
 * Creates appropriate renderer based on widget type
 * * @class WidgetRendererFactory
 */
export class WidgetRendererFactory {
    constructor() {
        this.renderers = new Map();
        this.registerDefaultRenderers();
    }

    /**
     * Registers default widget renderers
     * @private
     */
    registerDefaultRenderers() {
        this.register('text', new TextWidgetRenderer());
        this.register('image', new ImageWidgetRenderer());
        this.register('video', new VideoWidgetRenderer());
        this.register('card', new CardWidgetRenderer());
        this.register('banner', new BannerWidgetRenderer());
        this.register('list', new ListWidgetRenderer());
        this.register('form', new FormWidgetRenderer());
        this.register('custom', new CustomWidgetRenderer());
    }

    /**
     * Registers widget renderer
     * @param {string} type - Widget type
     * @param {IRenderer} renderer - Renderer instance
     */
    register(type, renderer) {
        this.renderers.set(type, renderer);
    }

    /**
     * Gets renderer for widget type
     * @param {string} type - Widget type
     * @returns {IRenderer} Renderer instance
     */
    getRenderer(type) {
        return this.renderers.get(type) || this.renderers.get('custom');
    }

    /**
     * Renders widget using appropriate renderer
     * @param {Object} widget - Widget to render
     * @returns {string} Rendered HTML
     */
    renderWidget(widget) {
        const renderer = this.getRenderer(widget.type);
        return renderer.render(widget);
    }
}

/**
 * Text Widget Renderer
 * @class TextWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class TextWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const content = this.processContent(widget.content, widget);
        return `
            <div class="rendered-widget widget-type-text status-${widget.status}" id="widget-${widget.id}" data-type="text" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <div class="card-body">
                    <h5 class="typography-h4 mb-1">${this.escapeHtml(widget.title)}</h5>
                    <p class="typography-body">${content}</p>
                </div>
            </div>
        `;
    }
}

/**
 * Image Widget Renderer
 * @class ImageWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class ImageWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const settings = widget.getSettingsObject();
        const imageUrl = this.escapeHtml(widget.content);
        const altText = this.escapeHtml(settings.alt || widget.title);
        return `
            <div class="rendered-widget widget-type-image status-${widget.status}" id="widget-${widget.id}" data-type="image" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <img src="${imageUrl}" alt="${altText}" style="max-width: 100%; height: auto; display: block;">
                <div class="card-body">
                    <h5 class="typography-h4">${this.escapeHtml(widget.title)}</h5>
                </div>
            </div>
        `;
    }
}

/**
 * Video Widget Renderer
 * @class VideoWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class VideoWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const videoUrl = this.escapeHtml(widget.content);
        return `
            <div class="rendered-widget widget-type-video status-${widget.status}" id="widget-${widget.id}" data-type="video" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <iframe src="${videoUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
            </div>
        `;
    }
}

/**
 * Card Widget Renderer
 * @class CardWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class CardWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const settings = widget.getSettingsObject();
        const header = settings.showHeader ? `<div class="card-header"><h5 class="typography-h4">${this.escapeHtml(widget.title)}</h5></div>` : '';
        const footer = settings.showFooter ? `<div class="card-footer">${this.escapeHtml(settings.footerText || '')}</div>` : '';
        const bodyContent = this.processContent(widget.content, widget);
        return `
            <div class="rendered-widget widget-type-card status-${widget.status}" id="widget-${widget.id}" data-type="card" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <div class="card">
                    ${header}
                    <div class="card-body">${bodyContent}</div>
                    ${footer}
                </div>
            </div>
        `;
    }
}

/**
 * Banner Widget Renderer
 * @class BannerWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class BannerWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const content = this.processContent(widget.content, widget);
        return `
            <div class="rendered-widget widget-type-banner status-${widget.status}" id="widget-${widget.id}" data-type="banner" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <div class="p-3">${content}</div>
            </div>
        `;
    }
}

/**
 * List Widget Renderer
 * @class ListWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class ListWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const listItems = widget.content.split('\n').map(item => `<li>${this.escapeHtml(item.trim())}</li>`).join('');
        return `
            <div class="rendered-widget widget-type-list status-${widget.status}" id="widget-${widget.id}" data-type="list" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <div class="card-body">
                    <h5 class="typography-h4">${this.escapeHtml(widget.title)}</h5>
                    <ul>${listItems}</ul>
                </div>
            </div>
        `;
    }
}

/**
 * Form Widget Renderer
 * @class FormWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class FormWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        const settings = widget.getSettingsObject();
        const formAction = this.escapeHtml(settings.action || '#');
        const formMethod = this.escapeHtml(settings.method || 'POST');
        const formFields = settings.fields ? settings.fields.map(field => {
            const label = this.escapeHtml(field.label);
            const name = this.escapeHtml(field.name);
            const type = this.escapeHtml(field.type);
            return `<label>${label}: <input type="${type}" name="${name}"></label>`;
        }).join('<br>') : '';

        return `
            <div class="rendered-widget widget-type-form status-${widget.status}" id="widget-${widget.id}" data-type="form" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <div class="card-body">
                    <h5 class="typography-h4">${this.escapeHtml(widget.title)}</h5>
                    <form action="${formAction}" method="${formMethod}">
                        ${formFields}
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </form>
                </div>
            </div>
        `;
    }
}

/**
 * Custom Widget Renderer
 * @class CustomWidgetRenderer
 * @extends BaseWidgetRenderer
 */
class CustomWidgetRenderer extends BaseWidgetRenderer {
    render(widget) {
        // Renders content without a specific wrapper
        const content = widget.content || '<p class="empty-state">No content to display.</p>';
        return `
            <div class="rendered-widget widget-type-custom status-${widget.status}" id="widget-${widget.id}" data-type="custom" data-id="${widget.id}">
                ${this.renderControls(widget)}
                <div class="card-body">
                    <h5 class="typography-h4 mb-1">${this.escapeHtml(widget.title)}</h5>
                    <div class="custom-content">${content}</div>
                </div>
            </div>
        `;
    }
}
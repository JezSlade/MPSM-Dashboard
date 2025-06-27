document.addEventListener('DOMContentLoaded', () => {
    if (typeof GridStack === 'undefined') {
        console.error("GridStack is not available. Ensure the library is loaded correctly.");
        return;
    }

    const grid = GridStack.init({
        float: true,
        removable: true,
        acceptWidgets: true,
        styleInHead: true
    });

    fetch('/api/widgets')
        .then(res => res.json())
        .then(widgets => {
            widgets.forEach(widget => {
                grid.addWidget({
                    id: widget.id,
                    x: widget.x,
                    y: widget.y,
                    w: widget.width,
                    h: widget.height,
                    content: `<div class="widget" data-id="${widget.id}">
                                <div class="widget-header">${widget.title}</div>
                                <div class="widget-body" id="widget-${widget.id}"></div>
                              </div>`
                });
                loadWidgetContent(widget.id);
            });
        });

    grid.on('change', (e, items) => {
        if (!e?.target?.classList?.contains('grid-stack-item')) {
            console.warn('Event target missing classList or unexpected type:', e?.target);
            return;
        }
        items.forEach(item => {
            updateWidgetPosition(item.id, item.x, item.y, item.width, item.height);
        });
    });
});

function loadWidgetContent(widgetId) {
    fetch(`/render_widget.php?id=${widgetId}`)
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById(`widget-${widgetId}`);
            if (container) {
                container.innerHTML = html;
            } else {
                console.warn(`Missing widget container for ID ${widgetId}`);
            }
        });
}

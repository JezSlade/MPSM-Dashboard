document.addEventListener('DOMContentLoaded', () => {
    const grid = new GridStack({
        float: true,
        removable: true,
        acceptWidgets: true,
        styleInHead: true
    });

    // Load widget positions from API
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

    // Auto-save positions
    grid.on('change', (e, items) => {
        items.forEach(item => {
            updateWidgetPosition(item.id, item.x, item.y, item.width, item.height);
        });
    });
});

function loadWidgetContent(widgetId) {
    fetch(`/render_widget.php?id=${widgetId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById(`widget-${widgetId}`).innerHTML = html;
        });
}
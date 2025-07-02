
</html>



<!-- Theme Component Library Modal -->
<div class="message-modal" id="theme-modal" style="display: none;">
    <div class="message-modal-header">
        <h2>Theme Component Library</h2>
        <button class="btn btn-danger" onclick="closeThemeModal()">×</button>
    </div>
    <div class="message-modal-body">
        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Buttons</h3>
            <button class="btn btn-primary">Primary Button</button>
            <button class="btn btn-secondary">Secondary</button>
            <button class="btn btn-outline">Outline</button>
            <button class="btn btn-danger">Danger</button>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Input Fields</h3>
            <input type="text" class="form-control" placeholder="Text input">
            <input type="password" class="form-control" placeholder="Password">
            <select class="form-control">
                <option>Option 1</option>
                <option>Option 2</option>
            </select>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Cards</h3>
            <div class="card">
                <div class="card-header">Card Title</div>
                <div class="card-body">This is the body of a neumorphic card.</div>
            </div>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Badges & Tags</h3>
            <span class="badge badge-success">Success</span>
            <span class="badge badge-warning">Warning</span>
            <span class="badge badge-info">Info</span>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Progress & Loaders</h3>
            <div class="progress-bar"><div class="progress" style="width: 60%;"></div></div>
            <div class="loader"></div>
        </section>
    </div>
</div>

<script>
function openThemeModal() {
    document.getElementById('theme-modal').style.display = 'block';
}
function closeThemeModal() {
    document.getElementById('theme-modal').style.display = 'none';
}
</script>

<script src="version.js"></script>
<script>
window.addEventListener("DOMContentLoaded", () => {
    if (window.appVersion) {
        const raw = window.appVersion.split(".").pop();
        const verInt = parseInt(raw);
        const v1 = Math.floor(verInt / 100);
        const v2 = Math.floor((verInt % 100) / 10);
        const v3 = verInt % 10 + ((verInt % 100) >= 10 ? 0 : (verInt % 100));
        document.getElementById("ver-1").textContent = v1;
        document.getElementById("ver-2").textContent = v2;
        document.getElementById("ver-3").textContent = v3;
    }
});

function openThemeModal() {
    document.getElementById("theme-modal").style.display = 'block';
}
function closeThemeModal() {
    document.getElementById("theme-modal").style.display = 'none';
}
</script>

</body>
</html>
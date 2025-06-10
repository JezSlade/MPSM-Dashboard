<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MPSM Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}" />
    <script>
        window.allEndpoints = @json($allEndpoints);
        window.roleMappings = @json($roleMappings);
        window.apiBaseUrl = '{{ env('BASE_URL') }}';
        window.DEALER_CODE = '{{ env('DEALER_CODE') }}';
    </script>
</head>
<body>
<header class="glass-panel header">
    <div class="status-panel">
        DB: <span id="dbStatus" class="status-dot"></span>
        API: <span id="apiStatus" class="status-dot"></span>
    </div>
    <div class="header-right">
        <div class="search-container">
            <input list="customerList" id="customerSelect" class="dropdown" placeholder="Search Customer"/>
            <datalist id="customerList"></datalist>
        </div>
        <span class="version-display">v<span id="versionDisplay"></span></span>
        <button id="toggleDebug" class="btn" style="display:none">Hide Debug</button>
    </div>
</header>
<div class="body-container">
    <aside id="sidebar" class="sidebar"></aside>
    <main class="main-content">
        <div id="cardsViewport" class="cards-container"></div>
    </main>
</div>
<div id="modal" class="modal">
    <div class="modal-content">
        <button id="modalClose" class="btn modal-close">×</button>
        <div id="modalBody"></div>
    </div>
</div>
<div id="debug-panel" class="debug-panel" style="display:none">
    <div class="debug-header">
        <div class="debug-title">🐛 Debug Console</div>
        <button id="debugClear" class="btn debug-clear">Clear</button>
    </div>
    <div class="debug-content" id="debug-content"></div>
</div>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('versionDisplay').textContent = window.appVersion || 'n/a';
    });
</script>
</body>
</html>

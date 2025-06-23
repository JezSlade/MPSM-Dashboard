<?php
/**
 * Main Navigation
 * ------------------------------------------------------------------
 * Switcher between Dashboard and Sandbox views.
 *   • Uses query-string routing (?view=dashboard|sandbox)
 *   • Follows MPSM CODE AUDIT PROTOCOL (no external libs, __DIR__ safety)
 */
// ------------------------------------------------------------------
// DEBUG BLOCK (Always Keep at Top)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
// ------------------------------------------------------------------

declare(strict_types=1);

// Which tab is active?
$currentView = $_GET['view'] ?? 'dashboard';

// Helper to build each <li>/<a>; guarded to avoid redeclaration
if (!function_exists('build_nav_link')) {
    function build_nav_link(string $label, string $view, string $current): string
    {
        $isActive = ($view === $current) ? 'active' : '';
        $href     = '/index.php?view=' . urlencode($view);

        return
            '<li class="' . $isActive . '">' .
                '<a href="' . $href . '">' . htmlspecialchars($label) . '</a>' .
            '</li>';
    }
}
?>
<nav class="main-nav">
    <ul>
        <?php echo build_nav_link('Dashboard', 'dashboard', $currentView); ?>
        <?php echo build_nav_link('Sandbox',   'sandbox',   $currentView); ?>
    </ul>
</nav>

<style>
/* Glass / neumorphic navigation styling */
.main-nav {
    position: sticky;
    top: 0;
    z-index: 999;
    backdrop-filter: blur(10px);
    background: var(--bg-card, rgba(255,255,255,0.06));
    width: 100%;
}
.main-nav ul {
    margin: 0;
    padding: 0.75rem 1.5rem;
    display: flex;
    gap: 2rem;
    list-style: none;
}
.main-nav li a {
    text-decoration: none;
    font-weight: 600;
    color: var(--text-dark, #f5f5f5);
}
.main-nav li.active a {
    text-decoration: underline;
}
</style>

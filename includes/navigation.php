<?php
/**
 * Main Navigation
 * ------------------------------------------------------------------
 * Two-tab switcher between Dashboard and Sandbox views.
 * Uses simple query-string routing so no rewrite rules are needed.
 */

declare(strict_types=1);

// Determine which tab is “active”
$currentView = $_GET['view'] ?? 'dashboard';

// Tiny helper to build <li> links safely
function nav_link(string $label, string $view, string $current): string
{
    $isActive = ($view === $current) ? 'active' : '';
    $href = '/index.php?view=' . urlencode($view);

    return '<li class="' . $isActive . '"><a href="' . $href . '">' . htmlspecialchars($label) . '</a></li>';
}
?>

<nav class="main-nav">
    <ul>
        <?= nav_link('Dashboard', 'dashboard', $currentView) ?>
        <?= nav_link('Sandbox',   'sandbox',   $currentView) ?>
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

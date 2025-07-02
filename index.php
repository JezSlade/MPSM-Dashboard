<?php
// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// --- Cache Control Headers ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
// --- End Cache Control Headers ---

session_start();

// Include configuration and helper functions/classes
require_once 'config.php';
require_once 'helpers.php';
require_once 'src/php/DashboardManager.php';
require_once 'src/php/FileManager.php'; // Ensure FileManager is included if used for widget creation

// Instantiate DashboardManager
// $available_widgets is populated by discover_widgets() in config.php
$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, $available_widgets);

// Load current dashboard state (settings + widget states)
$current_dashboard_state = $dashboardManager->loadDashboardState();
$settings = $current_dashboard_state; // $settings now includes 'widgets_state'

// IMPORTANT: Initialize $_SESSION['dashboard_settings'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['dashboard_settings'] = $current_dashboard_state;

// The index.php no longer handles POST requests directly for actions.
// All actions are now handled by dedicated API endpoints via AJAX.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This part should ideally not be reached for action_type POSTs
    // as AJAX requests are now handled by api/dashboard.php etc.
}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by AJAX and then reloaded via loadDashboardState().
$settings = array_replace_recursive($dashboardManager->loadDashboardState(), $_SESSION['dashboard_settings'] ?? []);

// Pass available widgets to the view
global $available_widgets; // Ensure $available_widgets from config.php is accessible

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <!-- Chart.js CDN for charting widgets -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <style>
        :root {
            --accent: <?= $settings['accent_color'] ?>;
            --glass-bg: rgba(35, 40, 49, <?= $settings['glass_intensity'] ?>);
            --blur-amount: <?= $settings['blur_amount'] ?>;
        }
        .widget {
            transition: <?= $settings['enable_animations'] ? 'var(--transition)' : 'none' ?>;
        }
        .widget:hover {
            <?php if ($settings['enable_animations']): ?>
            transform: translateY(-5px);
            box-shadow:
                12px 12px 24px var(--shadow-dark),
                -12px -12px 24px rgba(74, 78, 94, 0.1);
            <?php endif; ?>
        }
    </style>
</head>
<body>
    <!-- New: Overlay for expanded widgets -->
    <div class="widget-expanded-overlay" id="widget-expanded-overlay"></div>

    <!-- Widget Settings Modal Structure (for single widget dimensions) -->
    <div class="message-modal-overlay" id="widget-settings-modal-overlay">
        <div class="message-modal" id="widget-settings-modal">
            <div class="message-modal-header">
                <h3 id="widget-settings-modal-title">Widget Settings</h3>
                <button class="btn-close-modal" id="close-widget-settings-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <form id="widget-dimensions-form">
                    <!-- Changed from widget_index to widget_id -->
                    <input type="hidden" id="widget-settings-id" name="widget_id">
                    <div class="form-group">
                        <label for="widget-settings-width">Width (Grid Units)</label>
                        <input type="number" id="widget-settings-width" name="new_width" min="0.5" max="3" step="0.5" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="widget-settings-height">Height (Grid Units)</label>
                        <input type="number" id="widget-settings-height" name="new_height" min="0.5" max="4" step="0.5" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Save Dimensions</button>
                </form>
            </div>
        </div>
    </div>
    <!-- END Widget Settings Modal Structure -->

    <!-- NEW: Widget Management Modal Structure (Consolidated Widget Settings) -->
    <div class="message-modal-overlay" id="widget-management-modal-overlay">
        <div class="message-modal large-modal" id="widget-management-modal">
            <div class="message-modal-header">
                <h3 id="widget-management-modal-title">Widget Management</h3>
                <button class="btn-close-modal" id="close-widget-management-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="widget-management-table">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Width</th>
                                <th>Height</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody id="widget-management-table-body">
                            <!-- Widget data will be populated here by JavaScript -->
                            <tr><td colspan="6" style="text-align: center; padding: 20px;">Loading widgets...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="message-modal-footer">
                <button class="btn btn-primary" id="save-widget-management-changes-btn">Save All Widget Changes</button>
            </div>
        </div>
    </div>
    <!-- END NEW: Widget Management Modal Structure -->

    <!-- NEW: Create New Widget Modal Structure -->
    <div class="message-modal-overlay" id="create-widget-modal-overlay">
        <div class="message-modal" id="create-widget-modal">
            <div class="message-modal-header">
                <h3>Create New Widget Template</h3>
                <button class="btn-close-modal" id="close-create-widget-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <form id="create-widget-form">
                    <div class="form-group">
                        <label for="new-widget-name">Widget Name</label>
                        <input type="text" id="new-widget-name" name="name" class="form-control" placeholder="e.g., My Custom Chart" required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-id">Widget ID (lowercase, no spaces, e.g., my_custom_chart)</label>
                        <input type="text" id="new-widget-id" name="id" class="form-control" placeholder="e.g., my_custom_chart" pattern="^[a-z0-9_]+$" title="Lowercase letters, numbers, and underscores only." required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-icon">Font Awesome Icon (e.g., chart-bar)</label>
                        <input type="text" id="new-widget-icon" name="icon" class="form-control" value="cube" placeholder="e.g., chart-bar">
                    </div>
                    <div class="form-group">
                        <label for="new-widget-width">Default Width (0.5-3.0 grid units)</label>
                        <input type="number" id="new-widget-width" name="width" class="form-control" value="1.0" min="0.5" max="3" step="0.5" required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-height">Default Height (0.5-4.0 grid units)</label>
                        <input type="number" id="new-widget-height" name="height" class="form-control" value="1.0" min="0.5" max="4" step="0.5" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-plus"></i> Create Widget Template
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- END NEW: Create New Widget Modal Structure -->

    <div class="dashboard">
        <!-- Dashboard Header -->
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <!-- Use the site_icon setting here -->
                    <i class="fas fa-<?= htmlspecialchars($settings['site_icon'] ?? 'gem') ?>"></i>
                </div>
                <div class="logo-text"><?= htmlspecialchars($settings['title']) ?></div>
                <div class="logo-version" style="font-size: 0.75em; color: #bbb; margin-left: 8px;" id="version-display">
                    <strong>v</strong>
                    <span id="ver-1" class="version-segment">?</span>.
                    <span id="ver-2" class="version-segment">?</span>.
                    <span id="ver-3" class="version-segment">?</span>
                </div>
            </div>

            <div class="header-actions">
                <button class="btn" id="settings-toggle">
                    <i class="fas fa-cog"></i> Settings
                </button>
                <button class="btn" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </header>

        <!-- Dashboard Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="section-title">Navigation</div>
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item" onclick="openThemeModal()">
                    <i class="fas fa-layer-group"></i>
                    <span>Theme Library</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </div>
                <div class="nav-item" id="widget-management-nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Widget Management</span>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-title">Widget Library</div>
                <div class="widget-list">
                    <?php foreach ($available_widgets as $id => $widget): ?>
                    <div class="widget-item" draggable="true" data-widget-id="<?= $id ?>">
                        <i class="fas fa-<?= $widget['icon'] ?>"></i>
                        <div class="widget-name"><?= $widget['name'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-title">Dashboard Settings</div>
                <div class="nav-item" id="general-settings-nav-item">
                    <i class="fas fa-cog"></i>
                    <span>General Settings</span>
                </div>
                <div class="nav-item" id="layout-settings-nav-item">
                    <i class="fas fa-columns"></i>
                    <span>Layout Settings</span>
                </div>
                <div class="nav-item" id="advanced-settings-nav-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Settings</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content" id="widget-container">
            <?php
            // Filter widgets to render based on 'show_all_available_widgets' and 'is_active'
            $widgets_to_render = [];
            if ($settings['show_all_available_widgets']) {
                // If 'show all' is true, render ALL discovered widgets
                $widgets_to_render = array_values($settings['widgets_state']);
            } else {
                // Otherwise, render only widgets marked as active
                foreach ($settings['widgets_state'] as $widget_id => $widget_data) {
                    if ($widget_data['is_active']) {
                        $widgets_to_render[] = $widget_data;
                    }
                }
            }

            // Sort widgets for rendering by their 'position'
            usort($widgets_to_render, function($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            foreach ($widgets_to_render as $widget):
                $widget_id = $widget['id'];
                // Use the dimensions from the widgets_state array
                $current_width_user_facing = max(0.5, min(3.0, (float)($widget['width'])));
                $current_height_user_facing = max(0.5, min(4.0, (float)($widget['height'])));

                // Convert user-facing units to internal grid units (doubled for half-unit precision)
                $current_width_internal = $current_width_user_facing * 2;
                $current_height_internal = $current_height_user_facing * 2;
            ?>
            <!-- Widget container, made draggable for reordering -->
            <div class="widget"
                 draggable="true"
                 style="--width: <?= $current_width_internal ?>; --height: <?= $current_height_internal ?>;"
                 data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                 data-current-width="<?= $current_width_user_facing ?>"
                 data-current-height="<?= $current_height_user_facing ?>">
                <!-- This placeholder div marks the widget's original position in the DOM -->
                <div class="widget-placeholder" data-original-parent-id="widget-container" data-original-id="<?= htmlspecialchars($widget_id) ?>"></div>

                <div class="widget-header">
                    <div class="widget-title">
                        <i class="fas fa-<?= htmlspecialchars($widget['icon']) ?>"></i>
                        <span><?= htmlspecialchars($widget['name']) ?></span>
                    </div>
                    <div class="widget-actions">
                        <!-- Add data attributes to identify actions -->
                        <div class="widget-action action-settings"
                            data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                            data-current-width="<?= $current_width_user_facing ?>"
                            data-current-height="<?= $current_height_user_facing ?>"
                            title="Adjust widget dimensions">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="widget-action action-expand">
                            <i class="fas fa-expand"></i>
                        </div>
                        <!-- Remove button now triggers deactivation -->
                        <div class="widget-action remove-widget"
                            data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                            title="Deactivate widget">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <?= render_widget($widget_id) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>

    <!-- Simple Message Modal Structure (for general confirmations/alerts) -->
    <div class="message-modal-overlay" id="message-modal-overlay">
        <div class="message-modal">
            <div class="message-modal-header">
                <h3 id="message-modal-title"></h3>
                <button class="btn-close-modal" id="close-message-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <p id="message-modal-content"></p>
            </div>
            <div class="message-modal-footer">
                <button class="btn btn-primary" id="confirm-message-modal">OK</button>
            </div>
        </div>
    </div>


    <!-- Settings Panel (Global Dashboard Settings) -->
    <div class="overlay" id="settings-overlay"></div>
    <div class="settings-panel" id="settings-panel">
        <div class="settings-header">
            <h2>Dashboard Settings</h2>
            <button class="btn" id="close-settings">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Settings Panel Navigation Tabs -->
        <div class="settings-tabs">
            <button class="settings-tab-btn active" data-target="general-settings-section">General</button>
            <button class="settings-tab-btn" data-target="layout-settings-section">Layout</button>
            <button class="settings-tab-btn" data-target="advanced-settings-section">Advanced</button>
        </div>

        <form id="global-settings-form" method="post" class="settings-form">
            <!-- General Settings Section -->
            <div class="settings-section active" id="general-settings-section">
                <div class="settings-group">
                    <h3 class="settings-title">General Settings</h3>

                    <div class="form-group">
                        <label for="dashboard_title">Dashboard Title</label>
                        <input type="text" id="dashboard_title" name="dashboard_title"
                            class="form-control" value="<?= htmlspecialchars($settings['title']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="site_icon">Site Icon (Font Awesome class, e.g., gem)</label>
                        <input type="text" id="site_icon" name="site_icon"
                            class="form-control" value="<?= htmlspecialchars($settings['site_icon'] ?? 'gem') ?>" placeholder="e.g., gem">
                    </div>

                    <div class="form-group">
                        <label for="accent_color">Accent Color</label>
                        <input type="color" id="accent_color" name="accent_color"
                            class="form-control" value="<?= $settings['accent_color'] ?>" style="height: 50px;">
                    </div>

                    <div class="form-group">
                        <label>Enable Animations</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_animations"
                                <?= $settings['enable_animations'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-group">
                    <h3 class="settings-title">Glass Effect</h3>

                    <div class="form-group">
                        <label for="glass_intensity">Glass Intensity</label>
                        <input type="range" id="glass_intensity" name="glass_intensity"
                            class="form-control" min="0.1" max="0.9" step="0.05"
                            value="<?= $settings['glass_intensity'] ?>">
                    </div>

                    <div class="form-group">
                        <label for="blur_amount">Blur Amount</labeSl>
                        <select id="blur_amount" name="blur_amount" class="form-control">
                            <option value="5px" <?= $settings['blur_amount'] == '5px' ? 'selected' : '' ?>>Subtle (5px)</option>
                            <option value="10px" <?= $settings['blur_amount'] == '10px' ? 'selected' : '' ?>>Standard (10px)</option>
                            <option value="15px" <?= $settings['blur_amount'] == '15px' ? 'selected' : '' ?>>Strong (15px)</option>
                            <option value="20px" <?= $settings['blur_amount'] == '20px' ? 'selected' : '' ?>>Extra Strong (20px)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Layout Settings Section -->
            <div class="settings-section" id="layout-settings-section">
                <div class="settings-group">
                    <h3 class="settings-title">Widget Layout</h3>
                    <!-- Show All Available Widgets Toggle -->
                    <div class="form-group">
                        <label>Show All Available Widgets (Overrides active/inactive status)</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="show_all_available_widgets" id="show_all_available_widgets"
                                <?= $settings['show_all_available_widgets'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-group">
                    <h3 class="settings-title">Add Existing Widget</h3>
                    <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 15px;">
                        Use Widget Management to activate/deactivate widgets.
                    </p>
                    <button type="button" class="btn" style="width: 100%;" disabled>
                        <i class="fas fa-info-circle"></i> Use Widget Management
                    </button>
                </div>

                <!-- Create New Widget Template Button -->
                <div class="settings-group">
                    <h3 class="settings-title">Create New Widget</h3>
                    <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 15px;">
                        Generate a new blank widget file ready for your custom code.
                    </p>
                    <button type="button" class="btn btn-primary" id="open-create-widget-modal" style="width: 100%;">
                        <i class="fas fa-file-code"></i> Create New Widget Template
                    </button>
                </div>
            </div>

            <!-- Advanced Settings Section -->
            <div class="settings-section" id="advanced-settings-section">
                <div class="settings-group">
                    <h3 class="settings-title">Advanced Options</h3>
                    <div class="form-group">
                        <label>Export Configuration</label>
                        <button class="btn" id="export-settings-btn" style="width: 100%;">
                            <i class="fas fa-download"></i> Download Settings
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Import Configuration</label>
                        <input type="file" class="form-control" id="import-settings-file-input">
                        <button type="button" class="btn btn-primary" id="import-settings-btn" style="width: 100%; margin-top: 10px;">
                            <i class="fas fa-upload"></i> Upload Settings
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Output Current Settings (JSON)</label>
                        <button type="button" class="btn" id="output-settings-json-btn" style="width: 100%;">
                            <i class="fas fa-code"></i> Show Current Settings
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Output Active Widgets (JSON)</label>
                        <button type="button" class="btn" id="output-active-widgets-json-btn" style="width: 100%;">
                            <i class="fas fa-list"></i> Show Active Widgets
                        </button>
                    </div>

                    <!-- Delete Settings JSON Button -->
                    <div class="form-group">
                        <label>Reset Dashboard</label>
                        <button type="button" id="delete-settings-json-btn" class="btn btn-danger" style="width: 100%;">
                            <i class="fas fa-trash-alt"></i> Delete Settings JSON (Reset All)
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </form>
    </div>

    <script type="module" src="src/js/main.js"></script>

<!-- Settings Modal (Canonical Design Reference) -->
<div id="settings-overlay" class="message-modal-overlay active">
  <div id="settings-panel" class="message-modal large-modal">
    <div class="tabs">
      <button class="tab-button" onclick="showTab('design-tab')">Design Components</button>
      <button class="tab-button" onclick="showTab('icons-tab')">Icon Reference</button>
    </div>
    <div class="tab-content" id="design-tab" style="display: block;">
      <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web App Design Components</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Variables from dashboard.css */
        :root {
            --primary: #00bcd4; /* Neon Cyan */
            --secondary: #e91e63; /* Neon Magenta */
            --accent: #ffeb3b; /* Neon Yellow - Default accent */
            --text-primary: #e0e0e0; /* Light text for readability */
            --text-secondary: #a0a0a0; /* Subtler text */
            --bg-primary: #0d0d1a; /* Very dark blue/black for main background */
            --bg-secondary: #1a1a2e; /* Slightly lighter dark blue for secondary backgrounds */
            --glass-bg: rgba(25, 25, 40, 0.4); /* Darker, more transparent glass */
            --glass-border: rgba(255, 255, 255, 0.08); /* More subtle glass border */
            --shadow-dark: rgba(0, 0, 0, 0.6); /* Deeper shadows for glass elements */
            --shadow-light: rgba(255, 255, 255, 0.02); /* Very subtle light shadow */
            --neon-glow-primary: 0 0 10px rgba(0, 188, 212, 0.6), 0 0 20px rgba(0, 188, 212, 0.4); /* Cyan glow */
            --neon-glow-secondary: 0 0 10px rgba(233, 30, 99, 0.6), 0 0 20px rgba(233, 30, 99, 0.4); /* Magenta glow */
            --success: #4CAF50;
            --danger: #F44336;
            --warning: #FFC107;
            --info: #2196F3;

            --header-height: 60px;
            --sidebar-width: 250px;
            --transition: all 0.3s ease-in-out; /* Smooth transitions for UI elements */
            --border-radius: 12px; /* Consistent rounded corners */

            /* Settings Panel Variables for accurate hiding */
            --settings-panel-base-width: 350px;
            --settings-panel-padding: 20px;
            --settings-panel-actual-width: calc(var(--settings-panel-base-width) + (var(--settings-panel-padding) * 2));
            --settings-panel-base-width: 400px;
            --settings-panel-actual-width: 400px;
            --settings-panel-padding: 20px;

            /* Neomorphism specific variables */
            --neomorphic-bg: #1a1a2e;
            --neomorphic-light-shadow: rgba(255, 255, 255, 0.05);
            --neomorphic-dark-shadow: rgba(0, 0, 0, 0.4);
            --neomorphic-inset-light: rgba(255, 255, 255, 0.02);
            --neomorphic-inset-dark: rgba(0, 0, 0, 0.6);
            --blur-amount: 10px; /* For glassmorphism effect */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary); /* Use primary dark background */
            color: var(--text-primary); /* Light text for readability */
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: var(--glass-bg); /* Glass background for main container */
            border: 1px solid var(--glass-border); /* Subtle glass border */
            border-radius: var(--border-radius); /* Consistent rounded corners */
            box-shadow: 8px 8px 16px var(--shadow-dark), -8px -8px 16px var(--shadow-light); /* Neomorphic shadow */
            backdrop-filter: blur(var(--blur-amount)); /* Apply blur for glass effect */
            -webkit-backdrop-filter: blur(var(--blur-amount)); /* Safari support */
        }
        h1, h2, h3, h4 {
            color: var(--primary); /* Neon Cyan for headings */
            font-weight: 600;
            text-shadow: 0 0 5px rgba(0, 188, 212, 0.3); /* Subtle neon text shadow */
        }
        /* Custom scrollbar for code blocks */
        pre::-webkit-scrollbar {
            height: 8px;
            background-color: var(--bg-secondary);
            border-radius: 4px;
        }
        pre::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
        pre::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }

        /* Neomorphism for inner content blocks */
        .neomorphic-card {
            background: var(--neomorphic-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
            transition: all 0.3s ease-in-out;
            padding: 1rem;
        }

        .neomorphic-card:hover {
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary); /* Add glow on hover */
        }

        /* Inputs */
        .neomorphic-input {
            background-color: var(--neomorphic-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border-radius: 8px;
            box-shadow: inset 2px 2px 5px var(--neomorphic-inset-dark),
                        inset -2px -2px 5px var(--neomorphic-inset-light);
            transition: all 0.3s ease-in-out;
        }

        .neomorphic-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: inset 2px 2px 5px var(--neomorphic-inset-dark),
                        inset -2px -2px 5px var(--neomorphic-inset-light),
                        0 0 0 3px rgba(0, 188, 212, 0.3); /* Neon focus ring */
        }

        /* Buttons */
        .neomorphic-btn {
            background-color: var(--primary);
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease-in-out;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }

        .neomorphic-btn:hover {
            background-color: var(--primary); /* Keep primary color */
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary); /* Stronger neon glow */
            transform: translateY(-2px);
        }

        .neomorphic-btn-secondary {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-btn-secondary:hover {
            background-color: var(--bg-secondary);
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        var(--neon-glow-secondary); /* Magenta glow */
            color: var(--secondary);
        }

        .neomorphic-btn-destructive {
            background-color: var(--danger);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-btn-destructive:hover {
            background-color: var(--danger);
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        0 0 15px rgba(244, 67, 54, 0.6); /* Red glow */
        }

        .neomorphic-link-btn {
            color: var(--primary);
            background: none;
            box-shadow: none;
            text-shadow: none;
        }
        .neomorphic-link-btn:hover {
            color: var(--accent);
            text-shadow: var(--neon-glow-primary);
            transform: none;
            box-shadow: none;
        }

        .neomorphic-icon-btn {
            background-color: var(--neomorphic-bg);
            color: var(--primary);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow),
                        -4px -4px 8px var(--neomorphic-light-shadow);
            border-radius: 50%;
            padding: 0.75rem;
        }
        .neomorphic-icon-btn:hover {
            background-color: var(--neomorphic-bg);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary);
            color: var(--accent);
            transform: translateY(-2px);
        }

        /* Checkbox / Radio */
        .neomorphic-checkbox-radio {
            appearance: none;
            -webkit-appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 0.25rem; /* Square for checkbox */
            border: 1px solid rgba(255, 255, 255, 0.15);
            background-color: var(--neomorphic-bg);
            box-shadow: inset 2px 2px 4px var(--neomorphic-inset-dark),
                        inset -2px -2px 4px var(--neomorphic-inset-light);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            position: relative;
        }

        .neomorphic-checkbox-radio:checked {
            background-color: var(--primary);
            border-color: var(--primary);
            box-shadow: inset 2px 2px 4px var(--neomorphic-inset-dark),
                        inset -2px -2px 4px var(--neomorphic-inset-light),
                        0 0 8px rgba(0, 188, 212, 0.5); /* Neon glow on checked */
        }

        .neomorphic-checkbox-radio:focus {
            outline: none;
            box-shadow: inset 2px 2px 4px var(--neomorphic-inset-dark),
                        inset -2px -2px 4px var(--neomorphic-inset-light),
                        0 0 0 3px rgba(0, 188, 212, 0.3);
        }

        /* Custom checkmark for checkbox */
        .neomorphic-checkbox-radio[type="checkbox"]:checked::before {
            content: '\2713'; /* Unicode checkmark */
            display: block;
            color: white;
            font-size: 0.8em;
            text-align: center;
            line-height: 1.25rem;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Radio button specific styling */
        .neomorphic-checkbox-radio[type="radio"] {
            border-radius: 50%; /* Circular for radio */
        }

        .neomorphic-checkbox-radio[type="radio"]:checked::before {
            content: '';
            display: block;
            width: 0.5rem;
            height: 0.5rem;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Toggle Switch - Custom styling */
        .toggle-switch .slider {
            background-color: var(--neomorphic-bg);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: inset 2px 2px 4px var(--neomorphic-inset-dark),
                        inset -2px -2px 4px var(--neomorphic-inset-light);
        }

        .toggle-switch input:checked + .slider {
            background-color: var(--primary);
            box-shadow: inset 2px 2px 4px var(--neomorphic-inset-dark),
                        inset -2px -2px 4px var(--neomorphic-inset-light),
                        0 0 8px rgba(0, 188, 212, 0.5);
        }

        .toggle-switch .slider:before {
            background-color: var(--text-primary);
            box-shadow: 2px 2px 4px var(--neomorphic-dark-shadow);
        }

        /* Table Styling */
        .neomorphic-table {
            background: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-table thead {
            background-color: rgba(0, 0, 0, 0.3);
        }
        .neomorphic-table th {
            color: var(--accent);
            text-shadow: 0 0 3px rgba(255, 235, 59, 0.3);
        }
        .neomorphic-table tbody tr {
            background-color: var(--neomorphic-bg);
            transition: background-color 0.2s ease;
        }
        .neomorphic-table tbody tr:hover {
            background-color: rgba(0, 188, 212, 0.08); /* Subtle highlight on hover */
        }
        .neomorphic-table td {
            color: var(--text-secondary);
        }

        /* List Group */
        .neomorphic-list-group {
            background: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-list-item {
            color: var(--text-primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .neomorphic-list-item:last-child {
            border-bottom: none;
        }
        .neomorphic-list-item:hover {
            background-color: rgba(0, 188, 212, 0.08);
            color: var(--primary);
        }
        .neomorphic-list-item a {
            color: var(--primary);
        }
        .neomorphic-list-item a:hover {
            color: var(--accent);
            text-shadow: var(--neon-glow-primary);
        }

        /* Badges */
        .neomorphic-badge {
            background-color: var(--primary);
            color: white;
            box-shadow: 2px 2px 5px var(--neomorphic-dark-shadow);
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        }
        .neomorphic-badge.green { background-color: var(--success); }
        .neomorphic-badge.red { background-color: var(--danger); }
        .neomorphic-badge.yellow { background-color: var(--accent); color: var(--bg-primary); } /* Yellow badge with dark text */

        /* Progress Bar */
        .neomorphic-progress-track {
            background-color: var(--neomorphic-bg);
            box-shadow: inset 2px 2px 5px var(--neomorphic-inset-dark),
                        inset -2px -2px 5px var(--neomorphic-inset-light);
        }
        .neomorphic-progress-fill {
            background-color: var(--primary);
            box-shadow: 0 0 8px rgba(0, 188, 212, 0.5); /* Neon glow on fill */
        }

        /* Spinner */
        .neomorphic-spinner {
            border-color: var(--primary);
            border-top-color: transparent; /* Makes it spin */
            box-shadow: 0 0 10px var(--primary); /* Neon glow */
        }

        /* Accordion */
        .neomorphic-accordion-btn {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow),
                        -4px -4px 8px var(--neomorphic-light-shadow);
            color: var(--text-primary);
        }
        .neomorphic-accordion-btn:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary);
        }
        .neomorphic-accordion-btn svg {
            color: var(--accent);
        }
        .neomorphic-accordion-content {
            background-color: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            border-top: none;
            box-shadow: inset 2px 2px 5px var(--neomorphic-inset-dark),
                        inset -2px -2px 5px var(--neomorphic-inset-light);
            color: var(--text-secondary);
        }

        /* Carousel */
        .neomorphic-carousel-item img {
            border-radius: var(--border-radius);
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow);
        }
        .neomorphic-carousel-indicator {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .neomorphic-carousel-indicator[aria-current="true"] {
            background-color: var(--primary);
            box-shadow: 0 0 8px var(--primary);
        }
        .neomorphic-carousel-control {
            background-color: rgba(25, 25, 40, 0.6);
            border: 1px solid var(--glass-border);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow);
        }
        .neomorphic-carousel-control:hover {
            background-color: rgba(0, 188, 212, 0.2);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        var(--neon-glow-primary);
        }
        .neomorphic-carousel-control svg {
            color: var(--primary);
            text-shadow: 0 0 5px rgba(0, 188, 212, 0.5);
        }

        /* Tooltip */
        .neomorphic-tooltip-btn {
            background-color: var(--primary);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-tooltip-btn:hover {
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary);
        }
        .neomorphic-tooltip {
            background-color: rgba(25, 25, 40, 0.8);
            border: 1px solid var(--glass-border);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow);
            color: var(--text-primary);
            backdrop-filter: blur(5px);
        }

        /* Popover */
        .neomorphic-popover-btn {
            background-color: var(--primary);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-popover-btn:hover {
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary);
        }
        .neomorphic-popover {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow);
            color: var(--text-secondary);
        }
        .neomorphic-popover-header {
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--glass-border);
            color: var(--primary);
        }

        /* Alert */
        .neomorphic-alert {
            border: 1px solid var(--glass-border);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow),
                        -4px -4px 8px var(--neomorphic-light-shadow);
            color: var(--text-primary);
            background-color: rgba(25, 25, 40, 0.6);
        }
        .neomorphic-alert.blue { background-color: rgba(33, 150, 243, 0.2); border-color: var(--info); color: var(--info); }
        .neomorphic-alert.green { background-color: rgba(76, 175, 80, 0.2); border-color: var(--success); color: var(--success); }
        .neomorphic-alert.red { background-color: rgba(244, 67, 54, 0.2); border-color: var(--danger); color: var(--danger); }
        .neomorphic-alert span {
            font-weight: bold;
        }

        /* Modal */
        .neomorphic-modal-overlay {
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
        }
        .neomorphic-modal {
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5), var(--neon-glow-primary);
        }
        .neomorphic-modal-header {
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--glass-border);
            color: var(--primary);
        }
        .neomorphic-modal-body {
            color: var(--text-secondary);
        }
        .neomorphic-modal-footer {
            background-color: rgba(0, 0, 0, 0.2);
            border-top: 1px solid var(--glass-border);
        }
        .neomorphic-modal .btn-close-modal {
            color: var(--text-secondary);
        }
        .neomorphic-modal .btn-close-modal:hover {
            color: var(--danger);
        }

        /* Toast */
        .neomorphic-toast {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow),
                        -4px -4px 8px var(--neomorphic-light-shadow);
            color: var(--text-primary);
        }
        .neomorphic-toast .icon-bg-blue { background-color: rgba(33, 150, 243, 0.2); color: var(--info); }
        .neomorphic-toast .icon-bg-green { background-color: rgba(76, 175, 80, 0.2); color: var(--success); }

        /* Skeleton Loader */
        .neomorphic-skeleton {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow),
                        -4px -4px 8px var(--neomorphic-light-shadow);
        }
        .neomorphic-skeleton-line {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .neomorphic-skeleton-circle {
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* Navigation Bar */
        .neomorphic-navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 4px 8px var(--shadow-dark);
        }
        .neomorphic-navbar .logo {
            color: var(--accent);
            text-shadow: var(--neon-glow-primary);
        }
        .neomorphic-navbar a {
            color: var(--text-secondary);
        }
        .neomorphic-navbar a:hover {
            color: var(--primary);
            text-shadow: var(--neon-glow-primary);
        }

        /* Sidebar */
        .neomorphic-sidebar {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            border-right: 1px solid var(--glass-border);
            box-shadow: 4px 0 10px var(--shadow-dark);
        }
        .neomorphic-sidebar .nav-item {
            color: var(--text-primary);
        }
        .neomorphic-sidebar .nav-item:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
            box-shadow: 0 0 8px rgba(0, 188, 212, 0.3);
        }
        .neomorphic-sidebar .nav-item.active {
            background-color: rgba(0, 188, 212, 0.2);
            color: var(--primary);
            box-shadow: 0 0 12px rgba(0, 188, 212, 0.4);
        }

        /* Breadcrumbs */
        .neomorphic-breadcrumb a {
            color: var(--text-secondary);
        }
        .neomorphic-breadcrumb a:hover {
            color: var(--primary);
            text-shadow: 0 0 5px rgba(0, 188, 212, 0.3);
        }
        .neomorphic-breadcrumb span {
            color: var(--text-secondary);
        }
        .neomorphic-breadcrumb svg {
            color: rgba(255, 255, 255, 0.15);
        }

        /* Pagination */
        .neomorphic-pagination-item {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            box-shadow: 2px 2px 5px var(--neomorphic-dark-shadow);
        }
        .neomorphic-pagination-item:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow), var(--neon-glow-primary);
        }
        .neomorphic-pagination-item.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 0 10px var(--primary);
        }
        .neomorphic-pagination-item svg {
            color: var(--text-primary);
        }
        .neomorphic-pagination-item.active svg {
            color: white;
        }

        /* Tabs */
        .neomorphic-tabs-container {
            border-bottom: 1px solid var(--glass-border);
        }
        .neomorphic-tab-btn {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            border-bottom: none;
            color: var(--text-secondary);
            box-shadow: 2px 2px 5px var(--neomorphic-dark-shadow);
        }
        .neomorphic-tab-btn:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow), var(--neon-glow-primary);
        }
        .neomorphic-tab-btn.active {
            background-color: var(--bg-secondary);
            border-color: var(--primary);
            border-bottom-color: var(--bg-secondary); /* Hide bottom border to merge with section */
            color: var(--primary);
            font-weight: bold;
            box-shadow: 0 -2px 10px rgba(0, 188, 212, 0.3);
        }
        .neomorphic-tab-content {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            box-shadow: inset 2px 2px 5px var(--neomorphic-inset-dark),
                        inset -2px -2px 5px var(--neomorphic-inset-light);
            color: var(--text-secondary);
        }

        /* Dropdown Menu (Clickable) */
        .neomorphic-dropdown-btn {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            box-shadow: 4px 4px 8px var(--neomorphic-dark-shadow),
                        -4px -4px 8px var(--neomorphic-light-shadow);
        }
        .neomorphic-dropdown-btn:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow), var(--neon-glow-primary);
        }
        .neomorphic-dropdown-menu {
            background-color: var(--neomorphic-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow);
        }
        .neomorphic-dropdown-menu a {
            color: var(--text-primary);
        }
        .neomorphic-dropdown-menu a:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
        }

        /* Offcanvas/Drawer */
        .neomorphic-drawer-btn {
            background-color: var(--primary);
            box-shadow: 6px 6px 12px var(--neomorphic-dark-shadow),
                        -6px -6px 12px var(--neomorphic-light-shadow);
        }
        .neomorphic-drawer-btn:hover {
            box-shadow: 8px 8px 16px var(--neomorphic-dark-shadow),
                        -8px -8px 16px var(--neomorphic-light-shadow),
                        var(--neon-glow-primary);
        }
        .neomorphic-drawer {
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--glass-border);
            box-shadow: 4px 0 10px var(--shadow-dark);
        }
        .neomorphic-drawer h5 {
            color: var(--accent);
        }
        .neomorphic-drawer button {
            color: var(--text-secondary);
        }
        .neomorphic-drawer button:hover {
            color: var(--danger);
        }
        .neomorphic-drawer a {
            color: var(--text-primary);
        }
        .neomorphic-drawer a:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--primary);
        }

        /* Stepper/Wizard */
        .neomorphic-stepper-item {
            color: var(--text-secondary);
        }
        .neomorphic-stepper-item.active {
            color: var(--primary);
            text-shadow: 0 0 5px rgba(0, 188, 212, 0.3);
        }
        .neomorphic-stepper-item svg {
            color: var(--primary);
        }
        .neomorphic-stepper-item.active svg {
            color: var(--primary);
        }
        .neomorphic-stepper-item::after {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Rating/Stars */
        .neomorphic-star.filled {
            color: var(--accent);
            text-shadow: 0 0 8px rgba(255, 235, 59, 0.5);
        }
        .neomorphic-star.empty {
            color: rgba(255, 255, 255, 0.15);
        }
        .neomorphic-rating-text {
            color: var(--text-primary);
        }

        /* Tags/Pills */
        .neomorphic-tag {
            background-color: var(--secondary); /* Magenta accent */
            color: white;
            box-shadow: 2px 2px 5px var(--neomorphic-dark-shadow);
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        }
        .neomorphic-tag.purple { background-color: #9C27B0; } /* Example purple */
        .neomorphic-tag.pink { background-color: #E91E63; } /* Example pink (secondary) */
        .neomorphic-tag button {
            color: rgba(255, 255, 255, 0.7);
        }
        .neomorphic-tag button:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

    </style>
</head>
<body class="p-4 sm:p-6 md:p-8">
    <div class="container">
        <h1 class="text-4xl font-bold mb-8 text-center">Web App Design Components</h1>

        <!-- Section: Form Elements -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">1. Form Elements</h2>

            <!-- Input Field (Text) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.1 Input Field (Text)</h3>
                <p class="text-gray-400 mb-2">A basic single-line text input for user data.</p>
                <div class="mb-4">
                    <label for="textInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Text Input:</label>
                    <input type="text" id="textInput" placeholder="Enter text here" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="textInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Text Input:&lt;/label&gt;
&lt;input type="text" id="textInput" placeholder="Enter text here" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;</code></pre>
            </div>

            <!-- Input Field (Password) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.2 Input Field (Password)</h3>
                <p class="text-gray-400 mb-2">An input field where characters are masked for security.</p>
                <div class="mb-4">
                    <label for="passwordInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Password:</label>
                    <input type="password" id="passwordInput" placeholder="Enter password" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="passwordInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Password:&lt;/label&gt;
&lt;input type="password" id="passwordInput" placeholder="Enter password" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;</code></pre>
            </div>

            <!-- Input Field (Email) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.3 Input Field (Email)</h3>
                <p class="text-gray-400 mb-2">An input field specifically for email addresses, often with built-in validation.</p>
                <div class="mb-4">
                    <label for="emailInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Email:</label>
                    <input type="email" id="emailInput" placeholder="you@example.com" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="emailInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Email:&lt;/label&gt;
&lt;input type="email" id="emailInput" placeholder="you@example.com" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;</code></pre>
            </div>

            <!-- Textarea -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.4 Textarea</h3>
                <p class="text-gray-400 mb-2">A multi-line text input for longer user entries.</p>
                <div class="mb-4">
                    <label for="textarea" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Message:</label>
                    <textarea id="textarea" rows="4" placeholder="Your message here..." class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"></textarea>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="textarea" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Message:&lt;/label&gt;
&lt;textarea id="textarea" rows="4" placeholder="Your message here..." class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;&lt;/textarea&gt;</code></pre>
            </div>

            <!-- Checkbox -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.5 Checkbox</h3>
                <p class="text-gray-400 mb-2">Allows users to select one or more options from a set.</p>
                <div class="mb-4">
                    <div class="flex items-center">
                        <input id="checkbox1" type="checkbox" class="neomorphic-checkbox-radio">
                        <label for="checkbox1" class="ml-2 block text-sm text-[var(--text-primary)]">Option 1</label>
                    </div>
                    <div class="flex items-center mt-2">
                        <input id="checkbox2" type="checkbox" class="neomorphic-checkbox-radio">
                        <label for="checkbox2" class="ml-2 block text-sm text-[var(--text-primary)]">Option 2</label>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="flex items-center"&gt;
    &lt;input id="checkbox1" type="checkbox" class="neomorphic-checkbox-radio"&gt;
    &lt;label for="checkbox1" class="ml-2 block text-sm text-[var(--text-primary)]"&gt;Option 1&lt;/label&gt;
&lt;/div&gt;
&lt;div class="flex items-center mt-2"&gt;
    &lt;input id="checkbox2" type="checkbox" class="neomorphic-checkbox-radio"&gt;
    &lt;label for="checkbox2" class="ml-2 block text-sm text-[var(--text-primary)]"&gt;Option 2&lt;/label&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Radio Button -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.6 Radio Button</h3>
                <p class="text-gray-400 mb-2">Allows users to select only one option from a set.</p>
                <div class="mb-4">
                    <div class="flex items-center">
                        <input id="radio1" name="radioGroup" type="radio" class="neomorphic-checkbox-radio">
                        <label for="radio1" class="ml-2 block text-sm text-[var(--text-primary)]">Choice A</label>
                    </div>
                    <div class="flex items-center mt-2">
                        <input id="radio2" name="radioGroup" type="radio" class="neomorphic-checkbox-radio">
                        <label for="radio2" class="ml-2 block text-sm text-[var(--text-primary)]">Choice B</label>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="flex items-center"&gt;
    &lt;input id="radio1" name="radioGroup" type="radio" class="neomorphic-checkbox-radio"&gt;
    &lt;label for="radio1" class="ml-2 block text-sm text-[var(--text-primary)]"&gt;Choice A&lt;/label&gt;
&lt;/div&gt;
&lt;div class="flex items-center mt-2"&gt;
    &lt;input id="radio2" name="radioGroup" type="radio" class="neomorphic-checkbox-radio"&gt;
    &lt;label for="radio2" class="ml-2 block text-sm text-[var(--text-primary)]"&gt;Choice B&lt;/label&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Dropdown (Select) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.7 Dropdown (Select)</h3>
                <p class="text-gray-400 mb-2">A list of options from which the user can select one.</p>
                <div class="mb-4">
                    <label for="dropdown" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Select Option:</label>
                    <select id="dropdown" class="mt-1 block w-full pl-3 pr-10 py-2 text-base neomorphic-input sm:text-sm">
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </select>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="dropdown" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Select Option:&lt;/label&gt;
&lt;select id="dropdown" class="mt-1 block w-full pl-3 pr-10 py-2 text-base neomorphic-input sm:text-sm"&gt;
    &lt;option&gt;Option 1&lt;/option&gt;
    &lt;option&gt;Option 2&lt;/option&gt;
    &lt;option&gt;Option 3&lt;/option&gt;
&lt;/select&gt;</code></pre>
            </div>

            <!-- File Input -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.8 File Input</h3>
                <p class="text-gray-400 mb-2">Allows users to upload one or more files.</p>
                <div class="mb-4">
                    <label for="fileInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Upload File:</label>
                    <input type="file" id="fileInput" class="mt-1 block w-full text-sm text-[var(--text-secondary)]
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-[var(--primary)] file:text-white
                        hover:file:bg-[var(--accent)] hover:file:shadow-[var(--neon-glow-primary)]">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="fileInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Upload File:&lt;/label&gt;
&lt;input type="file" id="fileInput" class="mt-1 block w-full text-sm text-[var(--text-secondary)]
    file:mr-4 file:py-2 file:px-4
    file:rounded-full file:border-0
    file:text-sm file:font-semibold
    file:bg-[var(--primary)] file:text-white
    hover:file:bg-[var(--accent)] hover:file:shadow-[var(--neon-glow-primary)]"&gt;</code></pre>
            </div>

            <!-- Date Picker -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.9 Date Picker</h3>
                <p class="text-gray-400 mb-2">An input field with a calendar interface for selecting dates.</p>
                <div class="mb-4">
                    <label for="datePicker" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Select Date:</label>
                    <input type="date" id="datePicker" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="datePicker" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Select Date:&lt;/label&gt;
&lt;input type="date" id="datePicker" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;</code></pre>
            </div>

            <!-- Time Picker -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.10 Time Picker</h3>
                <p class="text-gray-400 mb-2">An input field for selecting time.</p>
                <div class="mb-4">
                    <label for="timePicker" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Select Time:</label>
                    <input type="time" id="timePicker" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="timePicker" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Select Time:&lt;/label&gt;
&lt;input type="time" id="timePicker" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;</code></pre>
            </div>

            <!-- Range Slider -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.11 Range Slider</h3>
                <p class="text-gray-400 mb-2">Allows users to select a value within a specified range.</p>
                <div class="mb-4">
                    <label for="rangeSlider" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Volume:</label>
                    <input type="range" id="rangeSlider" min="0" max="100" value="50" class="mt-1 block w-full h-2 bg-[var(--neomorphic-bg)] rounded-lg appearance-none cursor-pointer accent-[var(--primary)]
                        shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)]">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="rangeSlider" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Volume:&lt;/label&gt;
&lt;input type="range" id="rangeSlider" min="0" max="100" value="50" class="mt-1 block w-full h-2 bg-[var(--neomorphic-bg)] rounded-lg appearance-none cursor-pointer accent-[var(--primary)]
    shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)]"&gt;</code></pre>
            </div>

            <!-- Color Picker -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.12 Color Picker</h3>
                <p class="text-gray-400 mb-2">An input field for selecting a color.</p>
                <div class="mb-4">
                    <label for="colorPicker" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Select Color:</label>
                    <input type="color" id="colorPicker" value="#00bcd4" class="mt-1 block w-24 h-10 border border-[var(--glass-border)] rounded-md shadow-sm cursor-pointer neomorphic-input">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="colorPicker" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Select Color:&lt;/label&gt;
&lt;input type="color" id="colorPicker" value="#00bcd4" class="mt-1 block w-24 h-10 border border-[var(--glass-border)] rounded-md shadow-sm cursor-pointer neomorphic-input"&gt;</code></pre>
            </div>

            <!-- Number Input -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.13 Number Input</h3>
                <p class="text-gray-400 mb-2">An input field specifically for numerical values, often with step controls.</p>
                <div class="mb-4">
                    <label for="numberInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1">Quantity:</label>
                    <input type="number" id="numberInput" min="0" max="100" value="1" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm">
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="numberInput" class="block text-sm font-medium text-[var(--text-primary)] mb-1"&gt;Quantity:&lt;/label&gt;
&lt;input type="number" id="numberInput" min="0" max="100" value="1" class="mt-1 block w-full px-3 py-2 neomorphic-input sm:text-sm"&gt;</code></pre>
            </div>

            <!-- Toggle Switch (Custom) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.14 Toggle Switch</h3>
                <p class="text-gray-400 mb-2">A binary switch for turning an option on or off.</p>
                <div class="mb-4 flex items-center">
                    <label for="toggleSwitch" class="flex items-center cursor-pointer toggle-switch">
                        <!-- Hidden checkbox -->
                        <input type="checkbox" id="toggleSwitch" class="sr-only peer">
                        <!-- Track -->
                        <div class="relative w-11 h-6 bg-[var(--neomorphic-bg)] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--primary)] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-[var(--glass-border)] after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]
                            shadow-[inset_2px_2px_4px_var(--neomorphic-inset-dark),_inset_-2px_-2px_4px_var(--neomorphic-inset-light)]"></div>
                        <span class="ml-3 text-sm font-medium text-[var(--text-primary)]">Enable Feature</span>
                    </label>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="toggleSwitch" class="flex items-center cursor-pointer toggle-switch"&gt;
    &lt;!-- Hidden checkbox --&gt;
    &lt;input type="checkbox" id="toggleSwitch" class="sr-only peer"&gt;
    &lt;!-- Track --&gt;
    &lt;div class="relative w-11 h-6 bg-[var(--neomorphic-bg)] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--primary)] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-[var(--glass-border)] after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]
        shadow-[inset_2px_2px_4px_var(--neomorphic-inset-dark),_inset_-2px_-2px_4px_var(--neomorphic-inset-light)]"&gt;&lt;/div&gt;
    &lt;span class="ml-3 text-sm font-medium text-[var(--text-primary)]"&gt;Enable Feature&lt;/span&gt;
&lt;/label&gt;</code></pre>
            </div>

            <!-- Search Input -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">1.15 Search Input</h3>
                <p class="text-gray-400 mb-2">An input field specifically for search queries, often with a search icon.</p>
                <div class="mb-4">
                    <label for="searchInput" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <!-- Search icon (example using SVG) -->
                            <svg class="h-5 w-5 text-[var(--text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="search" id="searchInput" placeholder="Search..." class="block w-full pl-10 pr-3 py-2 neomorphic-input leading-5 placeholder-[var(--text-secondary)] sm:text-sm">
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;label for="searchInput" class="sr-only"&gt;Search&lt;/label&gt;
&lt;div class="relative"&gt;
    &lt;div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"&gt;
        &lt;!-- Search icon (example using SVG) --&gt;
        &lt;svg class="h-5 w-5 text-[var(--text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"&gt;
            &lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /&gt;
        &lt;/svg&gt;
    &lt;/div&gt;
    &lt;input type="search" id="searchInput" placeholder="Search..." class="block w-full pl-10 pr-3 py-2 neomorphic-input leading-5 placeholder-[var(--text-secondary)] sm:text-sm"&gt;
&lt;/div&gt;</code></pre>
            </div>

        </section>

        <!-- Section: Buttons -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">2. Buttons</h2>

            <!-- Primary Button -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">2.1 Primary Button</h3>
                <p class="text-gray-400 mb-2">The most prominent action button, typically for main actions.</p>
                <div class="mb-4">
                    <button class="px-6 py-3 neomorphic-btn">Primary Action</button>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button class="px-6 py-3 neomorphic-btn"&gt;Primary Action&lt;/button&gt;</code></pre>
            </div>

            <!-- Secondary Button -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">2.2 Secondary Button</h3>
                <p class="text-gray-400 mb-2">A less prominent button for alternative actions.</p>
                <div class="mb-4">
                    <button class="px-6 py-3 neomorphic-btn-secondary">Secondary Action</button>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button class="px-6 py-3 neomorphic-btn-secondary"&gt;Secondary Action&lt;/button&gt;</code></pre>
            </div>

            <!-- Destructive Button -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">2.3 Destructive Button</h3>
                <p class="text-gray-400 mb-2">A button for actions that have irreversible consequences (e.g., delete).</p>
                <div class="mb-4">
                    <button class="px-6 py-3 neomorphic-btn-destructive">Delete Item</button>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button class="px-6 py-3 neomorphic-btn-destructive"&gt;Delete Item&lt;/button&gt;</code></pre>
            </div>

            <!-- Link Button -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">2.4 Link Button</h3>
                <p class="text-gray-400 mb-2">A button styled like a text link, for navigating or less critical actions.</p>
                <div class="mb-4">
                    <button class="neomorphic-link-btn font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2 rounded-md">Learn More</button>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button class="neomorphic-link-btn font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2 rounded-md"&gt;Learn More&lt;/button&gt;</code></pre>
            </div>

            <!-- Icon Button -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">2.5 Icon Button</h3>
                <p class="text-gray-400 mb-2">A button primarily represented by an icon.</p>
                <div class="mb-4">
                    <button class="neomorphic-icon-btn">
                        <!-- Example: Plus icon (SVG) -->
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0H6" />
                        </svg>
                    </button>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button class="neomorphic-icon-btn"&gt;
    &lt;!-- Example: Plus icon (SVG) --&gt;
    &lt;svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"&gt;
        &lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0H6" /&gt;
    &lt;/svg&gt;
&lt;/button&gt;</code></pre>
            </div>

            <!-- Button Group -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">2.6 Button Group</h3>
                <p class="text-gray-400 mb-2">Multiple buttons grouped together for related actions.</p>
                <div class="mb-4">
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <button type="button" class="px-4 py-2 text-sm font-medium text-[var(--primary)] bg-[var(--neomorphic-bg)] border border-[var(--glass-border)] rounded-l-lg hover:bg-[rgba(0,188,212,0.1)] focus:z-10 focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]
                            shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)] hover:shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light),_var(--neon-glow-primary)]">
                            Left
                        </button>
                        <button type="button" class="px-4 py-2 text-sm font-medium text-[var(--primary)] bg-[var(--neomorphic-bg)] border-t border-b border-[var(--glass-border)] hover:bg-[rgba(0,188,212,0.1)] focus:z-10 focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]
                            shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)] hover:shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light),_var(--neon-glow-primary)]">
                            Middle
                        </button>
                        <button type="button" class="px-4 py-2 text-sm font-medium text-[var(--primary)] bg-[var(--neomorphic-bg)] border border-[var(--glass-border)] rounded-r-lg hover:bg-[rgba(0,188,212,0.1)] focus:z-10 focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]
                            shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)] hover:shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light),_var(--neon-glow-primary)]">
                            Right
                        </button>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="inline-flex rounded-md shadow-sm" role="group"&gt;
    &lt;button type="button" class="px-4 py-2 text-sm font-medium text-[var(--primary)] bg-[var(--neomorphic-bg)] border border-[var(--glass-border)] rounded-l-lg hover:bg-[rgba(0,188,212,0.1)] focus:z-10 focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]
        shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)] hover:shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light),_var(--neon-glow-primary)]"&gt;
        Left
    &lt;/button&gt;
    &lt;button type="button" class="px-4 py-2 text-sm font-medium text-[var(--primary)] bg-[var(--neomorphic-bg)] border-t border-b border-[var(--glass-border)] hover:bg-[rgba(0,188,212,0.1)] focus:z-10 focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]
        shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)] hover:shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light),_var(--neon-glow-primary)]"&gt;
        Middle
    &lt;/button&gt;
    &lt;button type="button" class="px-4 py-2 text-sm font-medium text-[var(--primary)] bg-[var(--neomorphic-bg)] border border-[var(--glass-border)] rounded-r-lg hover:bg-[rgba(0,188,212,0.1)] focus:z-10 focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]
        shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light)] hover:shadow-[inset_2px_2px_5px_var(--neomorphic-inset-dark),_inset_-2px_-2px_5px_var(--neomorphic-inset-light),_var(--neon-glow-primary)]"&gt;
        Right
    &lt;/button&gt;
&lt;/div&gt;</code></pre>
            </div>
        </section>

        <!-- Section: Navigation -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">3. Navigation</h2>

            <!-- Navigation Bar (Simple) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">3.1 Navigation Bar (Simple)</h3>
                <p class="text-gray-400 mb-2">A horizontal bar for primary navigation links.</p>
                <div class="mb-4 bg-[var(--glass-bg)] p-4 rounded-lg neomorphic-navbar">
                    <nav class="flex justify-between items-center">
                        <div class="text-[var(--accent)] font-bold text-xl logo">App Logo</div>
                        <ul class="flex space-x-6">
                            <li><a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out">Home</a></li>
                            <li><a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out">Dashboard</a></li>
                            <li><a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out">Settings</a></li>
                            <li><a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out">Profile</a></li>
                        </ul>
                    </nav>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;nav class="flex justify-between items-center bg-[var(--glass-bg)] p-4 rounded-lg neomorphic-navbar"&gt;
    &lt;div class="text-[var(--accent)] font-bold text-xl logo"&gt;App Logo&lt;/div&gt;
    &lt;ul class="flex space-x-6"&gt;
        &lt;li&gt;&lt;a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out"&gt;Home&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out"&gt;Dashboard&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out"&gt;Settings&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#" class="text-[var(--text-secondary)] hover:text-[var(--primary)] transition duration-150 ease-in-out"&gt;Profile&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
&lt;/nav&gt;</code></pre>
            </div>

            <!-- Sidebar Navigation -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">3.2 Sidebar Navigation</h3>
                <p class="text-gray-400 mb-2">A vertical navigation menu, often used for dashboards or complex apps.</p>
                <div class="mb-4 bg-[var(--glass-bg)] w-64 h-64 p-4 rounded-lg flex flex-col justify-between neomorphic-sidebar">
                    <nav>
                        <ul class="space-y-2">
                            <li><a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out">Dashboard</a></li>
                            <li><a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out">Users</a></li>
                            <li><a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out">Products</a></li>
                            <li><a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out">Reports</a></li>
                        </ul>
                    </nav>
                    <div class="mt-auto">
                        <a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out">Logout</a>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="bg-[var(--glass-bg)] w-64 h-64 p-4 rounded-lg flex flex-col justify-between neomorphic-sidebar"&gt;
    &lt;nav&gt;
        &lt;ul class="space-y-2"&gt;
            &lt;li&gt;&lt;a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out"&gt;Dashboard&lt;/a&gt;&lt;/li&gt;
            &lt;li&gt;&lt;a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out"&gt;Users&lt;/a&gt;&lt;/li&gt;
            &lt;li&gt;&lt;a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out"&gt;Products&lt;/a&gt;&lt;/li&gt;
            &lt;li&gt;&lt;a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out"&gt;Reports&lt;/a&gt;&lt;/li&gt;
        &lt;/ul&gt;
    &lt;/nav&gt;
    &lt;div class="mt-auto"&gt;
        &lt;a href="#" class="block px-4 py-2 text-[var(--text-primary)] hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)] rounded-md transition duration-150 ease-in-out"&gt;Logout&lt;/a&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Breadcrumbs -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">3.3 Breadcrumbs</h3>
                <p class="text-gray-400 mb-2">Indicates the current page's location within a hierarchical structure.</p>
                <div class="mb-4">
                    <nav class="flex neomorphic-breadcrumb" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="#" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--primary)] inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-[rgba(255,255,255,0.15)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                                    Home
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-[rgba(255,255,255,0.15)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    <a href="#" class="ml-1 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--primary)] md:ml-2">Products</a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-[rgba(255,255,255,0.15)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    <span class="ml-1 text-sm font-medium text-[var(--text-secondary)] md:ml-2">Current Product</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;nav class="flex neomorphic-breadcrumb" aria-label="Breadcrumb"&gt;
    &lt;ol class="inline-flex items-center space-x-1 md:space-x-3"&gt;
        &lt;li class="inline-flex items-center"&gt;
            &lt;a href="#" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--primary)] inline-flex items-center"&gt;
                &lt;svg class="w-4 h-4 mr-2 text-[rgba(255,255,255,0.15)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"&gt;&lt;/path&gt;&lt;/svg&gt;
                Home
            &lt;/a&gt;
        &lt;/li&gt;
        &lt;li&gt;
            &lt;div class="flex items-center"&gt;
                &lt;svg class="w-6 h-6 text-[rgba(255,255,255,0.15)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
                &lt;a href="#" class="ml-1 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--primary)] md:ml-2"&gt;Products&lt;/a&gt;
            &lt;/div&gt;
        &lt;/li&gt;
        &lt;li aria-current="page"&gt;
            &lt;div class="flex items-center"&gt;
                &lt;svg class="w-6 h-6 text-[rgba(255,255,255,0.15)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
                &lt;span class="ml-1 text-sm font-medium text-[var(--text-secondary)] md:ml-2"&gt;Current Product&lt;/span&gt;
            &lt;/div&gt;
        &lt;/li&gt;
    &lt;/ol&gt;
&lt;/nav&gt;</code></pre>
            </div>

            <!-- Pagination -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">3.4 Pagination</h3>
                <p class="text-gray-400 mb-2">Allows users to navigate through multiple pages of content.</p>
                <div class="mb-4">
                    <nav class="flex justify-center" aria-label="Pagination">
                        <ul class="inline-flex items-center -space-x-px">
                            <li>
                                <a href="#" class="block px-3 py-2 ml-0 leading-tight neomorphic-pagination-item rounded-l-lg">
                                    <span class="sr-only">Previous</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="px-3 py-2 leading-tight neomorphic-pagination-item">1</a>
                            </li>
                            <li>
                                <a href="#" aria-current="page" class="px-3 py-2 neomorphic-pagination-item active">2</a>
                            </li>
                            <li>
                                <a href="#" class="px-3 py-2 leading-tight neomorphic-pagination-item">3</a>
                            </li>
                            <li>
                                <a href="#" class="block px-3 py-2 leading-tight neomorphic-pagination-item rounded-r-lg">
                                    <span class="sr-only">Next</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;nav class="flex justify-center" aria-label="Pagination"&gt;
    &lt;ul class="inline-flex items-center -space-x-px"&gt;
        &lt;li&gt;
            &lt;a href="#" class="block px-3 py-2 ml-0 leading-tight neomorphic-pagination-item rounded-l-lg"&gt;
                &lt;span class="sr-only"&gt;Previous&lt;/span&gt;
                &lt;svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
            &lt;/a&gt;
        &lt;/li&gt;
        &lt;li&gt;
            &lt;a href="#" class="px-3 py-2 leading-tight neomorphic-pagination-item"&gt;1&lt;/a&gt;
        &lt;/li&gt;
        &lt;li&gt;
            &lt;a href="#" aria-current="page" class="px-3 py-2 neomorphic-pagination-item active"&gt;2&lt;/a&gt;
        &lt;/li&gt;
        &lt;li&gt;
            &lt;a href="#" class="px-3 py-2 leading-tight neomorphic-pagination-item"&gt;3&lt;/a&gt;
        &lt;/li&gt;
        &lt;li&gt;
            &lt;a href="#" class="block px-3 py-2 leading-tight neomorphic-pagination-item rounded-r-lg"&gt;
                &lt;span class="sr-only"&gt;Next&lt;/span&gt;
                &lt;svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
            &lt;/a&gt;
        &lt;/li&gt;
    &lt;/ul&gt;
&lt;/nav&gt;</code></pre>
            </div>

            <!-- Tabs -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">3.5 Tabs</h3>
                <p class="text-gray-400 mb-2">Organizes content into multiple sections, with only one section visible at a time.</p>
                <div class="mb-4">
                    <div class="border-b border-[var(--glass-border)] neomorphic-tabs-container">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[var(--primary)] hover:border-[var(--primary)] active:text-[var(--primary)] active:border-[var(--primary)] neomorphic-tab-btn" id="profile-tab" data-tabs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile</button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[var(--primary)] hover:border-[var(--primary)] neomorphic-tab-btn" id="dashboard-tab" data-tabs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false">Dashboard</button>
                            </li>
                            <li role="presentation">
                                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[var(--primary)] hover:border-[var(--primary)] neomorphic-tab-btn" id="settings-tab" data-tabs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Settings</button>
                            </li>
                        </ul>
                    </div>
                    <div id="myTabContent" class="mt-4">
                        <div class="p-4 rounded-lg neomorphic-tab-content" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <p class="text-sm text-[var(--text-secondary)]">This is the content for the Profile tab.</p>
                        </div>
                        <div class="hidden p-4 rounded-lg neomorphic-tab-content" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                            <p class="text-sm text-[var(--text-secondary)]">This is the content for the Dashboard tab.</p>
                        </div>
                        <div class="hidden p-4 rounded-lg neomorphic-tab-content" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                            <p class="text-sm text-[var(--text-secondary)]">This is the content for the Settings tab.</p>
                        </div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="border-b border-[var(--glass-border)] neomorphic-tabs-container"&gt;
    &lt;ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist"&gt;
        &lt;li class="mr-2" role="presentation"&gt;
            &lt;button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[var(--primary)] hover:border-[var(--primary)] active:text-[var(--primary)] active:border-[var(--primary)] neomorphic-tab-btn" id="profile-tab" data-tabs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false"&gt;Profile&lt;/button&gt;
        &lt;/li&gt;
        &lt;li class="mr-2" role="presentation"&gt;
            &lt;button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[var(--primary)] hover:border-[var(--primary)] neomorphic-tab-btn" id="dashboard-tab" data-tabs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false"&gt;Dashboard&lt;/button&gt;
        &lt;/li&gt;
        &lt;li role="presentation"&gt;
            &lt;button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[var(--primary)] hover:border-[var(--primary)] neomorphic-tab-btn" id="settings-tab" data-tabs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false"&gt;Settings&lt;/button&gt;
        &lt;/li&gt;
    &lt;/ul&gt;
&lt;/div&gt;
&lt;div id="myTabContent" class="mt-4"&gt;
    &lt;div class="p-4 rounded-lg neomorphic-tab-content" id="profile" role="tabpanel" aria-labelledby="profile-tab"&gt;
        &lt;p class="text-sm text-[var(--text-secondary)]"&gt;This is the content for the Profile tab.&lt;/p&gt;
    &lt;/div&gt;
    &lt;div class="hidden p-4 rounded-lg neomorphic-tab-content" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab"&gt;
        &lt;p class="text-sm text-[var(--text-secondary)]"&gt;This is the content for the Dashboard tab.&lt;/p&gt;
    &lt;/div&gt;
    &lt;div class="hidden p-4 rounded-lg neomorphic-tab-content" id="settings" role="tabpanel" aria-labelledby="settings-tab"&gt;
        &lt;p class="text-sm text-[var(--text-secondary)]"&gt;This is the content for the Settings tab.&lt;/p&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;!-- Note: Tabs require JavaScript for functionality. --&gt;</code></pre>
            </div>
        </section>

        <!-- Section: Data Display -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">4. Data Display</h2>

            <!-- Card -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.1 Card</h3>
                <p class="text-gray-400 mb-2">A flexible container for grouping related content.</p>
                <div class="mb-4">
                    <div class="max-w-sm neomorphic-card p-6">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-[var(--text-primary)]">Card Title</h5>
                        <p class="font-normal text-[var(--text-secondary)] mb-3">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p>
                        <a href="#" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center neomorphic-btn">
                            Read more
                            <svg class="ml-2 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </a>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="max-w-sm neomorphic-card p-6"&gt;
    &lt;h5 class="mb-2 text-2xl font-bold tracking-tight text-[var(--text-primary)]"&gt;Card Title&lt;/h5&gt;
    &lt;p class="font-normal text-[var(--text-secondary)] mb-3"&gt;Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.&lt;/p&gt;
    &lt;a href="#" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center neomorphic-btn"&gt;
        Read more
        &lt;svg class="ml-2 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/a&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Table -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.2 Table</h3>
                <p class="text-gray-400 mb-2">Displays data in rows and columns.</p>
                <div class="mb-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[var(--glass-border)] rounded-lg overflow-hidden neomorphic-table">
                        <thead class="bg-[rgba(0,0,0,0.3)]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider">Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--glass-border)]">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]">John Doe</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">Software Engineer</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full neomorphic-badge green">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">Member</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]">Jane Smith</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">UI/UX Designer</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full neomorphic-badge yellow">Pending</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">Admin</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;table class="min-w-full divide-y divide-[var(--glass-border)] rounded-lg overflow-hidden neomorphic-table"&gt;
    &lt;thead class="bg-[rgba(0,0,0,0.3)]"&gt;
        &lt;tr&gt;
            &lt;th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider"&gt;Name&lt;/th&gt;
            &lt;th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider"&gt;Title&lt;/th&gt;
            &lt;th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider"&gt;Status&lt;/th&gt;
            &lt;th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[var(--accent)] uppercase tracking-wider"&gt;Role&lt;/th&gt;
        &lt;/tr&gt;
    &lt;/thead&gt;
    &lt;tbody class="divide-y divide-[var(--glass-border)]"&gt;
        &lt;tr&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]"&gt;John Doe&lt;/td&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]"&gt;Software Engineer&lt;/td&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]"&gt;
                &lt;span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full neomorphic-badge green"&gt;Active&lt;/span&gt;
            &lt;/td&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]"&gt;Member&lt;/td&gt;
        &lt;/tr&gt;
        &lt;tr&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]"&gt;Jane Smith&lt;/td&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]"&gt;UI/UX Designer&lt;/td&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]"&gt;
                &lt;span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full neomorphic-badge yellow"&gt;Pending&lt;/span&gt;
            &lt;/td&gt;
            &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]"&gt;Admin&lt;/td&gt;
        &lt;/tr&gt;
    &lt;/tbody&gt;
&lt;/table&gt;</code></pre>
            </div>

            <!-- List Group -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.3 List Group</h3>
                <p class="text-gray-400 mb-2">A series of content items, often with links or actions, displayed vertically.</p>
                <div class="mb-4">
                    <ul class="divide-y divide-[var(--glass-border)] neomorphic-list-group">
                        <li class="px-4 py-3 sm:px-6 cursor-pointer neomorphic-list-item">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-[var(--text-primary)]">List Item 1</p>
                                <a href="#" class="text-sm text-[var(--primary)] hover:text-[var(--accent)]">View</a>
                            </div>
                        </li>
                        <li class="px-4 py-3 sm:px-6 cursor-pointer neomorphic-list-item">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-[var(--text-primary)]">List Item 2</p>
                                <a href="#" class="text-sm text-[var(--primary)] hover:text-[var(--accent)]">View</a>
                            </div>
                        </li>
                        <li class="px-4 py-3 sm:px-6 cursor-pointer neomorphic-list-item">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-[var(--text-primary)]">List Item 3</p>
                                <a href="#" class="text-sm text-[var(--primary)] hover:text-[var(--accent)]">View</a>
                            </div>
                        </li>
                    </ul>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;ul class="divide-y divide-[var(--glass-border)] neomorphic-list-group"&gt;
    &lt;li class="px-4 py-3 sm:px-6 cursor-pointer neomorphic-list-item"&gt;
        &lt;div class="flex items-center justify-between"&gt;
            &lt;p class="text-sm font-medium text-[var(--text-primary)]"&gt;List Item 1&lt;/p&gt;
            &lt;a href="#" class="text-sm text-[var(--primary)] hover:text-[var(--accent)]"&gt;View&lt;/a&gt;
        &lt;/div&gt;
    &lt;/li&gt;
    &lt;li class="px-4 py-3 sm:px-6 cursor-pointer neomorphic-list-item"&gt;
        &lt;div class="flex items-center justify-between"&gt;
            &lt;p class="text-sm font-medium text-[var(--text-primary)]"&gt;List Item 2&lt;/p&gt;
            &lt;a href="#" class="text-sm text-[var(--primary)] hover:text-[var(--accent)]"&gt;View&lt;/a&gt;
        &lt;/div&gt;
    &lt;/li&gt;
    &lt;li class="px-4 py-3 sm:px-6 cursor-pointer neomorphic-list-item"&gt;
        &lt;div class="flex items-center justify-between"&gt;
            &lt;p class="text-sm font-medium text-[var(--text-primary)]"&gt;List Item 3&lt;/p&gt;
            &lt;a href="#" class="text-sm text-[var(--primary)] hover:text-[var(--accent)]"&gt;View&lt;/a&gt;
        &lt;/div&gt;
    &lt;/li&gt;
&lt;/ul&gt;</code></pre>
            </div>

            <!-- Avatar -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.4 Avatar</h3>
                <p class="text-gray-400 mb-2">Represents a user or entity, typically with an image or initials.</p>
                <div class="mb-4 flex items-center space-x-4">
                    <img class="h-10 w-10 rounded-full border border-[var(--glass-border)] shadow-[2px_2px_5px_var(--neomorphic-dark-shadow)]" src="https://placehold.co/40x40/0d0d1a/00bcd4?text=JD" alt="User Avatar">
                    <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-[var(--neomorphic-bg)] rounded-full border border-[var(--glass-border)] shadow-[2px_2px_5px_var(--neomorphic-dark-shadow)]">
                        <span class="font-medium text-[var(--primary)]">TS</span>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;!-- Image Avatar --&gt;
&lt;img class="h-10 w-10 rounded-full border border-[var(--glass-border)] shadow-[2px_2px_5px_var(--neomorphic-dark-shadow)]" src="https://placehold.co/40x40/0d0d1a/00bcd4?text=JD" alt="User Avatar"&gt;

&lt;!-- Initials Avatar --&gt;
&lt;div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-[var(--neomorphic-bg)] rounded-full border border-[var(--glass-border)] shadow-[2px_2px_5px_var(--neomorphic-dark-shadow)]"&gt;
    &lt;span class="font-medium text-[var(--primary)]"&gt;TS&lt;/span&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Badge -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.5 Badge</h3>
                <p class="text-gray-400 mb-2">Small, non-interactive labels used for status, counts, or categories.</p>
                <div class="mb-4 flex space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium neomorphic-badge">New</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium neomorphic-badge green">Active</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium neomorphic-badge red">Error</span>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium neomorphic-badge"&gt;New&lt;/span&gt;
&lt;span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium neomorphic-badge green"&gt;Active&lt;/span&gt;
&lt;span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium neomorphic-badge red"&gt;Error&lt;/span&gt;</code></pre>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.6 Progress Bar</h3>
                <p class="text-gray-400 mb-2">Visually indicates the progress of an operation.</p>
                <div class="mb-4">
                    <div class="w-full h-2.5 rounded-full neomorphic-progress-track">
                        <div class="h-2.5 rounded-full neomorphic-progress-fill" style="width: 45%"></div>
                    </div>
                    <p class="text-sm text-[var(--text-secondary)] mt-1">45% Complete</p>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="w-full h-2.5 rounded-full neomorphic-progress-track"&gt;
    &lt;div class="h-2.5 rounded-full neomorphic-progress-fill" style="width: 45%"&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;p class="text-sm text-[var(--text-secondary)] mt-1"&gt;45% Complete&lt;/p&gt;</code></pre>
            </div>

            <!-- Spinner/Loader -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.7 Spinner/Loader</h3>
                <p class="text-gray-400 mb-2">Indicates that content is loading or an action is in progress.</p>
                <div class="mb-4 flex justify-center items-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 neomorphic-spinner"></div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 neomorphic-spinner"&gt;&lt;/div&gt;
&lt;!-- For the spinner animation, you'd typically need CSS:
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
Tailwind's animate-spin utility handles this.
--&gt;</code></pre>
            </div>

            <!-- Accordion -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.8 Accordion</h3>
                <p class="text-gray-400 mb-2">Allows users to expand and collapse sections of content.</p>
                <div class="mb-4">
                    <div id="accordion-collapse" data-accordion="collapse">
                        <h2 id="accordion-collapse-heading-1">
                            <button type="button" class="flex items-center justify-between w-full p-5 font-medium text-left rounded-t-xl focus:ring-4 focus:ring-[var(--primary)] neomorphic-accordion-btn" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1">
                                <span>What is Lorem Ipsum?</span>
                                <svg data-accordion-icon class="w-6 h-6 rotate-180 shrink-0 text-[var(--accent)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </h2>
                        <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
                            <div class="p-5 neomorphic-accordion-content">
                                <p class="mb-2 text-[var(--text-secondary)]">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            </div>
                        </div>
                        <h2 id="accordion-collapse-heading-2">
                            <button type="button" class="flex items-center justify-between w-full p-5 font-medium text-left focus:ring-4 focus:ring-[var(--primary)] neomorphic-accordion-btn" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                                <span>Where does it come from?</span>
                                <svg data-accordion-icon class="w-6 h-6 shrink-0 text-[var(--accent)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </h2>
                        <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
                            <div class="p-5 neomorphic-accordion-content">
                                <p class="mb-2 text-[var(--text-secondary)]">Contrary to popular belief, Lorem Ipsum is not simply random text.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div id="accordion-collapse" data-accordion="collapse"&gt;
    &lt;h2 id="accordion-collapse-heading-1"&gt;
        &lt;button type="button" class="flex items-center justify-between w-full p-5 font-medium text-left rounded-t-xl focus:ring-4 focus:ring-[var(--primary)] neomorphic-accordion-btn" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1"&gt;
            &lt;span&gt;What is Lorem Ipsum?&lt;/span&gt;
            &lt;svg data-accordion-icon class="w-6 h-6 rotate-180 shrink-0 text-[var(--accent)]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
        &lt;/button&gt;
    &lt;/h2&gt;
    &lt;div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1"&gt;
        &lt;div class="p-5 neomorphic-accordion-content"&gt;
            &lt;p class="mb-2 text-[var(--text-secondary)]"&gt;Lorem Ipsum is simply dummy text of the printing and typesetting industry.&lt;/p&gt;
        &lt;/div&gt;
    &lt;/div&gt;
    &lt;!-- More accordion items... --&gt;
&lt;/div&gt;
&lt;!-- Note: Accordions require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Carousel/Image Slider -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.9 Carousel/Image Slider</h3>
                <p class="text-gray-400 mb-2">Displays multiple images or content blocks in a rotating sequence.</p>
                <div class="mb-4">
                    <div id="default-carousel" class="relative w-full" data-carousel="static">
                        <!-- Carousel wrapper -->
                        <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                            <!-- Item 1 -->
                            <div class="hidden duration-700 ease-in-out neomorphic-carousel-item" data-carousel-item>
                                <img src="https://placehold.co/800x400/0d0d1a/00bcd4?text=Image+1" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Slide 1">
                            </div>
                            <!-- Item 2 -->
                            <div class="hidden duration-700 ease-in-out neomorphic-carousel-item" data-carousel-item>
                                <img src="https://placehold.co/800x400/0d0d1a/e91e63?text=Image+2" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Slide 2">
                            </div>
                            <!-- Item 3 -->
                            <div class="hidden duration-700 ease-in-out neomorphic-carousel-item" data-carousel-item>
                                <img src="https://placehold.co/800x400/0d0d1a/ffeb3b?text=Image+3" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Slide 3">
                            </div>
                        </div>
                        <!-- Slider indicators -->
                        <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2">
                            <button type="button" class="w-3 h-3 rounded-full neomorphic-carousel-indicator" aria-current="true" aria-label="Slide 1" data-carousel-slide-to="0"></button>
                            <button type="button" class="w-3 h-3 rounded-full neomorphic-carousel-indicator" aria-current="false" aria-label="Slide 2" data-carousel-slide-to="1"></button>
                            <button type="button" class="w-3 h-3 rounded-full neomorphic-carousel-indicator" aria-current="false" aria-label="Slide 3" data-carousel-slide-to="2"></button>
                        </div>
                        <!-- Slider controls -->
                        <button type="button" class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none neomorphic-carousel-control" data-carousel-prev>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full sm:w-10 sm:h-10">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                <span class="sr-only">Previous</span>
                            </span>
                        </button>
                        <button type="button" class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none neomorphic-carousel-control" data-carousel-next>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full sm:w-10 sm:h-10">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                <span class="sr-only">Next</span>
                            </span>
                        </button>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div id="default-carousel" class="relative w-full" data-carousel="static"&gt;
    &lt;!-- Carousel wrapper --&gt;
    &lt;div class="relative h-56 overflow-hidden rounded-lg md:h-96"&gt;
        &lt;!-- Item 1 --&gt;
        &lt;div class="hidden duration-700 ease-in-out neomorphic-carousel-item" data-carousel-item&gt;
            &lt;img src="https://placehold.co/800x400/0d0d1a/00bcd4?text=Image+1" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Slide 1"&gt;
        &lt;/div&gt;
        &lt;!-- Item 2 --&gt;
        &lt;div class="hidden duration-700 ease-in-out neomorphic-carousel-item" data-carousel-item&gt;
            &lt;img src="https://placehold.co/800x400/0d0d1a/e91e63?text=Image+2" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Slide 2"&gt;
        &lt;/div&gt;
        &lt;!-- Item 3 --&gt;
        &lt;div class="hidden duration-700 ease-in-out neomorphic-carousel-item" data-carousel-item&gt;
            &lt;img src="https://placehold.co/800x400/0d0d1a/ffeb3b?text=Image+3" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Slide 3"&gt;
        &lt;/div&gt;
    &lt;/div&gt;
    &lt;!-- Slider indicators --&gt;
    &lt;div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2"&gt;
        &lt;button type="button" class="w-3 h-3 rounded-full neomorphic-carousel-indicator" aria-current="true" aria-label="Slide 1" data-carousel-slide-to="0"&gt;&lt;/button&gt;
        &lt;button type="button" class="w-3 h-3 rounded-full neomorphic-carousel-indicator" aria-current="false" aria-label="Slide 2" data-carousel-slide-to="1"&gt;&lt;/button&gt;
        &lt;button type="button" class="w-3 h-3 rounded-full neomorphic-carousel-indicator" aria-current="false" aria-label="Slide 3" data-carousel-slide-to="2"&gt;&lt;/button&gt;
    &lt;/div&gt;
    &lt;!-- Slider controls --&gt;
    &lt;button type="button" class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none neomorphic-carousel-control" data-carousel-prev&gt;
        &lt;span class="inline-flex items-center justify-center w-8 h-8 rounded-full sm:w-10 sm:h-10"&gt;
            &lt;svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"&gt;&lt;/path&gt;&lt;/svg&gt;
            &lt;span class="sr-only"&gt;Previous&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
    &lt;button type="button" class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none neomorphic-carousel-control" data-carousel-next&gt;
        &lt;span class="inline-flex items-center justify-center w-8 h-8 rounded-full sm:w-10 sm:h-10"&gt;
            &lt;svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"&gt;&lt;/path&gt;&lt;/svg&gt;
            &lt;span class="sr-only"&gt;Next&lt;/span&gt;
        &lt;/span&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;!-- Note: Carousels require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Tooltip -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.10 Tooltip</h3>
                <p class="text-gray-400 mb-2">Provides additional information when a user hovers over an element.</p>
                <div class="mb-4 flex justify-center">
                    <button data-tooltip-target="tooltip-default" type="button" class="neomorphic-tooltip-btn font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Hover me
                    </button>
                    <div id="tooltip-default" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium transition-opacity duration-300 rounded-lg shadow-sm opacity-0 neomorphic-tooltip">
                        Tooltip content
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button data-tooltip-target="tooltip-default" type="button" class="neomorphic-tooltip-btn font-medium rounded-lg text-sm px-5 py-2.5 text-center"&gt;
    Hover me
&lt;/button&gt;
&lt;div id="tooltip-default" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium transition-opacity duration-300 rounded-lg shadow-sm opacity-0 neomorphic-tooltip"&gt;
    Tooltip content
    &lt;div class="tooltip-arrow" data-popper-arrow&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;!-- Note: Tooltips require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Popover -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.11 Popover</h3>
                <p class="text-gray-400 mb-2">Displays a small overlay of content when an element is clicked.</p>
                <div class="mb-4 flex justify-center">
                    <button data-popover-target="popover-default" type="button" class="neomorphic-popover-btn font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Click me
                    </button>
                    <div data-popover id="popover-default" role="tooltip" class="absolute z-10 invisible inline-block w-64 text-sm transition-opacity duration-300 rounded-lg shadow-sm opacity-0 neomorphic-popover">
                        <div class="px-3 py-2 border-b rounded-t-lg neomorphic-popover-header">
                            <h3 class="font-semibold">Popover title</h3>
                        </div>
                        <div class="px-3 py-2">
                            <p>And here's some amazing content. It's very engaging. Right?</p>
                        </div>
                        <div data-popper-arrow></div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button data-popover-target="popover-default" type="button" class="neomorphic-popover-btn font-medium rounded-lg text-sm px-5 py-2.5 text-center"&gt;
    Click me
&lt;/button&gt;
&lt;div data-popover id="popover-default" role="tooltip" class="absolute z-10 invisible inline-block w-64 text-sm transition-opacity duration-300 rounded-lg shadow-sm opacity-0 neomorphic-popover"&gt;
    &lt;div class="px-3 py-2 border-b rounded-t-lg neomorphic-popover-header"&gt;
        &lt;h3 class="font-semibold"&gt;Popover title&lt;/h3&gt;
    &lt;/div&gt;
    &lt;div class="px-3 py-2"&gt;
        &lt;p&gt;And here's some amazing content. It's very engaging. Right?&lt;/p&gt;
    &lt;/div&gt;
    &lt;div data-popper-arrow&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;!-- Note: Popovers require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Alert -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.12 Alert</h3>
                <p class="text-gray-400 mb-2">Conveys important, time-sensitive messages to the user.</p>
                <div class="mb-4">
                    <div class="flex items-center p-4 mb-4 rounded-lg neomorphic-alert blue" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Info</span>
                        <div>
                            <span class="font-medium">Info alert!</span> Change a few things up and try submitting again.
                        </div>
                    </div>
                    <div class="flex items-center p-4 mb-4 rounded-lg neomorphic-alert green" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Success</span>
                        <div>
                            <span class="font-medium">Success alert!</span> Your action was successful.
                        </div>
                    </div>
                    <div class="flex items-center p-4 mb-4 rounded-lg neomorphic-alert red" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 010-2h2a1 1 0 011 1v4h1a1 1 0 010 2Z"/>
                        </svg>
                        <span class="sr-only">Danger</span>
                        <div>
                            <span class="font-medium">Danger alert!</span> Something went wrong.
                        </div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="flex items-center p-4 mb-4 rounded-lg neomorphic-alert blue" role="alert"&gt;
    &lt;svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"&gt;
        &lt;path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 011 1v4h1a1 1 0 010 2Z"/&gt;
    &lt;/svg&gt;
    &lt;span class="sr-only"&gt;Info&lt;/span&gt;
    &lt;div&gt;
        &lt;span class="font-medium"&gt;Info alert!&lt;/span&gt; Change a few things up and try submitting again.
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="flex items-center p-4 mb-4 rounded-lg neomorphic-alert green" role="alert"&gt;
    &lt;svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"&gt;
        &lt;path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 010-3ZM12 15H8a1 1 0 010-2h1v-3H8a1 1 0 010-2h2a1 1 0 011 1v4h1a1 1 0 010 2Z"/&gt;
    &lt;/svg&gt;
    &lt;span class="sr-only"&gt;Success&lt;/span&gt;
    &lt;div&gt;
        &lt;span class="font-medium"&gt;Success alert!&lt;/span&gt; Your action was successful.
    &lt;/div&gt;
&lt;/div&gt;
&lt;div class="flex items-center p-4 mb-4 rounded-lg neomorphic-alert red" role="alert"&gt;
    &lt;svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"&gt;
        &lt;path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 010-3ZM12 15H8a1 1 0 010-2h1v-3H8a1 1 0 010-2h2a1 1 0 011 1v4h1a1 1 0 010 2Z"/&gt;
    &lt;/svg&gt;
    &lt;span class="sr-only"&gt;Danger&lt;/span&gt;
    &lt;div&gt;
        &lt;span class="font-medium"&gt;Danger alert!&lt;/span&gt; Something went wrong.
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Modal -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.13 Modal</h3>
                <p class="text-gray-400 mb-2">A dialog box that appears on top of the current page, requiring user interaction.</p>
                <div class="mb-4 flex justify-center">
                    <button data-modal-target="defaultModal" data-modal-toggle="defaultModal" class="block text-white neomorphic-btn" type="button">
                        Toggle modal
                    </button>

                    <!-- Main modal -->
                    <div id="defaultModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full neomorphic-modal-overlay">
                        <div class="relative w-full max-w-2xl max-h-full">
                            <!-- Modal content -->
                            <div class="relative rounded-lg shadow neomorphic-modal">
                                <!-- Modal header -->
                                <div class="flex items-start justify-between p-4 border-b rounded-t neomorphic-modal-header">
                                    <h3 class="text-xl font-semibold text-[var(--primary)]">
                                        Modal Title
                                    </h3>
                                    <button type="button" class="text-[var(--text-secondary)] bg-transparent hover:bg-[rgba(255,255,255,0.1)] hover:text-[var(--primary)] rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center btn-close-modal" data-modal-hide="defaultModal">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                <!-- Modal body -->
                                <div class="p-6 space-y-6 neomorphic-modal-body">
                                    <p class="text-base leading-relaxed text-[var(--text-secondary)]">
                                        With less than a month to go before the European Union's new privacy law goes into effect, companies around the world are scrambling to make sure they're compliant.
                                    </p>
                                    <p class="text-base leading-relaxed text-[var(--text-secondary)]">
                                        The European Union's General Data Protection Regulation (GDR) goes into effect on May 25, 2018.
                                    </p>
                                </div>
                                <!-- Modal footer -->
                                <div class="flex items-center p-6 space-x-2 border-t rounded-b neomorphic-modal-footer">
                                    <button data-modal-hide="defaultModal" type="button" class="neomorphic-btn">I accept</button>
                                    <button data-modal-hide="defaultModal" type="button" class="neomorphic-btn-secondary">Decline</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button data-modal-target="defaultModal" data-modal-toggle="defaultModal" class="block text-white neomorphic-btn" type="button"&gt;
    Toggle modal
&lt;/button&gt;

&lt;!-- Main modal --&gt;
&lt;div id="defaultModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full neomorphic-modal-overlay"&gt;
    &lt;div class="relative w-full max-w-2xl max-h-full"&gt;
        &lt;!-- Modal content --&gt;
        &lt;div class="relative rounded-lg shadow neomorphic-modal"&gt;
            &lt;!-- Modal header --&gt;
            &lt;div class="flex items-start justify-between p-4 border-b rounded-t neomorphic-modal-header"&gt;
                &lt;h3 class="text-xl font-semibold text-[var(--primary)]"&gt;
                    Modal Title
                &lt;/h3&gt;
                &lt;button type="button" class="text-[var(--text-secondary)] bg-transparent hover:bg-[rgba(255,255,255,0.1)] hover:text-[var(--primary)] rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center btn-close-modal" data-modal-hide="defaultModal"&gt;
                    &lt;svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"&gt;
                        &lt;path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/&gt;
                    &lt;/svg&gt;
                    &lt;span class="sr-only"&gt;Close modal&lt;/span&gt;
                &lt;/button&gt;
            &lt;/div&gt;
            &lt;!-- Modal body --&gt;
            &lt;div class="p-6 space-y-6 neomorphic-modal-body"&gt;
                &lt;p class="text-base leading-relaxed text-[var(--text-secondary)]"&gt;
                    With less than a month to go before the European Union's new privacy law goes into effect, companies around the world are scrambling to make sure they're compliant.
                &lt;/p&gt;
                &lt;p class="text-base leading-relaxed text-[var(--text-secondary)]"&gt;
                    The European Union's General Data Protection Regulation (GDR) goes into effect on May 25, 2018.
                &lt;/p&gt;
            &lt;/div&gt;
            &lt;!-- Modal footer --&gt;
            &lt;div class="flex items-center p-6 space-x-2 border-t rounded-b neomorphic-modal-footer"&gt;
                &lt;button data-modal-hide="defaultModal" type="button" class="neomorphic-btn"&gt;I accept&lt;/button&gt;
                &lt;button data-modal-hide="defaultModal" type="button" class="neomorphic-btn-secondary"&gt;Decline&lt;/button&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;!-- Note: Modals require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Toast Notification -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.14 Toast Notification</h3>
                <p class="text-gray-400 mb-2">Small, non-intrusive messages that appear temporarily to provide feedback.</p>
                <div class="mb-4">
                    <div id="toast-success" class="flex items-center w-full max-w-xs p-4 mb-4 rounded-lg shadow neomorphic-toast" role="alert">
                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg icon-bg-green">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                            </svg>
                            <span class="sr-only">Check icon</span>
                        </div>
                        <div class="ml-3 text-sm font-normal">Item moved successfully.</div>
                        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-transparent text-[var(--text-secondary)] hover:text-[var(--primary)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] p-1.5 hover:bg-[rgba(255,255,255,0.1)] inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div id="toast-success" class="flex items-center w-full max-w-xs p-4 mb-4 rounded-lg shadow neomorphic-toast" role="alert"&gt;
    &lt;div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg icon-bg-green"&gt;
        &lt;svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"&gt;
            &lt;path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 011.414 1.414Z"/&gt;
        &lt;/svg&gt;
        &lt;span class="sr-only"&gt;Check icon&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ml-3 text-sm font-normal"&gt;Item moved successfully.&lt;/div&gt;
    &lt;button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-transparent text-[var(--text-secondary)] hover:text-[var(--primary)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] p-1.5 hover:bg-[rgba(255,255,255,0.1)] inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close"&gt;
        &lt;span class="sr-only"&gt;Close&lt;/span&gt;
        &lt;svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"&gt;
            &lt;path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/&gt;
        &lt;/svg&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;!-- Note: Toasts require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Skeleton Loader -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">4.15 Skeleton Loader</h3>
                <p class="text-gray-400 mb-2">A placeholder animation shown while content is loading, mimicking the structure of the actual content.</p>
                <div class="mb-4">
                    <div role="status" class="max-w-md p-4 space-y-4 divide-y divide-[var(--glass-border)] rounded shadow animate-pulse neomorphic-skeleton md:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-24 mb-2.5 neomorphic-skeleton-line"></div>
                                <div class="w-32 h-2 bg-[rgba(255,255,255,0.05)] rounded-full neomorphic-skeleton-line"></div>
                            </div>
                            <div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-12 neomorphic-skeleton-line"></div>
                        </div>
                        <div class="flex items-center justify-between pt-4">
                            <div>
                                <div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-24 mb-2.5 neomorphic-skeleton-line"></div>
                                <div class="w-32 h-2 bg-[rgba(255,255,255,0.05)] rounded-full neomorphic-skeleton-line"></div>
                            </div>
                            <div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-12 neomorphic-skeleton-line"></div>
                        </div>
                        <div class="flex items-center justify-between pt-4">
                            <div>
                                <div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-24 mb-2.5 neomorphic-skeleton-line"></div>
                                <div class="w-32 h-2 bg-[rgba(255,255,255,0.05)] rounded-full neomorphic-skeleton-line"></div>
                            </div>
                            <div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-12 neomorphic-skeleton-line"></div>
                        </div>
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div role="status" class="max-w-md p-4 space-y-4 divide-y divide-[var(--glass-border)] rounded shadow animate-pulse neomorphic-skeleton md:p-6"&gt;
    &lt;div class="flex items-center justify-between"&gt;
        &lt;div&gt;
            &lt;div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-24 mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
            &lt;div class="w-32 h-2 bg-[rgba(255,255,255,0.05)] rounded-full neomorphic-skeleton-line"&gt;&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-12 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;/div&gt;
    &lt;div class="flex items-center justify-between pt-4"&gt;
        &lt;div&gt;
            &lt;div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-24 mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
            &lt;div class="w-32 h-2 bg-[rgba(255,255,255,0.05)] rounded-full neomorphic-skeleton-line"&gt;&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-12 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;/div&gt;
    &lt;div class="flex items-center justify-between pt-4"&gt;
        &lt;div&gt;
            &lt;div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-24 mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
            &lt;div class="w-32 h-2 bg-[rgba(255,255,255,0.05)] rounded-full neomorphic-skeleton-line"&gt;&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="h-2.5 bg-[rgba(255,255,255,0.1)] rounded-full w-12 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;/div&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;</code></pre>
            </div>
        </section>

        <!-- Section: Feedback & Notifications -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">5. Feedback & Notifications</h2>

            <!-- Toast (already covered in Data Display, but good to re-list for completeness in this category) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">5.1 Toast Notification (Recap)</h3>
                <p class="text-gray-400 mb-2">Small, non-intrusive messages that appear temporarily to provide feedback.</p>
                <div class="mb-4">
                    <div id="toast-info" class="flex items-center w-full max-w-xs p-4 mb-4 rounded-lg shadow neomorphic-toast" role="alert">
                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg icon-bg-blue">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                            </svg>
                            <span class="sr-only">Info icon</span>
                        </div>
                        <div class="ml-3 text-sm font-normal">A new update is available.</div>
                        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-transparent text-[var(--text-secondary)] hover:text-[var(--primary)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] p-1.5 hover:bg-[rgba(255,255,255,0.1)] inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-info" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div id="toast-info" class="flex items-center w-full max-w-xs p-4 mb-4 rounded-lg shadow neomorphic-toast" role="alert"&gt;
    &lt;div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg icon-bg-blue"&gt;
        &lt;svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"&gt;
            &lt;path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/&gt;
        &lt;/svg&gt;
        &lt;span class="sr-only"&gt;Info icon&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="ml-3 text-sm font-normal"&gt;A new update is available.&lt;/div&gt;
    &lt;button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-transparent text-[var(--text-secondary)] hover:text-[var(--primary)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] p-1.5 hover:bg-[rgba(255,255,255,0.1)] inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-info" aria-label="Close"&gt;
        &lt;span class="sr-only"&gt;Close&lt;/span&gt;
        &lt;svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"&gt;
            &lt;path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/&gt;
        &lt;/svg&gt;
    &lt;/button&gt;
&lt;/div&gt;
&lt;!-- Note: Toasts require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Notification Badge (already covered in Data Display, but good to re-list for completeness in this category) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">5.2 Notification Badge (Recap)</h3>
                <p class="text-gray-400 mb-2">A small indicator, usually a number, showing unread items or new notifications.</p>
                <div class="mb-4">
                    <button type="button" class="relative inline-flex items-center p-3 text-sm font-medium text-center text-white neomorphic-btn">
                        Messages
                        <span class="sr-only">Notifications</span>
                        <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-[var(--danger)] border-2 border-[var(--bg-primary)] rounded-full -top-2 -right-2 shadow-[0_0_8px_var(--danger)]">99+</div>
                    </button>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button type="button" class="relative inline-flex items-center p-3 text-sm font-medium text-center text-white neomorphic-btn"&gt;
    Messages
    &lt;span class="sr-only"&gt;Notifications&lt;/span&gt;
    &lt;div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-[var(--danger)] border-2 border-[var(--bg-primary)] rounded-full -top-2 -right-2 shadow-[0_0_8px_var(--danger)]"&gt;99+&lt;/div&gt;
&lt;/button&gt;</code></pre>
            </div>
        </section>

        <!-- Section: Layout & Structure -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">6. Layout & Structure</h2>

            <!-- Grid Layout -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">6.1 Grid Layout</h3>
                <p class="text-gray-400 mb-2">Organizes content into rows and columns, providing a structured layout.</p>
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="neomorphic-card p-4 text-[var(--primary)] text-center">Column 1</div>
                    <div class="neomorphic-card p-4 text-[var(--primary)] text-center">Column 2</div>
                    <div class="neomorphic-card p-4 text-[var(--primary)] text-center">Column 3</div>
                    <div class="neomorphic-card p-4 text-[var(--primary)] text-center md:col-span-2 lg:col-span-1">Column 4 (Spans)</div>
                    <div class="neomorphic-card p-4 text-[var(--primary)] text-center">Column 5</div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] text-center"&gt;Column 1&lt;/div&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] text-center"&gt;Column 2&lt;/div&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] text-center"&gt;Column 3&lt;/div&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] text-center md:col-span-2 lg:col-span-1"&gt;Column 4 (Spans)&lt;/div&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] text-center"&gt;Column 5&lt;/div&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Flexbox Layout -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">6.2 Flexbox Layout</h3>
                <p class="text-gray-400 mb-2">Provides a one-dimensional layout system for aligning and distributing items in a container.</p>
                <div class="mb-4 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="neomorphic-card p-4 text-[var(--primary)] flex-1 text-center">Item 1</div>
                    <div class="neomorphic-card p-4 text-[var(--primary)] flex-1 text-center">Item 2</div>
                    <div class="neomorphic-card p-4 text-[var(--primary)] flex-1 text-center">Item 3</div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4"&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] flex-1 text-center"&gt;Item 1&lt;/div&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] flex-1 text-center"&gt;Item 2&lt;/div&gt;
    &lt;div class="neomorphic-card p-4 text-[var(--primary)] flex-1 text-center"&gt;Item 3&lt;/div&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Footer -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">6.3 Footer</h3>
                <p class="text-gray-400 mb-2">The bottom section of a web page, typically containing copyright information, links, and contact details.</p>
                <div class="mb-4 bg-[var(--bg-secondary)] text-[var(--text-secondary)] p-6 rounded-lg text-center border border-[var(--glass-border)] shadow-[6px_6px_12px_var(--neomorphic-dark-shadow)]">
                    <p class="mb-2">&copy; 2023 My Web App. All rights reserved.</p>
                    <ul class="flex justify-center space-x-4">
                        <li><a href="#" class="hover:text-[var(--primary)]">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-[var(--primary)]">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-[var(--primary)]">Contact Us</a></li>
                    </ul>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;footer class="bg-[var(--bg-secondary)] text-[var(--text-secondary)] p-6 rounded-lg text-center border border-[var(--glass-border)] shadow-[6px_6px_12px_var(--neomorphic-dark-shadow)]"&gt;
    &lt;p class="mb-2"&gt;&copy; 2023 My Web App. All rights reserved.&lt;/p&gt;
    &lt;ul class="flex justify-center space-x-4"&gt;
        &lt;li&gt;&lt;a href="#" class="hover:text-[var(--primary)]"&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#" class="hover:text-[var(--primary)]"&gt;Terms of Service&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#" class="hover:text-[var(--primary)]"&gt;Contact Us&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
&lt;/footer&gt;</code></pre>
            </div>
        </section>

        <!-- Section: Advanced Components -->
        <section class="mb-10">
            <h2 class="text-3xl font-semibold mb-6 border-b-2 border-[var(--glass-border)] pb-2">7. Advanced Components</h2>

            <!-- Dropdown Menu (Clickable) -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">7.1 Dropdown Menu (Clickable)</h3>
                <p class="text-gray-400 mb-2">A menu that appears when a button or link is clicked, offering a list of options.</p>
                <div class="mb-4 relative inline-block text-left">
                    <button id="dropdownButton" data-dropdown-toggle="dropdownMenu" class="inline-flex items-center px-4 py-2 text-sm font-medium neomorphic-dropdown-btn" type="button">
                        Options
                        <svg class="w-4 h-4 ml-2 text-[var(--primary)]" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="dropdownMenu" class="z-10 hidden divide-y divide-[var(--glass-border)] rounded-lg shadow w-44 neomorphic-dropdown-menu">
                        <ul class="py-2 text-sm text-[var(--text-primary)]" aria-labelledby="dropdownButton">
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">Dashboard</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">Settings</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">Earnings</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">Sign out</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="relative inline-block text-left"&gt;
    &lt;button id="dropdownButton" data-dropdown-toggle="dropdownMenu" class="inline-flex items-center px-4 py-2 text-sm font-medium neomorphic-dropdown-btn" type="button"&gt;
        Options
        &lt;svg class="w-4 h-4 ml-2 text-[var(--primary)]" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"&gt;&lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/button&gt;

    &lt;!-- Dropdown menu --&gt;
    &lt;div id="dropdownMenu" class="z-10 hidden divide-y divide-[var(--glass-border)] rounded-lg shadow w-44 neomorphic-dropdown-menu"&gt;
        &lt;ul class="py-2 text-sm text-[var(--text-primary)]" aria-labelledby="dropdownButton"&gt;
            &lt;li&gt;
                &lt;a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;Dashboard&lt;/a&gt;
            &lt;/li&gt;
            &lt;li&gt;
                &lt;a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;Settings&lt;/a&gt;
            &lt;/li&gt;
            &lt;li&gt;
                &lt;a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;Earnings&lt;/a&gt;
            &lt;/li&gt;
            &lt;li&gt;
                &lt;a href="#" class="block px-4 py-2 hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;Sign out&lt;/a&gt;
            &lt;/li&gt;
        &lt;/ul&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;!-- Note: Dropdown menus require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Offcanvas/Drawer -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">7.2 Offcanvas/Drawer</h3>
                <p class="text-gray-400 mb-2">A hidden sidebar that slides into view, often used for navigation or additional content on smaller screens.</p>
                <div class="mb-4">
                    <button data-drawer-target="drawer-navigation" data-drawer-show="drawer-navigation" aria-controls="drawer-navigation" type="button" class="neomorphic-drawer-btn font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Open navigation
                    </button>

                    <!-- drawer component -->
                    <div id="drawer-navigation" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full w-64 neomorphic-drawer" tabindex="-1" aria-labelledby="drawer-navigation-label">
                        <h5 id="drawer-navigation-label" class="text-base font-semibold text-[var(--accent)] uppercase">Menu</h5>
                        <button type="button" data-drawer-hide="drawer-navigation" aria-controls="drawer-navigation" class="text-[var(--text-secondary)] bg-transparent hover:bg-[rgba(255,255,255,0.1)] hover:text-[var(--primary)] rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Close menu</span>
                        </button>
                        <div class="py-4 overflow-y-auto">
                            <ul class="space-y-2 font-medium">
                                <li>
                                    <a href="#" class="flex items-center p-2 text-[var(--text-primary)] rounded-lg hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">
                                        <span class="ml-3">Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="flex items-center p-2 text-[var(--text-primary)] rounded-lg hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">
                                        <span class="ml-3">Kanban</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="flex items-center p-2 text-[var(--text-primary)] rounded-lg hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]">
                                        <span class="ml-3">Inbox</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;button data-drawer-target="drawer-navigation" data-drawer-show="drawer-navigation" aria-controls="drawer-navigation" type="button" class="neomorphic-drawer-btn font-medium rounded-lg text-sm px-5 py-2.5 text-center"&gt;
    Open navigation
&lt;/button&gt;

&lt;!-- drawer component --&gt;
&lt;div id="drawer-navigation" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full w-64 neomorphic-drawer" tabindex="-1" aria-labelledby="drawer-navigation-label"&gt;
    &lt;h5 id="drawer-navigation-label" class="text-base font-semibold text-[var(--accent)] uppercase"&gt;Menu&lt;/h5&gt;
    &lt;button type="button" data-drawer-hide="drawer-navigation" aria-controls="drawer-navigation" class="text-[var(--text-secondary)] bg-transparent hover:bg-[rgba(255,255,255,0.1)] hover:text-[var(--primary)] rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center"&gt;
        &lt;svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
        &lt;span class="sr-only"&gt;Close menu&lt;/span&gt;
    &lt;/button&gt;
    &lt;div class="py-4 overflow-y-auto"&gt;
        &lt;ul class="space-y-2 font-medium"&gt;
            &lt;li&gt;
                &lt;a href="#" class="flex items-center p-2 text-[var(--text-primary)] rounded-lg hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;
                    &lt;span class="ml-3"&gt;Dashboard&lt;/span&gt;
                &lt;/a&gt;
            &lt;/li&gt;
            &lt;li&gt;
                &lt;a href="#" class="flex items-center p-2 text-[var(--text-primary)] rounded-lg hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;
                    &lt;span class="ml-3"&gt;Kanban&lt;/span&gt;
                &lt;/a&gt;
            &lt;/li&gt;
            &lt;li&gt;
                &lt;a href="#" class="flex items-center p-2 text-[var(--text-primary)] rounded-lg hover:bg-[rgba(0,188,212,0.1)] hover:text-[var(--primary)]"&gt;
                    &lt;span class="ml-3"&gt;Inbox&lt;/span&gt;
                &lt;/a&gt;
            &lt;/li&gt;
        &lt;/ul&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;!-- Note: Drawers require JavaScript for functionality. --&gt;</code></pre>
            </div>

            <!-- Stepper/Wizard -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">7.3 Stepper/Wizard</h3>
                <p class="text-gray-400 mb-2">Guides users through a multi-step process.</p>
                <div class="mb-4">
                    <ol class="flex items-center w-full text-sm font-medium text-center sm:text-base">
                        <li class="flex md:w-full items-center text-[var(--primary)] sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-[var(--glass-border)] after:border-1 after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10 neomorphic-stepper-item active">
                            <span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-[var(--glass-border)]">
                                <svg class="w-4 h-4 mr-2 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 000-1.414z" clip-rule="evenodd"></path></svg>
                                Personal <span class="hidden sm:inline-flex sm:ml-2">Info</span>
                            </span>
                        </li>
                        <li class="flex md:w-full items-center text-[var(--text-secondary)] sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-[var(--glass-border)] after:border-1 after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10 neomorphic-stepper-item">
                            <span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-[var(--glass-border)]">
                                <span class="mr-2">2</span>
                                Account <span class="hidden sm:inline-flex sm:ml-2">Setup</span>
                            </span>
                        </li>
                        <li class="flex items-center text-[var(--text-secondary)] neomorphic-stepper-item">
                            <span class="mr-2">3</span>
                            Confirmation
                        </li>
                    </ol>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;ol class="flex items-center w-full text-sm font-medium text-center sm:text-base"&gt;
    &lt;li class="flex md:w-full items-center text-[var(--primary)] sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-[var(--glass-border)] after:border-1 after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10 neomorphic-stepper-item active"&gt;
        &lt;span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-[var(--glass-border)]"&gt;
            &lt;svg class="w-4 h-4 mr-2 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 000-1.414z" clip-rule="evenodd"&gt;&lt;/path&gt;&lt;/svg&gt;
            Personal &lt;span class="hidden sm:inline-flex sm:ml-2"&gt;Info&lt;/span&gt;
        &lt;/span&gt;
    &lt;/li&gt;
    &lt;li class="flex md:w-full items-center text-[var(--text-secondary)] sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-[var(--glass-border)] after:border-1 after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10 neomorphic-stepper-item"&gt;
        &lt;span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-[var(--glass-border)]"&gt;
            &lt;span class="mr-2"&gt;2&lt;/span&gt;
            Account &lt;span class="hidden sm:inline-flex sm:ml-2"&gt;Setup&lt;/span&gt;
        &lt;/span&gt;
    &lt;/li&gt;
    &lt;li class="flex items-center text-[var(--text-secondary)] neomorphic-stepper-item"&gt;
        &lt;span class="mr-2"&gt;3&lt;/span&gt;
        Confirmation
    &lt;/li&gt;
&lt;/ol&gt;</code></pre>
            </div>

            <!-- Rating/Stars -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">7.4 Rating/Stars</h3>
                <p class="text-gray-400 mb-2">Allows users to rate items, typically using a star system.</p>
                <div class="mb-4 flex items-center">
                    <span class="neomorphic-star filled">
                        <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"></path></svg>
                    </span>
                    <span class="neomorphic-star filled">
                        <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"></path></svg>
                    </span>
                    <span class="neomorphic-star filled">
                        <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"></path></svg>
                    </span>
                    <span class="neomorphic-star filled">
                        <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"></path></svg>
                    </span>
                    <span class="neomorphic-star empty">
                        <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"></path></svg>
                    </span>
                    <p class="ml-2 text-sm font-bold neomorphic-rating-text">4.0 out of 5 stars</p>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div class="flex items-center"&gt;
    &lt;span class="neomorphic-star filled"&gt;
        &lt;svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/span&gt;
    &lt;span class="neomorphic-star filled"&gt;
        &lt;svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/span&gt;
    &lt;span class="neomorphic-star filled"&gt;
        &lt;svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/span&gt;
    &lt;span class="neomorphic-star filled"&gt;
        &lt;svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/span&gt;
    &lt;span class="neomorphic-star empty"&gt;
        &lt;svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"&gt;&lt;path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"&gt;&lt;/path&gt;&lt;/svg&gt;
    &lt;/span&gt;
    &lt;p class="ml-2 text-sm font-bold neomorphic-rating-text"&gt;4.0 out of 5 stars&lt;/p&gt;
&lt;/div&gt;</code></pre>
            </div>

            <!-- Tags/Pills -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">7.5 Tags/Pills</h3>
                <p class="text-gray-400 mb-2">Small, interactive labels used for categorization or filtering.</p>
                <div class="mb-4 flex space-x-2">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium neomorphic-tag purple">
                        Tag 1
                        <button type="button" class="flex-shrink-0 ml-1.5 h-3 w-3 rounded-full inline-flex items-center justify-center">
                            <span class="sr-only">Remove tag</span>
                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 6" />
                            </svg>
                        </button>
                    </span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium neomorphic-tag pink">
                        Tag 2
                        <button type="button" class="flex-shrink-0 ml-1.5 h-3 w-3 rounded-full inline-flex items-center justify-center">
                            <span class="sr-only">Remove tag</span>
                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 6" />
                            </svg>
                        </button>
                    </span>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium neomorphic-tag purple"&gt;
    Tag 1
    &lt;button type="button" class="flex-shrink-0 ml-1.5 h-3 w-3 rounded-full inline-flex items-center justify-center"&gt;
        &lt;span class="sr-only"&gt;Remove tag&lt;/span&gt;
        &lt;svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"&gt;
            &lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 6" /&gt;
        &lt;/svg&gt;
    &lt;/button&gt;
&lt;/span&gt;
&lt;span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium neomorphic-tag pink"&gt;
    Tag 2
    &lt;button type="button" class="flex-shrink-0 ml-1.5 h-3 w-3 rounded-full inline-flex items-center justify-center"&gt;
        &lt;span class="sr-only"&gt;Remove tag&lt;/span&gt;
        &lt;svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"&gt;
            &lt;path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 6" /&gt;
        &lt;/svg&gt;
    &lt;/button&gt;
&lt;/span&gt;</code></pre>
            </div>

            <!-- Skeleton Text -->
            <div class="mb-6 p-4 neomorphic-card">
                <h3 class="text-2xl font-medium mb-3">7.6 Skeleton Text</h3>
                <p class="text-gray-400 mb-2">Placeholder lines of text that mimic the shape and length of actual text while content is loading.</p>
                <div class="mb-4">
                    <div role="status" class="max-w-sm p-4 space-y-4 divide-y rounded shadow animate-pulse neomorphic-skeleton md:p-6">
                        <div class="h-2.5 rounded-full w-48 mb-4 neomorphic-skeleton-line"></div>
                        <div class="h-2 rounded-full max-w-[360px] mb-2.5 neomorphic-skeleton-line"></div>
                        <div class="h-2 rounded-full mb-2.5 neomorphic-skeleton-line"></div>
                        <div class="h-2 rounded-full max-w-[330px] mb-2.5 neomorphic-skeleton-line"></div>
                        <div class="h-2 rounded-full max-w-[300px] mb-2.5 neomorphic-skeleton-line"></div>
                        <div class="h-2 rounded-full max-w-[360px] neomorphic-skeleton-line"></div>
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <pre class="bg-[var(--bg-secondary)] p-3 rounded-md overflow-x-auto text-sm text-[var(--text-primary)]"><code class="language-html">&lt;div role="status" class="max-w-sm p-4 space-y-4 divide-y rounded shadow animate-pulse neomorphic-skeleton md:p-6"&gt;
    &lt;div class="h-2.5 rounded-full w-48 mb-4 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;div class="h-2 rounded-full max-w-[360px] mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;div class="h-2 rounded-full mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;div class="h-2 rounded-full max-w-[330px] mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;div class="h-2 rounded-full max-w-[300px] mb-2.5 neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;div class="h-2 rounded-full max-w-[360px] neomorphic-skeleton-line"&gt;&lt;/div&gt;
    &lt;span class="sr-only"&gt;Loading...&lt;/span&gt;
&lt;/div&gt;</code></pre>
            </div>
        </section>

    </div>
</body>
</html>

    </div>
    <div class="tab-content" id="icons-tab" style="display: none;">
      <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Font Awesome Icons Menu</title>
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Variables - from dashboard.css */
        :root {
            --primary: #00bcd4; /* Neon Cyan */
            --secondary: #e91e63; /* Neon Magenta */
            --accent: #ffeb3b; /* Neon Yellow - Default accent */
            --text-primary: #e0e0e0; /* Light text for readability */
            --text-secondary: #a0a0a0; /* Subtler text */
            --bg-primary: #0d0d1a; /* Very dark blue/black for main background */
            --bg-secondary: #1a1a2e; /* Slightly lighter dark blue for secondary backgrounds */
            --glass-bg: rgba(25, 25, 40, 0.4); /* Darker, more transparent glass */
            --glass-border: rgba(255, 255, 255, 0.08); /* More subtle glass border */
            --shadow-dark: rgba(0, 0, 0, 0.6); /* Deeper shadows for glass elements */
            --shadow-light: rgba(255, 255, 255, 0.02); /* Very subtle light shadow */
            --neon-glow-primary: 0 0 10px rgba(0, 188, 212, 0.6), 0 0 20px rgba(0, 188, 212, 0.4); /* Cyan glow */
            --neon-glow-secondary: 0 0 10px rgba(233, 30, 99, 0.6), 0 0 20px rgba(233, 30, 99, 0.4); /* Magenta glow */
            --success: #4CAF50;
            --danger: #F44336;
            --warning: #FFC107;
            --info: #2196F3;
            --border-radius: 12px; /* Consistent rounded corners */
        }

        /* Existing styles from font_awesome.php's internal CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif; /* Changed to Inter as per instructions */
            background: var(--bg-primary); /* Using CSS variable */
            min-height: 100vh;
            padding: 20px;
            color: var(--text-primary); /* Ensure text is readable */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--bg-secondary); /* Using CSS variable */
            border-radius: var(--border-radius); /* Using CSS variable */
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid var(--glass-border); /* Added border for consistency */
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%); /* Using CSS variable */
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid var(--glass-border);
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: var(--neon-glow-primary);
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .search-container {
            padding: 30px;
            background: var(--bg-secondary); /* Using CSS variable */
            border-bottom: 1px solid var(--glass-border);
        }

        .search-box {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid var(--glass-border); /* Using CSS variable */
            background-color: rgba(25, 25, 40, 0.6); /* Glass effect */
            color: var(--text-primary); /* Text color */
            border-radius: 50px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--primary); /* Using CSS variable */
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.3); /* Using CSS variable */
        }

        .search-box .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary); /* Using CSS variable */
            font-size: 18px;
        }

        .content {
            padding: 30px;
        }

        .icon-group {
            margin-bottom: 40px;
        }

        .group-title {
            font-size: 1.5rem;
            color: var(--accent); /* Using CSS variable */
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary); /* Using CSS variable */
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 5px rgba(0, 188, 212, 0.3);
        }

        .group-title .count {
            background: var(--primary); /* Using CSS variable */
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: normal;
        }

        .icons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .icon-item {
            background: var(--glass-bg); /* Using CSS variable */
            border: 1px solid var(--glass-border); /* Using CSS variable */
            border-radius: var(--border-radius); /* Using CSS variable */
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            box-shadow: 4px 4px 8px var(--shadow-dark), -4px -4px 8px var(--shadow-light);
        }

        .icon-item:hover {
            border-color: var(--primary); /* Using CSS variable */
            transform: translateY(-2px);
            box-shadow: 0 0 15px rgba(0, 188, 212, 0.4); /* Neon glow on hover */
        }

        .icon-item i {
            font-size: 2rem;
            color: var(--primary); /* Using CSS variable */
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .icon-item:hover i {
            color: var(--accent); /* Change icon color on hover */
        }

        .icon-name {
            font-weight: 600;
            color: var(--text-primary); /* Using CSS variable */
            margin-bottom: 5px;
        }

        .icon-class {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: var(--text-secondary); /* Using CSS variable */
            background: rgba(0, 0, 0, 0.2); /* Darker background for code */
            padding: 5px 8px;
            border-radius: 5px;
            word-break: break-all;
        }

        .copy-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success); /* Using CSS variable */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        }

        .copy-notification.show {
            opacity: 1;
        }

        .stats {
            background: var(--glass-bg); /* Using CSS variable */
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: var(--border-radius); /* Using CSS variable */
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 4px 4px 8px var(--shadow-dark), -4px -4px 8px var(--shadow-light);
        }

        .stats h3 {
            color: var(--accent); /* Using CSS variable */
            margin-bottom: 10px;
        }

        .stats p {
            color: var(--text-primary);
        }

        .stats a {
            color: var(--primary); /* Using CSS variable */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .stats a:hover {
            color: var(--accent);
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary); /* Using CSS variable */
        }

        .no-results i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.1); /* Lighter, subtle color */
            margin-bottom: 20px;
        }

        /* Theme/Color Controls */
        .color-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            padding: 15px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: 4px 4px 8px var(--shadow-dark);
        }

        .color-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .color-option:hover {
            background-color: rgba(0, 188, 212, 0.1);
        }

        .color-box {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 5px;
            transition: border-color 0.3s ease;
        }

        .color-option.active .color-box {
            border-color: var(--accent);
            box-shadow: 0 0 10px var(--accent);
        }

        .color-option span {
            font-size: 0.8em;
            color: var(--text-secondary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .icons-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }

            .icon-item {
                padding: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .color-controls {
                flex-wrap: wrap;
            }
        }

        /* Ensure Font Awesome icons are visible - from dashboard.css */
        .fas, .far, .fal, .fab, .fa {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important; /* For solid icons (fas) */
            display: inline-block !important;
            font-size: inherit !important;
            color: inherit !important;
            text-rendering: auto !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-icons"></i> Font Awesome Icons</h1>
            <p>Browse and search through Font Awesome free icons</p>
        </div>

        <div class="search-container">
            <form id="searchForm" class="search-box">
                <input type="text"
                       id="searchInput"
                       name="search"
                       placeholder="Search icons..."
                       value=""
                       autocomplete="off">
                <i class="fas fa-search search-icon"></i>
            </form>
        </div>

        <div class="content">
            <div class="color-controls">
                <div class="color-option active" data-color="default">
                    <div class="color-box" style="background-color: var(--primary);"></div>
                    <span>Default</span>
                </div>
                <div class="color-option" data-color="red">
                    <div class="color-box" style="background-color: #F44336;"></div>
                    <span>Red</span>
                </div>
                <div class="color-option" data-color="green">
                    <div class="color-box" style="background-color: #4CAF50;"></div>
                    <span>Green</span>
                </div>
                <div class="color-option" data-color="yellow">
                    <div class="color-box" style="background-color: #FFC107;"></div>
                    <span>Yellow</span>
                </div>
                <div class="color-option" data-color="magenta">
                    <div class="color-box" style="background-color: var(--secondary);"></div>
                    <span>Magenta</span>
                </div>
            </div>

            <div id="stats" class="stats">
                <h3>Total Icons Available</h3>
                <p><span id="totalIconsCount"></span> free Font Awesome icons</p>
            </div>

            <div id="noResults" class="no-results" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No icons found</h3>
                <p>Try a different search term or <a href="#" onclick="clearSearch(); return false;" style="color: var(--primary);">browse all icons</a></p>
            </div>

            <div id="iconGroups">
                <!-- Icon groups will be rendered here by JavaScript -->
            </div>
        </div>
    </div>

    <div class="copy-notification" id="copyNotification">
        Icon class copied to clipboard!
    </div>

    <script>
        const fontAwesomeIcons = {
            // Solid Icons
            'fas fa-home': 'Home',
            'fas fa-user': 'User',
            'fas fa-search': 'Search',
            'fas fa-envelope': 'Envelope',
            'fas fa-phone': 'Phone',
            'fas fa-calendar': 'Calendar',
            'fas fa-clock': 'Clock',
            'fas fa-heart': 'Heart',
            'fas fa-star': 'Star',
            'fas fa-bookmark': 'Bookmark',
            'fas fa-thumbs-up': 'Thumbs Up',
            'fas fa-thumbs-down': 'Thumbs Down',
            'fas fa-share': 'Share',
            'fas fa-download': 'Download',
            'fas fa-upload': 'Upload',
            'fas fa-print': 'Print',
            'fas fa-trash': 'Trash',
            'fas fa-edit': 'Edit',
            'fas fa-save': 'Save',
            'fas fa-copy': 'Copy',
            'fas fa-cut': 'Cut',
            'fas fa-paste': 'Paste',
            'fas fa-undo': 'Undo',
            'fas fa-redo': 'Redo',
            'fas fa-plus': 'Plus',
            'fas fa-minus': 'Minus',
            'fas fa-times': 'Times',
            'fas fa-check': 'Check',
            'fas fa-arrow-left': 'Arrow Left',
            'fas fa-arrow-right': 'Arrow Right',
            'fas fa-arrow-up': 'Arrow Up',
            'fas fa-arrow-down': 'Arrow Down',
            'fas fa-cog': 'Settings',
            'fas fa-wrench': 'Wrench',
            'fas fa-file': 'File',
            'fas fa-folder': 'Folder',
            'fas fa-image': 'Image',
            'fas fa-video': 'Video',
            'fas fa-music': 'Music',
            'fas fa-microphone': 'Microphone',
            'fas fa-camera': 'Camera',
            'fas fa-map-marker-alt': 'Location',
            'fas fa-shopping-cart': 'Shopping Cart',
            'fas fa-credit-card': 'Credit Card',
            'fas fa-money-bill': 'Money Bill',
            'fas fa-car': 'Car',
            'fas fa-plane': 'Plane',
            'fas fa-train': 'Train',
            'fas fa-bus': 'Bus',
            'fas fa-bicycle': 'Bicycle',
            'fas fa-ship': 'Ship',
            'fas fa-building': 'Building',
            'fas fa-hospital': 'Hospital',
            'fas fa-school': 'School',
            'fas fa-graduation-cap': 'Graduation Cap',
            'fas fa-book': 'Book',
            'fas fa-laptop': 'Laptop',
            'fas fa-mobile-alt': 'Mobile',
            'fas fa-tablet-alt': 'Tablet',
            'fas fa-desktop': 'Desktop',
            'fas fa-tv': 'TV',
            'fas fa-gamepad': 'Gamepad',
            'fas fa-headphones': 'Headphones',
            'fas fa-keyboard': 'Keyboard',
            'fas fa-mouse': 'Mouse',
            'fas fa-battery-full': 'Battery Full',
            'fas fa-wifi': 'WiFi',
            'fas fa-signal': 'Signal',
            'fas fa-bluetooth': 'Bluetooth',
            'fas fa-cloud': 'Cloud',
            'fas fa-database': 'Database',
            'fas fa-server': 'Server',
            'fas fa-code': 'Code',
            'fas fa-bug': 'Bug',
            'fas fa-lock': 'Lock',
            'fas fa-unlock': 'Unlock',
            'fas fa-key': 'Key',
            'fas fa-shield-alt': 'Shield',
            'fas fa-eye': 'Eye',
            'fas fa-eye-slash': 'Eye Slash',

            // Regular Icons
            'far fa-heart': 'Heart (Regular)',
            'far fa-star': 'Star (Regular)',
            'far fa-bookmark': 'Bookmark (Regular)',
            'far fa-thumbs-up': 'Thumbs Up (Regular)',
            'far fa-thumbs-down': 'Thumbs Down (Regular)',
            'far fa-clock': 'Clock (Regular)',
            'far fa-calendar': 'Calendar (Regular)',
            'far fa-envelope': 'Envelope (Regular)',
            'far fa-file': 'File (Regular)',
            'far fa-folder': 'Folder (Regular)',
            'far fa-image': 'Image (Regular)',
            'far fa-user': 'User (Regular)',
            'far fa-eye': 'Eye (Regular)',
            'far fa-comment': 'Comment (Regular)',
            'far fa-comments': 'Comments (Regular)',
            'far fa-bell': 'Bell (Regular)',
            'far fa-hand-point-up': 'Hand Point Up (Regular)',
            'far fa-hand-point-down': 'Hand Point Down (Regular)',
            'far fa-hand-point-left': 'Hand Point Left (Regular)',
            'far fa-hand-point-right': 'Hand Point Right (Regular)',
            'far fa-lightbulb': 'Lightbulb (Regular)',
            'far fa-gem': 'Gem (Regular)',
            'far fa-moon': 'Moon (Regular)',
            'far fa-sun': 'Sun (Regular)',
            'far fa-snowflake': 'Snowflake (Regular)',

            // Brand Icons
            'fab fa-facebook': 'Facebook',
            'fab fa-twitter': 'Twitter',
            'fab fa-instagram': 'Instagram',
            'fab fa-linkedin': 'LinkedIn',
            'fab fa-youtube': 'YouTube',
            'fab fa-github': 'GitHub',
            'fab fa-google': 'Google',
            'fab fa-apple': 'Apple',
            'fab fa-microsoft': 'Microsoft',
            'fab fa-amazon': 'Amazon',
            'fab fa-spotify': 'Spotify',
            'fab fa-paypal': 'PayPal',
            'fab fa-wordpress': 'WordPress',
            'fab fa-dropbox': 'Dropbox',
            'fab fa-skype': 'Skype',
            'fab fa-slack': 'Slack',
            'fab fa-discord': 'Discord',
            'fab fa-whatsapp': 'WhatsApp',
            'fab fa-telegram': 'Telegram',
            'fab fa-tiktok': 'TikTok',
            'fab fa-snapchat': 'Snapchat',
            'fab fa-pinterest': 'Pinterest',
            'fab fa-reddit': 'Reddit',
            'fab fa-twitch': 'Twitch',
            'fab fa-steam': 'Steam',
            'fab fa-android': 'Android',
            'fab fa-firefox': 'Firefox',
            'fab fa-chrome': 'Chrome',
            'fab fa-safari': 'Safari',
            'fab fa-edge': 'Edge',
            'fab fa-opera': 'Opera'
        };

        const iconGroupsContainer = document.getElementById('iconGroups');
        const searchInput = document.getElementById('searchInput');
        const statsElement = document.getElementById('stats');
        const totalIconsCountElement = document.getElementById('totalIconsCount');
        const noResultsElement = document.getElementById('noResults');
        const colorControls = document.querySelector('.color-controls');

        let currentIconColor = 'var(--primary)'; // Default color

        /**
         * Copies the given text to the clipboard and shows a notification.
         * @param {string} text - The text to copy.
         */
        function copyToClipboard(text) {
            document.execCommand('copy'); // Use execCommand for broader compatibility in iframes
            const tempInput = document.createElement('textarea');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            showCopyNotification();
        }

        /**
         * Displays a "copied to clipboard" notification.
         */
        function showCopyNotification() {
            const notification = document.getElementById('copyNotification');
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 2000);
        }

        /**
         * Renders the icons based on the search query and current color.
         * @param {string} searchQuery - The search term.
         */
        function renderIcons(searchQuery = '') {
            const lowerCaseSearchQuery = searchQuery.toLowerCase().trim();
            let filteredIcons = {};

            if (lowerCaseSearchQuery) {
                for (const iconClass in fontAwesomeIcons) {
                    const iconName = fontAwesomeIcons[iconClass];
                    if (iconName.toLowerCase().includes(lowerCaseSearchQuery) || iconClass.toLowerCase().includes(lowerCaseSearchQuery)) {
                        filteredIcons[iconClass] = iconName;
                    }
                }
            } else {
                filteredIcons = { ...fontAwesomeIcons };
            }

            // Group icons by type
            const groupedIcons = {
                'Solid Icons (fas)': {},
                'Regular Icons (far)': {},
                'Brand Icons (fab)': {}
            };

            for (const iconClass in filteredIcons) {
                const iconName = filteredIcons[iconClass];
                if (iconClass.startsWith('fas')) {
                    groupedIcons['Solid Icons (fas)'][iconClass] = iconName;
                } else if (iconClass.startsWith('far')) {
                    groupedIcons['Regular Icons (far)'][iconClass] = iconName;
                } else if (iconClass.startsWith('fab')) {
                    groupedIcons['Brand Icons (fab)'][iconClass] = iconName;
                }
            }

            iconGroupsContainer.innerHTML = ''; // Clear previous icons

            if (Object.keys(filteredIcons).length === 0) {
                noResultsElement.style.display = 'block';
                statsElement.style.display = 'none';
            } else {
                noResultsElement.style.display = 'none';
                statsElement.style.display = 'block';
                totalIconsCountElement.textContent = Object.keys(filteredIcons).length;
                if (lowerCaseSearchQuery) {
                    statsElement.querySelector('h3').textContent = `Search Results for "${searchQuery}"`;
                    statsElement.querySelector('p').innerHTML = `${Object.keys(filteredIcons).length} icons found <a href="#" onclick="clearSearch(); return false;" style="color: var(--primary);">← Clear search</a>`;
                } else {
                    statsElement.querySelector('h3').textContent = `Total Icons Available`;
                    statsElement.querySelector('p').textContent = `${Object.keys(filteredIcons).length} free Font Awesome icons`;
                }


                for (const groupName in groupedIcons) {
                    const icons = groupedIcons[groupName];
                    if (Object.keys(icons).length > 0) {
                        const groupDiv = document.createElement('div');
                        groupDiv.classList.add('icon-group');

                        const groupTitle = document.createElement('h2');
                        groupTitle.classList.add('group-title');
                        groupTitle.innerHTML = `${groupName} <span class="count">${Object.keys(icons).length}</span>`;
                        groupDiv.appendChild(groupTitle);

                        const iconsGrid = document.createElement('div');
                        iconsGrid.classList.add('icons-grid');

                        for (const iconClass in icons) {
                            const iconName = icons[iconClass];
                            const iconItem = document.createElement('div');
                            iconItem.classList.add('icon-item');
                            iconItem.setAttribute('onclick', `copyToClipboard('${iconClass}')`);

                            const iconElement = document.createElement('i');
                            iconElement.classList.add(...iconClass.split(' '));
                            iconElement.style.color = currentIconColor; // Apply current color
                            iconItem.appendChild(iconElement);

                            const nameDiv = document.createElement('div');
                            nameDiv.classList.add('icon-name');
                            nameDiv.textContent = iconName;
                            iconItem.appendChild(nameDiv);

                            const classDiv = document.createElement('div');
                            classDiv.classList.add('icon-class');
                            classDiv.textContent = iconClass;
                            iconItem.appendChild(classDiv);

                            iconsGrid.appendChild(iconItem);
                        }
                        groupDiv.appendChild(iconsGrid);
                        iconGroupsContainer.appendChild(groupDiv);
                    }
                }
            }
        }

        /**
         * Clears the search input and re-renders all icons.
         */
        function clearSearch() {
            searchInput.value = '';
            renderIcons();
        }

        // Event listener for search input
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                renderIcons(this.value);
            }, 300); // Debounce search input
        });

        // Event listener for color controls
        colorControls.addEventListener('click', function(event) {
            const targetOption = event.target.closest('.color-option');
            if (targetOption) {
                // Remove active class from all options
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.remove('active');
                });
                // Add active class to the clicked option
                targetOption.classList.add('active');

                const color = targetOption.dataset.color;
                switch (color) {
                    case 'default':
                        currentIconColor = 'var(--primary)';
                        break;
                    case 'red':
                        currentIconColor = '#F44336';
                        break;
                    case 'green':
                        currentIconColor = '#4CAF50';
                        break;
                    case 'yellow':
                        currentIconColor = '#FFC107';
                        break;
                    case 'magenta':
                        currentIconColor = 'var(--secondary)';
                        break;
                    default:
                        currentIconColor = 'var(--primary)';
                }
                // Re-render icons with the new color
                renderIcons(searchInput.value);
            }
        });

        // Initial render of icons when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            renderIcons();
        });
    </script>
</body>
</html>

    </div>
  </div>
</div>
<script>
function showTab(tabId) {
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.style.display = 'none';
  });
  document.getElementById(tabId).style.display = 'block';
}
</script>

</body>
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

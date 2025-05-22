<?php
require_once 'core/config.php';
require_once 'core/auth.php';
require_once 'core/widget_registry.php';

// Require login
Auth::requireLogin();

$user_id = Auth::getCurrentUserId();
$widget_registry = WidgetRegistry::getInstance();
$all_widgets = $widget_registry->get_all_widgets();
$user_widgets = $widget_registry->get_user_widgets($user_id);

// Get user's widget layout preferences
$stmt = $db->prepare("SELECT layout FROM user_preferences WHERE user_id = ?");
$stmt->execute([$user_id]);
$layout = $stmt->fetchColumn();

if (!$layout) {
    // Default layout
    $layout = json_encode([
        'dashboard' => array_keys($user_widgets)
    ]);
}

$layout = json_decode($layout, true);

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_layout') {
        // Save the layout
        $new_layout = json_decode($_POST['layout'], true);
        
        if ($new_layout) {
            $stmt = $db->prepare("
                INSERT INTO user_preferences (user_id, layout) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE layout = VALUES(layout)
            ");
            
            if ($stmt->execute([$user_id, json_encode($new_layout)])) {
                $message = "Dashboard layout saved successfully.";
                $layout = $new_layout;
            } else {
                $message = "Failed to save dashboard layout.";
            }
        } else {
            $message = "Invalid layout data.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add_widget') {
        // Add a widget to the user's dashboard
        $widget_id = $_POST['widget_id'];
        
        if (isset($all_widgets[$widget_id])) {
            // Add widget to user permissions
            $stmt = $db->prepare("
                INSERT INTO user_widget_permissions (user_id, widget_id, can_view, can_edit) 
                VALUES (?, ?, 1, 0)
                ON DUPLICATE KEY UPDATE can_view = 1
            ");
            
            if ($stmt->execute([$user_id, $widget_id])) {
                // Add widget to layout
                if (!isset($layout['dashboard'])) {
                    $layout['dashboard'] = [];
                }
                
                if (!in_array($widget_id, $layout['dashboard'])) {
                    $layout['dashboard'][] = $widget_id;
                    
                    // Save the updated layout
                    $stmt = $db->prepare("
                        INSERT INTO user_preferences (user_id, layout) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE layout = VALUES(layout)
                    ");
                    
                    if ($stmt->execute([$user_id, json_encode($layout)])) {
                        $message = "Widget added to dashboard.";
                        // Refresh user widgets
                        $user_widgets = $widget_registry->get_user_widgets($user_id);
                    } else {
                        $message = "Failed to update dashboard layout.";
                    }
                } else {
                    $message = "Widget is already on your dashboard.";
                }
            } else {
                $message = "Failed to add widget permission.";
            }
        } else {
            $message = "Widget not found.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'remove_widget') {
        // Remove a widget from the user's dashboard
        $widget_id = $_POST['widget_id'];
        
        if (isset($layout['dashboard'])) {
            $index = array_search($widget_id, $layout['dashboard']);
            
            if ($index !== false) {
                // Remove widget from layout
                array_splice($layout['dashboard'], $index, 1);
                
                // Save the updated layout
                $stmt = $db->prepare("
                    INSERT INTO user_preferences (user_id, layout) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE layout = VALUES(layout)
                ");
                
                if ($stmt->execute([$user_id, json_encode($layout)])) {
                    $message = "Widget removed from dashboard.";
                } else {
                    $message = "Failed to update dashboard layout.";
                }
            } else {
                $message = "Widget is not on your dashboard.";
            }
        } else {
            $message = "Invalid dashboard layout.";
        }
    }
}

// Get available widgets (widgets the user doesn't already have)
$available_widgets = array_diff_key($all_widgets, $user_widgets);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customize Dashboard - MPSM Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-left">
                <h1>Customize Dashboard</h1>
            </div>
            <div class="header-right">
                <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </header>
        
        <main class="app-main">
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <section class="customize-section">
                <h2>Your Dashboard Layout</h2>
                <p>Drag and drop widgets to rearrange them. Click "Save Layout" when you're done.</p>
                
                <div id="dashboard-preview" class="dashboard-preview">
                    <?php 
                    if (isset($layout['dashboard'])) {
                        foreach ($layout['dashboard'] as $widget_id) {
                            if (isset($user_widgets[$widget_id])) {
                                $widget = $user_widgets[$widget_id];
                                echo '<div class="widget-item" data-widget-id="' . htmlspecialchars($widget_id) . '">';
                                echo '<h3>' . htmlspecialchars($widget['name']) . '</h3>';
                                echo '<p>' . htmlspecialchars($widget['description']) . '</p>';
                                echo '<div class="widget-actions">';
                                echo '<form method="post" style="display: inline;">';
                                echo '<input type="hidden" name="action" value="remove_widget">';
                                echo '<input type="hidden" name="widget_id" value="' . htmlspecialchars($widget_id) . '">';
                                echo '<button type="submit" class="btn btn-sm btn-danger">Remove</button>';
                                echo '</form>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
                
                <form id="save-layout-form" method="post">
                    <input type="hidden" name="action" value="save_layout">
                    <input type="hidden" id="layout-data" name="layout" value="">
                    <button type="submit" class="btn btn-primary">Save Layout</button>
                </form>
            </section>
            
            <section class="customize-section">
                <h2>Available Widgets</h2>
                <p>Add these widgets to your dashboard:</p>
                
                <div class="available-widgets">
                    <?php if (empty($available_widgets)): ?>
                        <p>You have all available widgets on your dashboard.</p>
                    <?php else: ?>
                        <?php foreach ($available_widgets as $widget_id => $widget): ?>
                            <div class="available-widget">
                                <h3><?php echo htmlspecialchars($widget['name']); ?></h3>
                                <p><?php echo htmlspecialchars($widget['description']); ?></p>
                                <form method="post">
                                    <input type="hidden" name="action" value="add_widget">
                                    <input type="hidden" name="widget_id" value="<?php echo htmlspecialchars($widget_id); ?>">
                                    <button type="submit" class="btn btn-primary">Add to Dashboard</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        
        <footer class="app-footer">
            <p>&copy; <?php echo date('Y'); ?> MPSM Dashboard. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script src="assets/js/customize.js"></script>
</body>
</html>

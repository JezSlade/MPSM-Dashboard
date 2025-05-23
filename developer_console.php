<?php
require_once 'core/config.php';
require_once 'core/auth.php';
require_once 'core/widget_registry.php';
require_once 'core/api.php';

// Check if user has developer permissions
if (!Auth::isDeveloper()) {
    header('Location: index.php');
    exit;
}

$widget_registry = WidgetRegistry::getInstance();
$all_widgets = $widget_registry->get_all_widgets();

// Get message from URL parameter
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Developer Console - MPSM Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-left">
                <h1>Developer Console</h1>
            </div>
            <div class="header-right">
                <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </header>
        
        <main class="app-main">
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="tabs">
                <div class="tab active" data-tab="widgets">Widgets</div>
                <div class="tab" data-tab="api">API Endpoints</div>
                <div class="tab" data-tab="docs">Documentation</div>
            </div>
            
            <div class="tab-content active" id="widgets-tab">
                <div class="widget-actions" style="margin-bottom: 20px;">
                    <a href="developer_console_wizard.php" class="btn btn-primary">Create New Widget</a>
                </div>
                
                <h2>Registered Widgets</h2>
                <div class="widget-list">
                    <?php if (empty($all_widgets)): ?>
                        <p>No widgets registered yet.</p>
                    <?php else: ?>
                        <?php foreach ($all_widgets as $widget_id => $widget): ?>
                            <div class="widget-item">
                                <div class="widget-header">
                                    <h3><?php echo htmlspecialchars($widget['name']); ?></h3>
                                    <div class="widget-actions">
                                        <a href="widget_edit.php?id=<?php echo urlencode($widget_id); ?>" class="btn btn-sm">Edit</a>
                                        <form method="post" action="widget_delete.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this widget?');">
                                            <input type="hidden" name="widget_id" value="<?php echo htmlspecialchars($widget_id); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <p><strong>ID:</strong> <?php echo htmlspecialchars($widget_id); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($widget['description']); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($widget['type']); ?></p>
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($widget['class_name']); ?></p>
                                <p><strong>File:</strong> <?php echo htmlspecialchars($widget['file_path']); ?></p>
                                <?php if (!empty($widget['required_permissions'])): ?>
                                    <p><strong>Required Permissions:</strong> <?php echo htmlspecialchars($widget['required_permissions']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($widget['config'])): ?>
                                    <p><strong>Configuration:</strong></p>
                                    <pre><?php echo htmlspecialchars(json_encode($widget['config'], JSON_PRETTY_PRINT)); ?></pre>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tab-content" id="api-tab">
                <h2>API Endpoints</h2>
                <p>These are the available API endpoints from the endpoints.json file:</p>
                
                <div class="endpoint-list">
                    <?php 
                    $endpoints = $api_client->get_all_endpoints();
                    if (empty($endpoints)): 
                    ?>
                        <p>No API endpoints found. Make sure the endpoints.json file is properly configured.</p>
                    <?php else: ?>
                        <?php foreach ($endpoints as $path => $methods): ?>
                            <div class="endpoint-item">
                                <span class="endpoint-path"><?php echo htmlspecialchars($path); ?></span>
                                <span class="endpoint-methods">(<?php echo htmlspecialchars(implode(', ', array_keys($methods))); ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <h2>Test API Connection</h2>
                <p>Click the button below to test the API connection:</p>
                <button id="test-api" class="btn btn-primary">Test API Connection</button>
                <div id="api-result" style="margin-top: 10px;"></div>
            </div>
            
            <div class="tab-content" id="docs-tab">
                <h2>Widget Development Documentation</h2>
                <p>Learn how to create and customize widgets for the MPSM Dashboard:</p>
                
                <ul>
                    <li><a href="widget_development_docs.php">Widget Development Guide</a></li>
                    <li><a href="developer_console_wizard.php">Widget Creation Wizard</a></li>
                </ul>
                
                <h3>Quick Reference</h3>
                <p><strong>Widget Types:</strong></p>
                <ul>
                    <li><strong>API Widget:</strong> Fetches data from API endpoints and displays it.</li>
                    <li><strong>Static Widget:</strong> Displays static or simple dynamic content.</li>
                    <li><strong>Custom Code Widget:</strong> Allows custom PHP, HTML, JavaScript, and CSS code.</li>
                </ul>
                
                <p><strong>Widget Lifecycle:</strong></p>
                <ol>
                    <li>Initialization: The widget is instantiated with its configuration.</li>
                    <li>Data Fetching: The fetch_data() method is called to retrieve data.</li>
                    <li>Rendering: The render() method is called to generate HTML.</li>
                    <li>Interaction: Users can interact with the widget (refresh, configure, etc.).</li>
                </ol>
            </div>
        </main>
        
        <footer class="app-footer">
            <p>&copy; <?php echo date('Y'); ?> MPSM Dashboard. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="assets/js/developer.js"></script>
</body>
</html>

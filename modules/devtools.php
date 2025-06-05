<?php
if (!has_permission('view_devtools')) {
    echo "<p class='error'>Access denied.</p>";
    exit;
}

// Load .env file
$env_file = BASE_PATH . '.env';
$env_vars = [];
if (file_exists($env_file)) {
    $env_content = file_get_contents($env_file);
    $lines = explode("\n", $env_content);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[$key] = $value;
        }
    }
}

// Load CSS variables (simulated by reading styles.css)
$css_file = BASE_PATH . 'styles.css';
$css_content = file_get_contents($css_file);
$primary_color = '#00cec9'; // Default teal
$depth_intensity = '8px'; // Default depth
if (preg_match('/--primary-color:\s*(#[0-9a-fA-F]{6})/', $css_content, $match)) {
    $primary_color = $match[1];
}
if (preg_match('/--depth-intensity:\s*(\d+px)/', $css_content, $match)) {
    $depth_intensity = $match[1];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_env'])) {
            $db_name = trim($_POST['DB_NAME'] ?? '');
            $debug_mode = isset($_POST['DEBUG_MODE']) ? 'true' : 'false';
            if (empty($db_name)) {
                throw new Exception("Database name cannot be empty.");
            }
            $new_env_content = "DB_HOST=localhost\nDB_USER={$env_vars['DB_USER']}\nDB_PASS={$env_vars['DB_PASS']}\nDB_NAME=$db_name\nDEBUG_MODE=$debug_mode\n";
            if (!file_put_contents($env_file, $new_env_content)) {
                throw new Exception("Failed to write to .env file. Check permissions.");
            }
            echo "<p class='success'>.env updated successfully! 🔧</p>";
        } elseif (isset($_POST['update_css'])) {
            $new_color = trim($_POST['primary_color'] ?? '');
            $new_depth = trim($_POST['depth_intensity'] ?? '');
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $new_color)) {
                throw new Exception("Invalid color format. Use #RRGGBB.");
            }
            if (!preg_match('/^\d+px$/', $new_depth)) {
                throw new Exception("Invalid depth format. Use a number followed by 'px'.");
            }
            $css_content = preg_replace('/--primary-color:\s*#[0-9a-fA-F]{6}/', "--primary-color: $new_color", $css_content);
            $css_content = preg_replace('/--depth-intensity:\s*\d+px/', "--depth-intensity: $new_depth", $css_content);
            if (!file_put_contents($css_file, $css_content)) {
                throw new Exception("Failed to write to styles.css. Check permissions.");
            }
            echo "<p class='success'>CSS updated successfully! 🎨</p>";
        } elseif (isset($_POST['clear_cache'])) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['user_id'] = 1; // Re-authenticate as admin
            echo "<p class='success'>Session cache cleared! 🗑️</p>";
        } elseif (isset($_POST['toggle_debug'])) {
            $debug_mode = isset($_POST['debug_enabled']) ? 'true' : 'false';
            $new_env_content = str_replace("DEBUG_MODE={$env_vars['DEBUG_MODE']}", "DEBUG_MODE=$debug_mode", file_get_contents($env_file));
            file_put_contents($env_file, $new_env_content);
            echo "<p class='success'>Debug mode updated! 🐞</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<h2>DevTools ⚙️</h2>
<p>Developer settings and tools for MPSM:</p>

<div class="devtools-section">
    <h3>Environment Variables 🔧</h3>
    <form method="POST" class="permissions-form">
        <label>Database Name (DB_NAME):</label>
        <input type="text" name="DB_NAME" value="<?php echo htmlspecialchars($env_vars['DB_NAME'] ?? ''); ?>" required>
        <label>Debug Mode (DEBUG_MODE):</label>
        <input type="checkbox" name="DEBUG_MODE" <?php echo ($env_vars['DEBUG_MODE'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
        <button type="submit" name="update_env">💾 Save .env</button>
    </form>
</div>

<div class="devtools-section">
    <h3>Theme Customization 🎨</h3>
    <form method="POST" class="permissions-form">
        <label>Primary Color (--primary-color):</label>
        <input type="color" name="primary_color" value="<?php echo $primary_color; ?>">
        <label>Depth Intensity (--depth-intensity):</label>
        <input type="text" name="depth_intensity" value="<?php echo $depth_intensity; ?>" placeholder="e.g., 8px">
        <button type="submit" name="update_css">💾 Save CSS</button>
    </form>
</div>

<div class="devtools-section">
    <h3>Debug Settings 🐞</h3>
    <form method="POST" class="permissions-form">
        <label>Enable Debug Mode:</label>
        <input type="checkbox" name="debug_enabled" <?php echo ($env_vars['DEBUG_MODE'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
        <button type="submit" name="toggle_debug">🔍 Toggle Debug</button>
    </form>
    <form method="POST" class="permissions-form">
        <button type="submit" name="clear_cache">🗑️ Clear Session Cache</button>
    </form>
</div>

<div class="devtools-section">
    <h3>Module Management 🛠️</h3>
    <ul>
        <li><a href="create_module.php">➕ Add Module</a> - Create a new module with JSON config.</li>
    </ul>
</div>
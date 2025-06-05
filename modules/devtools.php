<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include dependencies
require_once BASE_PATH . 'db.php'; // For load_env()
require_once BASE_PATH . 'functions.php'; // For has_permission()

// Check permissions
if (!function_exists('has_permission') || !has_permission('view_devtools')) {
    echo "<p class='text-red-500 p-4'>Access denied.</p>";
    exit;
}

// Load environment variables using the shared load_env() from db.php
$env_vars = load_env(BASE_PATH);

$css_file = BASE_PATH . 'styles.css';
$css_content = file_exists($css_file) ? file_get_contents($css_file) : '';
$primary_color = preg_match('/--primary-color:\s*(#[0-9a-fA-F]{6})/', $css_content, $match) ? $match[1] : '#00cec9';
$depth_intensity = preg_match('/--depth-intensity:\s*(\d+px)/', $css_content, $match) ? $match[1] : '8px';
$neon_underglow = preg_match('/--neon-underglow:\s*(#[0-9a-fA-F]{6})/', $css_content, $match) ? $match[1] : '#00FFFF';
$smoke_opacity = preg_match('/--smoke-opacity:\s*(\d*\.?\d+)/', $css_content, $match) ? $match[1] : '0.7';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_env'])) {
            $db_name = trim($_POST['DB_NAME'] ?? '');
            $debug_mode = isset($_POST['DEBUG_MODE']) ? 'true' : 'false';
            if (empty($db_name)) {
                throw new Exception("Database name cannot be empty.");
            }
            $new_env = "DB_HOST=localhost\nDB_USER={$env_vars['DB_USER']}\nDB_PASS={$env_vars['DB_PASS']}\nDB_NAME=$db_name\nDEBUG_MODE=$debug_mode\n";
            if (!file_put_contents(BASE_PATH . '.env', $new_env)) {
                throw new Exception("Failed to write to .env.");
            }
            echo "<p class='text-green-500 p-2'>.env updated!</p>";
        } elseif (isset($_POST['update_css'])) {
            $new_color = trim($_POST['primary_color'] ?? '');
            $new_depth = trim($_POST['depth_intensity'] ?? '');
            $new_neon = trim($_POST['neon_underglow'] ?? '');
            $new_opacity = trim($_POST['smoke_opacity'] ?? '');
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $new_color)) {
                throw new Exception("Invalid primary color format.");
            }
            if (!preg_match('/^\d+px$/', $new_depth)) {
                throw new Exception("Invalid depth format.");
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $new_neon)) {
                throw new Exception("Invalid neon underglow format.");
            }
            if (!preg_match('/^\d*\.?\d+$/', $new_opacity) || $new_opacity > 1 || $new_opacity < 0) {
                throw new Exception("Invalid opacity (0-1).");
            }
            $css_content = preg_replace('/--primary-color:\s*#[0-9a-fA-F]{6}/', "--primary-color: $new_color", $css_content);
            $css_content = preg_replace('/--depth-intensity:\s*\d+px/', "--depth-intensity: $new_depth", $css_content);
            $css_content = preg_replace('/--neon-underglow:\s*#[0-9a-fA-F]{6}/', "--neon-underglow: $new_neon", $css_content);
            $css_content = preg_replace('/--smoke-opacity:\s*\d*\.?\d+/', "--smoke-opacity: $new_opacity", $css_content);
            if (!file_put_contents($css_file, $css_content)) {
                throw new Exception("Failed to write to styles.css.");
            }
            echo "<p class='text-green-500 p-2'>CSS updated!</p>";
        } elseif (isset($_POST['run_tests'])) {
            $api_result = test_api();
            $db_result = test_database();
            $php_result = test_php();
            $env_result = test_environment();
            echo "<div class='glass p-4 border border-gray-800 rounded mt-4'><h3 class='text-xl text-cyan-neon'>Test Results</h3><ul class='mt-2 space-y-2'>";
            echo "<li>API Test: " . ($api_result ? "<span class='text-green-500'>Pass</span>" : "<span class='text-red-500'>Fail</span>") . "</li>";
            echo "<li>Database Test: " . ($db_result ? "<span class='text-green-500'>Pass</span>" : "<span class='text-red-500'>Fail</span>") . "</li>";
            echo "<li>PHP Test: " . ($php_result ? "<span class='text-green-500'>Pass</span>" : "<span class='text-red-500'>Fail</span>") . "</li>";
            echo "<li>Environment Test: " . ($env_result ? "<span class='text-green-500'>Pass</span>" : "<span class='text-red-500'>Fail</span>") . "</li></ul></div>";
        }
    } catch (Exception $e) {
        echo "<p class='text-red-500 p-2'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function test_api() {
    $env = load_env(BASE_PATH);
    if (isset($env['API_URL']) && file_exists(BASE_PATH . 'working.php')) {
        return true; // Mock success; replace with actual API call
    }
    return false;
}

function test_database() {
    $env = load_env(BASE_PATH);
    if (isset($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME'])) {
        $conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
        return $conn->connect_error === null;
    }
    return false;
}

function test_php() {
    return version_compare(PHP_VERSION, '7.4.0', '>=');
}

function test_environment() {
    return file_exists(BASE_PATH . '.env') && is_writable(BASE_PATH . '.env');
}
?>

<h2 class="text-2xl text-cyan-neon mb-4 flex items-center">
    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
    </svg>
    DevTools
</h2>
<p class="mb-4">Developer settings and tools for MPSM.</p>

<div class="space-y-6">
    <div class="glass p-4 border border-gray-800 rounded">
        <h3 class="text-xl text-magenta-neon flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            Environment Variables
        </h3>
        <form method="POST" class="mt-4 space-y-4">
            <div>
                <label class="block text-gray-300">Database Name (DB_NAME):</label>
                <input type="text" name="DB_NAME" value="<?php echo htmlspecialchars($env_vars['DB_NAME'] ?? ''); ?>" required class="w-full p-2 bg-black-smoke text-white border border-gray-700 rounded">
            </div>
            <div>
                <label class="block text-gray-300">Debug Mode (DEBUG_MODE):</label>
                <input type="checkbox" name="DEBUG_MODE" <?php echo ($env_vars['DEBUG_MODE'] ?? 'false') === 'true' ? 'checked' : ''; ?> class="mr-2">
            </div>
            <button type="submit" name="update_env" class="bg-black-smoke text-yellow-neon p-2 rounded border border-gray-700 hover:bg-gray-700">Save</button>
        </form>
    </div>

    <div class="glass p-4 border border-gray-800 rounded">
        <h3 class="text-xl text-yellow-neon flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
            Theme Customization
        </h3>
        <form method="POST" class="mt-4 space-y-4">
            <div>
                <label class="block text-gray-300">Primary Color (--primary-color):</label>
                <input type="color" name="primary_color" value="<?php echo $primary_color; ?>" class="w-full p-2 bg-black-smoke text-white border border-gray-700 rounded">
            </div>
            <div>
                <label class="block text-gray-300">Depth Intensity (--depth-intensity):</label>
                <input type="text" name="depth_intensity" value="<?php echo $depth_intensity; ?>" placeholder="e.g., 8px" class="w-full p-2 bg-black-smoke text-white border border-gray-700 rounded">
            </div>
            <div>
                <label class="block text-gray-300">Neon Underglow (--neon-underglow):</label>
                <input type="color" name="neon_underglow" value="<?php echo $neon_underglow; ?>" class="w-full p-2 bg-black-smoke text-white border border-gray-700 rounded">
            </div>
            <div>
                <label class="block text-gray-300">Smoke Opacity (--smoke-opacity):</label>
                <input type="number" name="smoke_opacity" value="<?php echo $smoke_opacity; ?>" step="0.1" min="0" max="1" class="w-full p-2 bg-black-smoke text-white border border-gray-700 rounded">
            </div>
            <button type="submit" name="update_css" class="bg-black-smoke text-yellow-neon p-2 rounded border border-gray-700 hover:bg-gray-700">Save</button>
        </form>
    </div>

    <div class="glass p-4 border border-gray-800 rounded">
        <h3 class="text-xl text-cyan-neon flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Debug Settings
        </h3>
        <form method="POST" class="mt-4 space-y-4">
            <div>
                <label class="block text-gray-300">Enable Debug Mode:</label>
                <input type="checkbox" name="debug_enabled" <?php echo ($env_vars['DEBUG_MODE'] ?? 'false') === 'true' ? 'checked' : ''; ?> class="mr-2">
                <button type="submit" name="toggle_debug" class="bg-black-smoke text-magenta-neon p-2 rounded border border-gray-700 hover:bg-gray-700">Toggle</button>
            </div>
            <button type="submit" name="clear_cache" class="mt-2 bg-black-smoke text-magenta-neon p-2 rounded border border-gray-700 hover:bg-gray-700">Clear Cache</button>
        </form>
    </div>

    <div class="glass p-4 border border-gray-800 rounded">
        <h3 class="text-xl text-yellow-neon flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Module Management
        </h3>
        <ul class="mt-4 space-y-2">
            <li><a href="create_module.php" class="text-magenta-neon hover:text-magenta-300">Add Module</a> - Create a new module with JSON config.</li>
        </ul>
    </div>

    <div class="glass p-4 border border-gray-800 rounded">
        <h3 class="text-xl text-cyan-neon flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            Run Tests
        </h3>
        <form method="POST" class="mt-4">
            <button type="submit" name="run_tests" class="bg-black-smoke text-yellow-neon p-2 rounded border border-gray-700 hover:bg-gray-700">Run All Tests</button>
        </form>
    </div>
</div>
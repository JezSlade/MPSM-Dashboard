<?php
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once BASE_PATH . 'db.php';

if ($argc < 2) die("Usage: php create_module.php <json_config_file>\n");

$config = json_decode(file_get_contents($argv[1]), true);
if (!$config) die("Invalid JSON config\n");

$module_name = $config['name'];
$permission = $config['permission'];
$description = $config['description'];

// Create module file
$module_content = <<<PHP
<?php
if (!has_permission('$permission')) {
    echo "<p class='error'>Access denied.</p>";
    exit;
}
?>
<h2>$description 📊</h2>
<p>Module content goes here...</p>
PHP;
file_put_contents(BASE_PATH . "modules/$module_name.php", $module_content);
if (!file_exists(BASE_PATH . "modules/$module_name.php")) die("Failed to create module file\n");

// Update setup.php
$setup_content = file_get_contents(BASE_PATH . "setup.php");
$setup_content .= "\nexecute_query(\$db, \"INSERT IGNORE INTO permissions (name) VALUES ('$permission')\");";
file_put_contents(BASE_PATH . "setup.php", $setup_content);

// Update index.php
$index_content = file_get_contents(BASE_PATH . "index.php");
$index_content = preg_replace('/\$modules = \[\s*(.*?)\];/s', "\$modules = [\n        '$module_name' => '$permission',\n        $1];", $index_content);
file_put_contents(BASE_PATH . "index.php", $index_content);

echo "Module $module_name created successfully. Run setup.php to apply database changes.\n";
?>
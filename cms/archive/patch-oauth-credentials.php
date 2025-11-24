<?php
/**
 * Patch OAuth Credentials in config.php
 *
 * Updates config.php to use correct credentials from .env
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    die("ERROR: config.php not found\n");
}

echo "=== OAUTH CREDENTIALS PATCH ===\n\n";

// Read current config
$content = file_get_contents($configFile);

// Backup
$backup = $configFile . '.backup.' . date('Y-m-d-His');
copy($configFile, $backup);
echo "Backup created: $backup\n\n";

// Replace OLD credentials with NEW credentials
$replacements = [
    // CLIENT_ID
    "'G0bYZyS9bjOjx6oRv-MQ6vGF3VkVTvZy5hzhVEOWQs8'" => "'9AT9j4UoU2BgLEqmiYCz'",

    // CLIENT_SECRET
    "'wFFXo9TQvvuCGVBb0_MMNZkZP5YuTPJqe_eRRdHCPQo'" => "'9gTbAKBCZe1ftYQbLbq9'",

    // USERNAME
    "'rbd.connect@resolutionsbydesign.com'" => "'dashboard'",

    // PASSWORD
    "'connect.RBD24!'" => "'d@\$hpa\$\$2024'",

    // SCOPE
    "'rbd.connect@resolutionsbydesign.com MpsMonitorApiAll'" => "'account'"
];

$changesMade = 0;
foreach ($replacements as $old => $new) {
    $oldContent = $content;
    $content = str_replace($old, $new, $content);
    if ($content !== $oldContent) {
        $changesMade++;
        echo "✓ Replaced: $old -> $new\n";
    }
}

if ($changesMade === 0) {
    echo "\nNO CHANGES NEEDED - Credentials already up to date\n";
    exit(0);
}

// Write updated config
file_put_contents($configFile, $content);

echo "\n✓ SUCCESS: Updated $changesMade credential(s)\n";
echo "\nNext step: Test OAuth authentication\n";
echo "URL: /cms/api/force-refresh-start.php\n";

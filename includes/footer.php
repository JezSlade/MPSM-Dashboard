<?php declare(strict_types=1);
// includes/footer.php
// -------------------------------------------------------------------
// Renders the global footer, including the deploy version (from
// documentation/backup.deploy.yml) and any copyright.
// -------------------------------------------------------------------

// Attempt to read “name:” from your deploy YAML
$deployYml = __DIR__ . '/../documentation/backup.deploy.yml';
$version   = 'unknown';
if (is_readable($deployYml)) {
    $lines = file($deployYml, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match('/^name:\s*(.+)$/', trim($line), $m)) {
            $version = trim($m[1]);
            break;
        }
    }
}
?>
<footer class="bg-gray-800 text-gray-400 text-sm py-3 text-center">
  <div>
    Version: <span class="font-mono"><?= htmlspecialchars($version, ENT_QUOTES) ?></span>
  </div>
  <div class="mt-1">
    &copy; <?= date('Y') ?> Your Company Name
  </div>
</footer>

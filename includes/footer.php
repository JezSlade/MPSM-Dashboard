<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------
?>
</main>
<footer class="glass-footer">
  <small>&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?></small>
</footer>
</div>
<script src="<?= APP_BASE_URL ?>public/js/theme.js"></script>
</body>
</html>

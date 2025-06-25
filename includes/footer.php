<?php
// /includes/footer.php — Shared footer for all views, adds global JS and closes the document
?>
<footer class="app-footer">
  <div class="footer-left">
    &copy; <?= date('Y') ?> <?= APP_NAME ?>
  </div>
  <div class="footer-right">
    Version <?= getenv('APP_VERSION') ?: '0.0.0' ?>
  </div>
</footer>

<!-- JS libraries -->
<script src="https://unpkg.com/feather-icons"></script>

<!-- Main JS -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Ensure all <i data-feather="..."> tags are replaced by SVGs
    if (window.feather) {
      feather.replace();
    }
  });
</script>

</body>
</html>

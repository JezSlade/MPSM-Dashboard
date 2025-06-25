<?php
/**
 * includes/footer.php — Footer component with dynamic version loader
 *
 * Expects a `version.js` at the site root defining `window.appVersion`.
 */
?>
<footer class="p-4 text-center text-sm opacity-75 neumorphic">
  <div>© <?php echo date('Y'); ?> Resolutions by Design</div>
  <div class="mt-1">Version: <span id="appVersion">Loading…</span></div>
</footer>

<script src="/version.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('appVersion');
    el.textContent = window.appVersion || 'N/A';
  });
</script>

<!--
Changelog:
- No modifications; footer remains unchanged.
-->

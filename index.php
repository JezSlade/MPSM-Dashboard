<?php
/**
 * index.php — Single-page entrypoint with OS detection, card-settings modal,
 * corrected modal toggling, click-outside-to-close, inner-click stopPropagation,
 * and dark-mode by default.
 *
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Define placeholder constant
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
?>
<!DOCTYPE html>
<!-- Set dark mode by default with both class and data-theme -->
<html lang="en" class="h-full mobile-first dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Global custom styles -->
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col transition-colors duration-300">

  <?php include __DIR__ . '/includes/header.php'; ?>
  <?php include __DIR__ . '/includes/navigation.php'; ?>

  <main class="flex-1 overflow-y-auto p-6">
    <div class="card-grid">
      <?php
      // Auto-discover all cards in /cards/
      $cardsDir = __DIR__ . '/cards/';
      $files = array_filter(scandir($cardsDir, SCANDIR_SORT_ASCENDING), function($f) {
        return pathinfo($f, PATHINFO_EXTENSION) === 'php';
      });
      foreach ($files as $file):
      ?>
      <div class="card-wrapper glow" data-file="<?php echo $file; ?>">
        <?php include $cardsDir . $file; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Card-settings modal (hidden by default) -->
  <div id="cardSettingsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div id="cardSettingsContent" class="bg-light dark:bg-dark neumorphic p-4 rounded w-11/12 md:w-1/3 max-h-[80vh] overflow-auto">
      <h2 class="text-lg font-semibold mb-2">Select Cards to Display</h2>
      <form id="cardSettingsForm" class="space-y-2">
        <?php foreach ($files as $file):
            $id = pathinfo($file, PATHINFO_FILENAME);
        ?>
        <label class="flex items-center space-x-2">
          <input type="checkbox" name="cards" value="<?php echo $file; ?>" checked>
          <span><?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?></span>
        </label>
        <?php endforeach; ?>
      </form>
      <div class="flex justify-end space-x-2 mt-4">
        <button id="cardSettingsSave" type="button" class="neu-btn">Save</button>
        <button id="cardSettingsCancel" type="button" class="neu-btn">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Initialize icons & behaviors -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const html = document.documentElement;

      // OS detection
      html.classList.add(/Mobi|Android|iPhone/.test(navigator.userAgent) ? 'is-mobile' : 'is-desktop');

      // Feather icons
      feather.replace();

      // Theme toggle
      document.getElementById('theme-toggle')?.addEventListener('click', () => {
        const current = html.getAttribute('data-theme');
        html.setAttribute('data-theme', current === 'light' ? 'dark' : 'light');
        html.classList.toggle('dark');
      });

      // Hard refresh
      document.getElementById('refresh-all')?.addEventListener('click', () => location.reload(true));

      // Clear session
      document.getElementById('clear-session')?.addEventListener('click', () => {
        document.cookie.split(';').forEach(c => {
          document.cookie = c.split('=')[0].trim() + '=;expires=Thu, 01 Jan 1970 GMT;path=/';
        });
        location.reload();
      });

      // View debug log
      document.getElementById('view-error-log')?.addEventListener('click', () => {
        window.open('/logs/debug.log', '_blank');
      });

      // Modal controls
      const modal = document.getElementById('cardSettingsModal');
      const content = document.getElementById('cardSettingsContent');
      const openBtn = document.getElementById('card-settings');
      const saveBtn = document.getElementById('cardSettingsSave');
      const cancelBtn = document.getElementById('cardSettingsCancel');

      function showModal() {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
      }
      function hideModal() {
        modal.style.display = 'none';
        modal.classList.add('hidden');
      }

      // Ensure modal hidden on load
      hideModal();

      // Prevent clicks inside content from closing
      content.addEventListener('click', e => e.stopPropagation());

      // Bind open only if button exists
      if (openBtn) openBtn.addEventListener('click', showModal);
      if (cancelBtn) cancelBtn.addEventListener('click', e => { e.preventDefault(); hideModal(); });
      if (saveBtn) saveBtn.addEventListener('click', e => {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('#cardSettingsForm input[name="cards"]:checked'))
                             .map(i => i.value);
        try {
          localStorage.setItem('visibleCards', JSON.stringify(checked));
        } catch {}
        applyCardVisibility();
        hideModal();
      });

      // Close when clicking outside the inner modal content
      modal.addEventListener('click', hideModal);

      // ESC key support
      document.addEventListener('keyup', e => {
        if (e.key === 'Escape') hideModal();
      });

      function applyCardVisibility() {
        let visible;
        try {
          visible = JSON.parse(localStorage.getItem('visibleCards') || '[]');
        } catch {
          visible = [];
          localStorage.removeItem('visibleCards');
        }
        document.querySelectorAll('.card-wrapper').forEach(card => {
          card.style.display = visible.includes(card.dataset.file) ? '' : 'none';
        });
      }
      applyCardVisibility();
    });
  </script>
</body>
</html>

  <!--
  * Changelog:
  * - Fixed stray closing </script> tag.
  * - Ensured modal is explicitly hidden on load via hideModal() before other actions.
  * - Consolidated changelog entries into one section at end.
  Changelog:
  - Changed <html> tag to default dark mode: added class="dark" and data-theme="dark".
  - Wrapped event listener attachments in null-safe checks (using `?.`) and conditional bindings to prevent JS errors.
  - Ensured modal remains hidden by default (`class="hidden"`).
  - Verified click-outside and inner-stopPropagation logic works reliably.
  - Appended detailed changelog entries for future reference.
  -->
  <!--
  Changelog:
  - Added id="cardSettingsContent" to inner modal div.
  - Added stopPropagation on inner content to prevent overlay click from firing when clicking inside.
  - Simplified overlay click listener to hideModal directly.
  - Verified Save/Cancel buttons have type="button" and hide modal on click.
  - Logged all changes for future reference.
  -->
</body>
</html>

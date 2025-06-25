<?php
/**
 * index.php — Single-page entrypoint with OS detection, card-settings modal, and corrected button behavior
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Define placeholder constant
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
?>
<!DOCTYPE html>
<html lang="en" class="h-full mobile-first" data-theme="light">
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

  <!-- Card-settings modal -->
  <div id="cardSettingsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-light dark:bg-dark neumorphic p-4 rounded w-11/12 md:w-1/3 max-h-[80vh] overflow-auto">
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
      // OS detection: add class based on userAgent
      const ua = navigator.userAgent;
      document.documentElement.classList.add(/Mobi|Android|iPhone/.test(ua) ? 'is-mobile' : 'is-desktop');

      // Render all Feather icons
      feather.replace();

      // Theme toggle
      document.getElementById('theme-toggle').addEventListener('click', () => {
        const html = document.documentElement;
        html.setAttribute('data-theme',
          html.getAttribute('data-theme') === 'light' ? 'dark' : 'light'
        );
      });

      // Hard refresh
      document.getElementById('refresh-all').addEventListener('click', () => {
        location.reload(true);
      });

      // Clear session
      document.getElementById('clear-session').addEventListener('click', () => {
        document.cookie.split(';').forEach(c => {
          document.cookie = c.split('=')[0].trim() + '=;expires=Thu, 01 Jan 1970 GMT;path=/';
        });
        location.reload();
      });

      // View debug log
      document.getElementById('view-error-log').addEventListener('click', () => {
        window.open('/logs/debug.log', '_blank');
      });

      // Card-settings modal controls
      const modal = document.getElementById('cardSettingsModal');
      document.getElementById('card-settings').addEventListener('click', () => {
        modal.classList.remove('hidden');
      });
      document.getElementById('cardSettingsCancel').addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.add('hidden');
      });
      document.getElementById('cardSettingsSave').addEventListener('click', (e) => {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('#cardSettingsForm input[name="cards"]:checked'))
                             .map(i => i.value);
        localStorage.setItem('visibleCards', JSON.stringify(checked));
        applyCardVisibility();
        modal.classList.add('hidden');
      });

      function applyCardVisibility() {
        const visible = JSON.parse(localStorage.getItem('visibleCards') || '[]');
        document.querySelectorAll('.card-wrapper').forEach(card => {
          card.style.display = visible.includes(card.dataset.file) ? '' : 'none';
        });
      }
      applyCardVisibility();
    });
  </script>
</body>
</html>

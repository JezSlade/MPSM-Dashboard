<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

$cardsDir = __DIR__ . '/../cards/';
$cardFiles = array_filter(scandir($cardsDir), fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php');
$visibleCards = isset($_COOKIE['visible_cards']) ? explode(',', $_COOKIE['visible_cards']) : $cardFiles;
?>

<!-- ⚙️ Gear icon for preferences -->
<button id="card-settings-toggle" class="gear-icon" title="View Preferences">⚙️</button>

<!-- Preferences Modal -->
<div id="card-settings-modal" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" onclick="hideCardSettings()">×</button>
    <h3>Customize Dashboard View</h3>
    <form id="card-settings-form">
      <?php foreach ($cardFiles as $file): ?>
        <?php $cardName = basename($file, '.php'); ?>
        <label class="card-toggle">
          <input type="checkbox" name="cards[]" value="<?= $file ?>" <?= in_array($file, $visibleCards) ? 'checked' : '' ?>>
          <?= ucwords(str_replace(['-', '_'], ' ', $cardName)) ?>
        </label>
      <?php endforeach; ?>
      <button type="submit" class="save-button">Save</button>
    </form>
  </div>
</div>

<style>
.gear-icon {
  position: fixed;
  top: 1rem;
  right: 1rem;
  background: rgba(255,255,255,0.1);
  border: none;
  color: white;
  font-size: 1.3rem;
  padding: 0.4rem 0.6rem;
  border-radius: 50%;
  cursor: pointer;
  z-index: 1001;
  transition: background 0.3s ease;
}
.gear-icon:hover {
  background: rgba(255,255,255,0.2);
}

.card-toggle {
  display: block;
  margin: 0.4rem 0;
  font-size: 0.9rem;
}

.save-button {
  margin-top: 1rem;
  padding: 0.4rem 0.8rem;
  background: rgba(0,200,255,0.2);
  border: 1px solid rgba(0,200,255,0.3);
  color: white;
  border-radius: 0.4rem;
  cursor: pointer;
}

.save-button:hover {
  background: rgba(0,200,255,0.4);
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal.hidden {
  display: none;
}
.modal-content {
  background: rgba(255,255,255,0.1);
  color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  max-width: 90%;
  width: 400px;
  backdrop-filter: blur(10px);
  position: relative;
}
.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: white;
  cursor: pointer;
}
</style>

<script>
function hideCardSettings() {
  document.getElementById('card-settings-modal').classList.add('hidden');
}
document.getElementById('card-settings-toggle').addEventListener('click', () => {
  document.getElementById('card-settings-modal').classList.remove('hidden');
});

document.getElementById('card-settings-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const selected = Array.from(document.querySelectorAll('input[name="cards[]"]:checked')).map(cb => cb.value);
  document.cookie = "visible_cards=" + selected.join(',') + "; path=/; max-age=31536000";
  location.reload();
});
</script>

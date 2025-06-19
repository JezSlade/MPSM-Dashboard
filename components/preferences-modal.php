<?php declare(strict_types=1);
// /components/preferences-modal.php

// 1) Collect all card_*.php files
$files = glob(__DIR__ . '/../cards/card_*.php') ?: [];
$cards = array_map('basename', $files);

// 2) Read current cookie selections
$visible = [];
if (isset($_COOKIE['visible_cards'])) {
    $visible = array_filter(explode(',', $_COOKIE['visible_cards']), 'strlen');
}
?>
<!-- Preferences Modal -->
<div id="preferences-modal" class="modal-backdrop hidden">
  <div class="modal-content max-w-2xl mx-auto">
    <h2 class="text-lg font-semibold mb-3 text-white">Select Cards to Display</h2>
    <form id="preferences-form">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
        <?php if (empty($cards)): ?>
          <p class="col-span-2 sm:col-span-3 text-center text-gray-400 text-sm">
            No cards found.
          </p>
        <?php else: ?>
          <?php foreach ($cards as $card):
              $label   = pathinfo($card, PATHINFO_FILENAME);
              $label   = substr($label, strlen('card_'));
              $label   = str_replace(['_', '-'], ' ', $label);
              $label   = ucwords($label);
              $checked = in_array($card, $visible) ? 'checked' : '';
          ?>
            <label class="flex items-center space-x-2 text-white text-sm">
              <input
                type="checkbox"
                name="cards[]"
                value="<?= htmlspecialchars($card) ?>"
                <?= $checked ?>
                class="form-checkbox h-4 w-4 text-cyan-500"
              />
              <span><?= htmlspecialchars($label) ?></span>
            </label>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="select-all"
                class="px-3 py-1 text-sm rounded-md bg-gray-700 hover:bg-gray-600">
          Select All
        </button>
        <button type="button" id="deselect-all"
                class="px-3 py-1 text-sm rounded-md bg-gray-700 hover:bg-gray-600">
          Deselect All
        </button>
        <button type="button" id="save-preferences"
                class="px-3 py-1 text-sm rounded-md bg-cyan-500 hover:bg-cyan-400 text-black">
          Save
        </button>
        <button type="button" id="cancel-preferences"
                class="px-3 py-1 text-sm rounded-md bg-red-600 hover:bg-red-500">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
/**
 * Toggles the preferences modal visibility.
 * @param {boolean=} show if omitted, toggles current state.
 */
function togglePreferencesModal(show) {
  const m = document.getElementById('preferences-modal');
  if (!m) return;
  if (typeof show === 'boolean') m.classList.toggle('hidden', !show);
  else m.classList.toggle('hidden');
}

(function(){
  const modal = document.getElementById('preferences-modal');
  const checkboxes = modal.querySelectorAll('input[type="checkbox"]');

  document.getElementById('select-all').addEventListener('click', () =>
    checkboxes.forEach(cb => cb.checked = true)
  );
  document.getElementById('deselect-all').addEventListener('click', () =>
    checkboxes.forEach(cb => cb.checked = false)
  );
  document.getElementById('save-preferences').addEventListener('click', () => {
    const sel = Array.from(checkboxes)
                     .filter(cb => cb.checked)
                     .map(cb => cb.value);
    document.cookie = `visible_cards=${encodeURIComponent(sel.join(','))};path=/;max-age=${60*60*24*365}`;
    togglePreferencesModal(false);
    location.reload();
  });
  document.getElementById('cancel-preferences').addEventListener('click', () =>
    togglePreferencesModal(false)
  );
})();
</script>

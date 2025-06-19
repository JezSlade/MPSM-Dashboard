<?php declare(strict_types=1);
// /components/preferences-modal.php

// 1) Directly glob for card_*.php in the cards directory
$cards = glob(__DIR__ . '/../cards/card_*.php') ?: [];

// 2) Normalize into basenames
$cards = array_map('basename', $cards);

// 3) Read current cookie selections
$visible = [];
if (isset($_COOKIE['visible_cards'])) {
    $visible = array_filter(explode(',', $_COOKIE['visible_cards']), 'strlen');
}
?>
<!-- Preferences Modal -->
<div id="preferences-modal" class="modal-backdrop hidden">
  <div class="modal-content">
    <h2 class="text-xl font-semibold mb-4 text-white">Select Cards to Display</h2>
    <form id="preferences-form">
      <div class="grid grid-cols-3 gap-4 mb-6">
        <?php if (empty($cards)): ?>
          <p class="col-span-3 text-center text-gray-400">No cards found.</p>
        <?php else: ?>
          <?php foreach ($cards as $card): 
              // Strip prefix & extension, then humanize
              $label = pathinfo($card, PATHINFO_FILENAME);     // e.g. "card_device_counters"
              $label = substr($label, strlen('card_'));        // "device_counters"
              $label = str_replace(['_', '-'], ' ', $label);  // "device counters"
              $label = ucwords($label);                        // "Device Counters"
              $checked = in_array($card, $visible) ? 'checked' : '';
          ?>
            <label class="flex items-center space-x-2 text-white">
              <input type="checkbox"
                     name="cards[]"
                     value="<?= htmlspecialchars($card) ?>"
                     <?= $checked ?>
                     class="form-checkbox h-5 w-5 text-cyan-500">
              <span><?= htmlspecialchars($label) ?></span>
            </label>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="flex justify-end space-x-4">
        <button type="button" id="select-all"
                class="px-4 py-2 rounded-md bg-gray-700 hover:bg-gray-600 text-white">
          Select All
        </button>
        <button type="button" id="deselect-all"
                class="px-4 py-2 rounded-md bg-gray-700 hover:bg-gray-600 text-white">
          Deselect All
        </button>
        <button type="button" id="save-preferences"
                class="px-4 py-2 rounded-md bg-cyan-500 hover:bg-cyan-400 text-black">
          Save
        </button>
        <button type="button" id="cancel-preferences"
                class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-500 text-white">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const modal     = document.getElementById('preferences-modal');
  const checkboxes= modal.querySelectorAll('input[type="checkbox"]');

  document.getElementById('select-all')
          .addEventListener('click', () => {
    checkboxes.forEach(cb => cb.checked = true);
  });

  document.getElementById('deselect-all')
          .addEventListener('click', () => {
    checkboxes.forEach(cb => cb.checked = false);
  });

  document.getElementById('save-preferences')
          .addEventListener('click', () => {
    const selected = Array.from(checkboxes)
                          .filter(cb => cb.checked)
                          .map(cb => cb.value);
    document.cookie = 
      `visible_cards=${encodeURIComponent(selected.join(','))};path=/;max-age=${60*60*24*365}`;
    modal.classList.add('hidden');
    location.reload();
  });

  document.getElementById('cancel-preferences')
          .addEventListener('click', () => {
    modal.classList.add('hidden');
  });
})();
</script>

<?php declare(strict_types=1);
// /components/preferences-modal.php

// 1) Find all card_*.php files via glob()
$cardsDir = realpath(__DIR__ . '/../cards');
$cards = [];
if ($cardsDir && is_dir($cardsDir)) {
    foreach (glob($cardsDir . '/card_*.php') as $filePath) {
        $cards[] = basename($filePath);
    }
}

// 2) Read current selections from cookie
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
              // e.g. 'card_device_counters.php' → 'Device Counters'
              $label = ucwords(str_replace(['card_','.php','_'], ['','',' '], $card));
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
  const modal = document.getElementById('preferences-modal');
  const form  = document.getElementById('preferences-form');

  document.getElementById('select-all')
          .addEventListener('click', () => {
    form.querySelectorAll('input[type="checkbox"]')
        .forEach(cb => cb.checked = true);
  });

  document.getElementById('deselect-all')
          .addEventListener('click', () => {
    form.querySelectorAll('input[type="checkbox"]')
        .forEach(cb => cb.checked = false);
  });

  document.getElementById('save-preferences')
          .addEventListener('click', () => {
    const selected = Array.from(
      form.querySelectorAll('input[name="cards[]"]:checked')
    ).map(cb => cb.value);
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

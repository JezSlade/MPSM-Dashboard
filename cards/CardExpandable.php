<?php
/**
 * cards/CardExpandable.php — FIXED VERSION with unique IDs and no currentScript dependency
 * Expects $cardId from parent index.php to ensure unique element IDs.
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file

// Fallback if $cardId is not set (though index.php should always set it)
if (!isset($cardId)) {
    $cardId = 'expandable-card-fallback-' . uniqid();
}

$expandableContentId = $cardId . '-expand-content';
$expandableBodyId = $cardId . '-expand-body';
$expandToggleBtnId = $cardId . '-expand-toggle-btn';
?>
<div id="<?php echo $expandableContentId; ?>"
     class="expandable-content neumorphic p-4 transition-all duration-200 overflow-hidden"
     style="max-height: 3rem;">
  <header class="flex items-center justify-between">
    <h2 class="font-medium text-lg">Expandable Card</h2>
    <button id="<?php echo $expandToggleBtnId; ?>" class="neu-btn" aria-label="Expand or collapse card">
      <i data-feather="chevron-down"></i>
    </button>
  </header>
  <div id="<?php echo $expandableBodyId; ?>"
       class="expandable-body mt-4 opacity-0 transition-opacity duration-200">
    <p class="text-sm mb-2">
      This is the expanded content of the card. You can place any detail here—charts, tables, or text.
    </p>
    <ul class="list-disc list-inside text-sm">
      <li>Detail item 1</li>
      <li>Detail item 2</li>
      <li>Detail item 3</li>
    </ul>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Use the dynamically generated unique IDs to select specific elements for this card instance
    const card = document.getElementById('<?php echo $expandableContentId; ?>');
    const body = document.getElementById('<?php echo $expandableBodyId; ?>');
    const btn = document.getElementById('<?php echo $expandToggleBtnId; ?>');

    // Add a check to ensure elements are found (important for robustness)
    if (!card || !body || !btn) {
        console.warn('Expandable Card: Elements not found for card ID: <?php echo $cardId; ?>. Script may not function correctly for this instance.');
        return; // Exit if elements aren't found for this card
    }

    let expanded = false;

    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent drag interference
      expanded = !expanded;
      if (expanded) {
        card.style.maxHeight = '20rem'; // Adjust this value as needed for your content
        body.style.opacity = '1';
      } else {
        card.style.maxHeight = '3rem'; // Adjust this value as needed
        body.style.opacity = '0';
      }
      const icon = btn.querySelector('i');
      if (icon) {
        icon.setAttribute('data-feather', expanded ? 'chevron-up' : 'chevron-down');
        // Ensure feather icons are replaced after changing the data-feather attribute
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      }
    });
  });
</script>
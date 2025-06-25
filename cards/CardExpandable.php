<?php
/**
 * cards/CardExpandable.php — Dummy card demonstrating expand/collapse functionality
 *
 * Changelog:
 * - Guarded icon swap with null-check to avoid `btn.querySelector(...) is null` errors.
 * - Consolidated `feather.replace()` after safe attribute update.
 */
?>
<div id="cardExpandable" class="neumorphic p-4 transition-all duration-200 overflow-hidden" 
     style="max-height: 3rem;">
<?php
$title = 'CardExpandable';
$cardId = 'CardExpandable';
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between">
    <h2 class="font-medium text-lg">Expandable Card</h2>
    <button id="expandToggle" class="neu-btn" aria-label="Expand or collapse card">
      <i data-feather="chevron-down"></i>
    </button>
  </header>
  <div id="expandableBody" class="mt-4 opacity-0 transition-opacity duration-200">
    <p class="text-sm mb-2">
      This is the expanded content of the card. You can place any detail here—charts, tables, or text.
    </p>
    <ul class="list-disc list-inside text-sm">
      <li>Detail item 1</li>
      <li>Detail item 2</li>
      <li>Detail item 3</li>
    </ul>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const card = document.getElementById('cardExpandable');
      const body = document.getElementById('expandableBody');
      const btn  = document.getElementById('expandToggle');
      let expanded = false;

      btn.addEventListener('click', () => {
        expanded = !expanded;
        if (expanded) {
          card.style.maxHeight = '20rem';
          body.style.opacity   = '1';
        } else {
          card.style.maxHeight = '3rem';
          body.style.opacity   = '0';
        }
        const icon = btn.querySelector('i');
        if (icon) {
          icon.setAttribute('data-feather', expanded ? 'chevron-up' : 'chevron-down');
        }
        feather.replace();
      });
    });
  </script>

  <!--
  Changelog:
  - Created CardExpandable.php to show expand/collapse.
  - Uses inline <script> to toggle max-height and opacity.
  - Button icon swaps between chevron-down and chevron-up.
  -->

<!--
Changelog:
- Updated to use shared card_header.php include.
-->

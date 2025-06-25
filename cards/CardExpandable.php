<?php
/**
 * cards/CardExpandable.php — Dummy card demonstrating expand/collapse functionality
 *
 * This card starts in “minimized” state (only header), and expands to show body content
 * when the toggle button is clicked. Clicking again collapses it.
 */
?>
<div id="cardExpandable" class="neumorphic p-4 transition-all duration-200 overflow-hidden" 
     style="max-height: 3rem;">
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
          // expand: grow height and fade in body
          card.style.maxHeight = '20rem';
          body.style.opacity   = '1';
          btn.querySelector('i').setAttribute('data-feather', 'chevron-up');
        } else {
          // collapse: shrink height and fade out body
          card.style.maxHeight = '3rem';
          body.style.opacity   = '0';
          btn.querySelector('i').setAttribute('data-feather', 'chevron-down');
        }
        // re-render the icon
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

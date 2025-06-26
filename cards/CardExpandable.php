<?php
/**
 * cards/CardExpandable.php — FIXED VERSION
 * Removed header conflicts and used dynamic IDs
 */
?>
<div class="expandable-content neumorphic p-4 transition-all duration-200 overflow-hidden" 
     style="max-height: 3rem;">
  <header class="flex items-center justify-between">
    <h2 class="font-medium text-lg">Expandable Card</h2>
    <button class="expand-toggle neu-btn" aria-label="Expand or collapse card">
      <i data-feather="chevron-down"></i>
    </button>
  </header>
  <div class="expandable-body mt-4 opacity-0 transition-opacity duration-200">
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
    // Use relative selectors instead of hardcoded IDs
    const currentScript = document.currentScript;
    const cardContent = currentScript.previousElementSibling;
    const card = cardContent.querySelector('.expandable-content');
    const body = cardContent.querySelector('.expandable-body');
    const btn = cardContent.querySelector('.expand-toggle');
    
    if (!card || !body || !btn) return;
    
    let expanded = false;

    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent drag interference
      expanded = !expanded;
      if (expanded) {
        card.style.maxHeight = '20rem';
        body.style.opacity = '1';
      } else {
        card.style.maxHeight = '3rem';
        body.style.opacity = '0';
      }
      const icon = btn.querySelector('i');
      if (icon) {
        icon.setAttribute('data-feather', expanded ? 'chevron-up' : 'chevron-down');
      }
      if (window.feather) feather.replace();
    });
  });
</script>
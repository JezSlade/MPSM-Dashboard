// /public/js/ui.js

function renderFeatherIcons() {
  if (window.feather) {
    feather.replace();
  }
}

function initializeGlobalUI() {
  renderFeatherIcons();

  // Settings modal toggle
  document.querySelectorAll('[data-action="open-settings"]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const modal = document.querySelector('#settings-modal');
      if (modal) {
        modal.classList.add('visible');
        renderFeatherIcons(); // If modal contains feather icons
      }
    });
  });

  // Refresh page button
  document.querySelectorAll('[data-action="refresh-page"]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      location.reload();
    });
  });
}

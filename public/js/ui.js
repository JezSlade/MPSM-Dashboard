// /public/js/ui.js
function renderFeatherIcons() {
  if (window.feather) {
    feather.replace();
  }
}

function initializeGlobalUI() {
  renderFeatherIcons();

  // Attach any global listeners here (e.g. gear icons)
  document.querySelectorAll('[data-action="open-settings"]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector('#settings-modal')?.classList.add('visible');
      renderFeatherIcons(); // Ensure icons inside modal render
    });
  });
}

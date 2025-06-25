// /public/js/feather-init.js
document.addEventListener('DOMContentLoaded', () => {
  // 1) Replace all <i data-feather="…"> with SVGs
  if (window.feather) {
    feather.replace();
  }

  // 2) Wire up the settings button once icons exist
  const settingsBtn = document.getElementById('settings-btn');
  if (settingsBtn) {
    settingsBtn.addEventListener('click', e => {
      e.preventDefault();
      const modal = document.getElementById('settingsModal');
      if (modal) {
        modal.classList.add('open');
      }
    });
  }
});

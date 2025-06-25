// /public/js/feather-init.js
document.addEventListener('DOMContentLoaded', () => {
  if (typeof feather !== 'undefined') {
    feather.replace();
  }

  setTimeout(() => {
    const settingsBtn = document.getElementById('settings-btn');
    if (settingsBtn) {
      settingsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('settingsModal')?.classList.add('open');
      });
    }
  }, 100); // delay lets icons render before events attach
});

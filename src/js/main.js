
document.addEventListener('DOMContentLoaded', () => {
  const themeBtn = document.querySelector('[data-action="open-theme-library"]');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      window.open('/public/theme-library.html', '_blank');
    });
  }
});

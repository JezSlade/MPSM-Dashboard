// Wait for DOM, then init icons & theme
document.addEventListener('DOMContentLoaded', () => {
  if (window.feather) feather.replace();

  // Theme toggle
  const btn = document.getElementById('themeToggle');
  btn.addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  });

  // Load saved theme
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
  }
});

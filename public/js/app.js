document.addEventListener('DOMContentLoaded', () => {
  if (window.feather) feather.replace();
  // Theme toggle
  const btn = document.getElementById('themeToggle');
  btn.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
  });
  // Load saved theme
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
  }
});
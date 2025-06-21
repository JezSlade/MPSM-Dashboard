<?php declare(strict_types=1);
// includes/footer.php
?>
<footer class="p-4 text-center text-sm neon-glass-footer">
  &copy; <?= date('Y') ?> MPS Monitor Dashboard
</footer>

<script>
// Close modals on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-backdrop:not(.hidden)')
      .forEach(m => m.classList.add('hidden'));
  }
});
</script>
</body>
</html>

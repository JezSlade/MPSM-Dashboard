<?php declare(strict_types=1); ?>
<?php // components/preferences-modal loaded in views if needed ?>
<script>
// Close preferences modal on Escape
document.addEventListener('keydown', e => {
  if(e.key==='Escape'){
    const m = document.getElementById('preferences-modal');
    if(m && !m.classList.contains('hidden')) m.classList.add('hidden');
  }
});
</script>
</body>
</html>

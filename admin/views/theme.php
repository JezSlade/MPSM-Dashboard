<!DOCTYPE html><html><body>
<h2>Theme Settings</h2>
<form method="post" action="<?= APP_BASE?>/?path=admin/save-theme">
  <label><input type="radio" name="theme" value="light"
    <?= $current==='light'?'checked':'' ?>> Light</label><br>
  <label><input type="radio" name="theme" value="dark"
    <?= $current==='dark'?'checked':'' ?>> Dark</label><br>
  <button type="submit">Save</button>
</form>
</body></html>

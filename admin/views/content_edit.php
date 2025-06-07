<!DOCTYPE html><html><body>
<h2><?= \$row ? 'Edit' : 'New' ?> Page</h2>
<form method="post" action="<?= APP_BASE?>/?path=admin/save-content">
  <?php if(\$row): ?>
    <input type="hidden" name="id" value="<?= h(\$row['id']) ?>">
  <?php endif; ?>
  <label>Slug:<br><input name="slug" value="<?= h(\$row['slug'] ?? '') ?>" required></label><br>
  <label>Title:<br><input name="title" value="<?= h(\$row['title'] ?? '') ?>" required></label><br>
  <label>Body:<br>
    <textarea name="body" rows=10 cols=50><?= h(\$row['body'] ?? '') ?></textarea>
  </label><br>
  <button type="submit">Save</button>
</form>
</body></html>

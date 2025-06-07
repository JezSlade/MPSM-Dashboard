<!DOCTYPE html><html><body>
<h2>Content Pages</h2>
<p><a href="<?= APP_BASE?>/?path=admin/edit-content">+ New Page</a></p>
<table border=1 cellpadding=5>
<tr><th>ID</th><th>Slug</th><th>Title</th><th>Action</th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= h($r['id']) ?></td>
  <td><?= h($r['slug']) ?></td>
  <td><?= h($r['title']) ?></td>
  <td><a href="<?= APP_BASE?>/?path=admin/edit-content&id=<?= $r['id'] ?>">Edit</a></td>
</tr>
<?php endforeach; ?>
</table>
</body></html>

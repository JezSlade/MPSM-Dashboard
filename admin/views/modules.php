<!DOCTYPE html><html><body>
<h2>Modules</h2>
<table border=1 cellpadding=5>
<tr><th>ID</th><th>Name</th><th>Position</th><th>Config</th></tr>
<?php foreach($mods as $m): ?>
  <tr>
    <td><?= h($m['id']) ?></td>
    <td><?= h($m['name']) ?></td>
    <td><?= h($m['position']) ?></td>
    <td><?= h($m['config']) ?></td>
  </tr>
<?php endforeach; ?>
</table>
<p>To modify instances, update <code>module_instances</code> or extend this UI.</p>
</body></html>

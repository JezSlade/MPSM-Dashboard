<?php declare(strict_types=1);
// /views/view-preferences-card.php

// scan cards directory
$cards = [];
foreach (glob(__DIR__ . '/../cards/card_*.php') as $file) {
    $fname = basename($file);
    // derive group prefix
    if (preg_match('/^card_([^_]+)_/', $fname, $m)) {
        $group = ucfirst($m[1]);
    } else {
        $group = 'Other';
    }
    // derive display name
    $disp = str_replace(['card_','.php','_'],'',['', $fname]);
    $disp = ucfirst(str_replace('_',' ',$fname));
    $cards[$group][] = ['file'=>$fname,'name'=>$disp];
}
?>
<div class="modal-content">
  <h3>View Preferences</h3>
  <div style="max-height:400px; overflow-y:auto;">
    <table class="preferences-table">
      <thead><tr><th>Group</th><th>Card Name</th><th>File</th><th>Show</th></tr></thead>
      <tbody>
      <?php foreach($cards as $group=>$items): ?>
        <?php foreach($items as $item): ?>
        <tr>
          <td><?=htmlspecialchars($group)?></td>
          <td><?=htmlspecialchars($item['name'])?></td>
          <td><?=htmlspecialchars($item['file'])?></td>
          <td><input type="checkbox" name="cards[]" value="<?=htmlspecialchars($item['file'])?>"></td>
        </tr>
        <?php endforeach;?>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <button class="btn-save">Save Preferences</button>
</div>

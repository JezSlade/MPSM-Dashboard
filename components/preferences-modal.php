<?php declare(strict_types=1); ?>
<!-- /components/preferences-modal.php -->
<div id="preferences-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-dialog">
    <h3>Select Cards to Display</h3>
    <?php
      $cardFiles    = $cardFiles ?? [];
      $visibleCards = $visibleCards ?? [];
      $groups = [];
      foreach ($cardFiles as $file) {
          if (preg_match('/^card_([^_]+)_/', $file, $m)) {
              $group = ucfirst($m[1]);
          } else {
              $group = 'Other';
          }
          $nameKey = preg_replace(['/^card_/', '/\.php$/'], '', $file);
          // Remove leading 'get_' if present
          if (strpos($nameKey, 'get_') === 0) {
              $nameKey = substr($nameKey, 4);
          }
          $display = ucfirst(str_replace('_', ' ', $nameKey));
          $groups[$group][] = ['file'=>$file,'name'=>$display];
      }
    ?>
    <div class="modal-content">
      <?php foreach($groups as $group=>$items): ?>
        <h4><?=htmlspecialchars($group)?></h4>
        <?php foreach($items as $item): ?>
          <label class="modal-item">
            <input type="checkbox" name="cards[]" value="<?=htmlspecialchars($item['file'])?>" <?=in_array($item['file'],$visibleCards)?'checked':''?>>
            <?=htmlspecialchars($item['name'])?>
          </label>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
    <div class="modal-actions">
      <button id="save-modal" class="btn">Save</button>
      <button id="cancel-modal" class="btn">Cancel</button>
    </div>
  </div>
</div>

<script>
function togglePreferencesModal(show) {
  document.getElementById('preferences-modal').classList.toggle('hidden', !show);
}
document.addEventListener('DOMContentLoaded',function(){
  document.querySelector('.gear-icon').addEventListener('click',()=>togglePreferencesModal(true));
  document.getElementById('save-modal').addEventListener('click',function(){
    const selected = Array.from(document.querySelectorAll('#preferences-modal input[name="cards[]"]:checked')).map(cb=>cb.value);
    document.cookie='visible_cards='+selected.join(',')+'; path=/; max-age=31536000';
    location.reload();
  });
  document.getElementById('cancel-modal').addEventListener('click',()=>togglePreferencesModal(false));
});
</script>

<style>
.modal { position: fixed; top:0; left:0; right:0; bottom:0; display:flex; align-items:center; justify-content:center; z-index:1000; }
.modal.hidden { display:none; }
.modal-backdrop { position:absolute; inset:0; background:rgba(0,0,0,0.5); }
.modal-dialog { position:relative; background:var(--bg-light); color:var(--text-light); padding:1rem; border-radius:8px; max-width:90%; max-height:80%; overflow:auto; box-shadow:0 8px 16px rgba(0,0,0,0.2); }
.modal-content { margin-bottom:1rem; }
.modal-item { display:block; margin:0.25rem 0; }
.modal-actions { text-align:right; }
.btn { margin-left:0.5rem; padding:0.5rem 1rem; border:none; border-radius:4px; cursor:pointer; background:var(--text-light); color:var(--bg-light); }
</style>

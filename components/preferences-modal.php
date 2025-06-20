<?php declare(strict_types=1);
// components/preferences-modal.php
$cards = include __DIR__ . '/../config/cards.php';
$vis = isset($_COOKIE['visible_cards'])?array_filter(array_map('trim',explode(',',$_COOKIE['visible_cards'])),'strlen'):[];?>
<div id="preferences-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true">
  <div class="modal-content max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-4 text-white">Select Cards to Display</h2>
    <form id="preferences-form">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
        <?php foreach($cards as$id=>$title):
          $chk=in_array($id,$vis)?'checked':'';?>
        <label class="flex items-center space-x-2 text-white">
          <input type="checkbox" name="cards[]" value="<?=htmlspecialchars($id)?>" <?=$chk?> class="form-checkbox h-5 w-5 text-cyan-400"/>
          <span><?=htmlspecialchars($title)?></span>
        </label>
        <?php endforeach;?>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="select-all" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600 text-sm">Select All</button>
        <button type="button" id="deselect-all" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600 text-sm">Clear All</button>
        <button type="button" id="save-preferences" class="px-4 py-2 bg-cyan-500 rounded hover:bg-cyan-400 text-black text-sm">Save</button>
        <button type="button" id="cancel-preferences" class="px-4 py-2 bg-red-600 rounded hover:bg-red-500 text-sm">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
(function(){
  const modal = document.getElementById('preferences-modal');
  const cbs   = modal.querySelectorAll('input[name="cards[]"]');
  document.getElementById('select-all').onclick     = ()=> cbs.forEach(cb=>cb.checked=true);
  document.getElementById('deselect-all').onclick   = ()=> cbs.forEach(cb=>cb.checked=false);
  document.getElementById('cancel-preferences').onclick = ()=> modal.classList.add('hidden');
  document.getElementById('save-preferences').onclick   = ()=>{
    const sel = Array.from(cbs).filter(cb=>cb.checked).map(cb=>cb.value);
    document.cookie = 'visible_cards='+encodeURIComponent(sel.join(','))+';path=/;max-age='+60*60*24*365;
    modal.classList.add('hidden');
    location.reload();
  };
  const btn = document.getElementById('preferences-toggle');
  if(btn) btn.addEventListener('click', ()=> modal.classList.remove('hidden'));
})();
</script>

<?php declare(strict_types=1);
// /includes/searchable_dropdown.php

function renderSearchableDropdown(
    string $id,
    string $datalistId,
    string $apiEndpoint,
    string $cookieName,
    string $placeholder,
    string $cssClasses = 'w-full text-xs bg-gray-800 text-white border border-gray-600 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500'
): void {
    $currCode = $_COOKIE[$cookieName] ?? '';
    echo <<<HTML
<div class="relative z-10 flex-1 max-w-xs">
  <label for="{$id}" class="sr-only">{$placeholder}</label>
  <input list="{$datalistId}" id="{$id}" class="{$cssClasses}" placeholder="{$placeholder}" value="" />
  <datalist id="{$datalistId}"></datalist>
</div>
<script>
(function(){
  const input    = document.getElementById('{$id}');
  const listEl   = document.getElementById('{$datalistId}');
  const cookie   = '{$cookieName}';
  fetch('{$apiEndpoint}')
    .then(r => r.json())
    .then(resp => {
      const items = resp.customers || resp.Result || resp || [];
      listEl.innerHTML = '';
      items.forEach(i => {
        const opt = document.createElement('option');
        opt.value = i.Description || i.Name || i.Code || '';
        opt.dataset.code = i.Code || '';
        listEl.appendChild(opt);
      });
      const m = document.cookie.match(new RegExp('(?:^|; )'+cookie+'=([^;]+)'));
      if (m) input.value = decodeURIComponent(m[1]);
    });
  input.addEventListener('change', () => {
    const sel = Array.from(listEl.options).find(o => o.value===input.value);
    if (sel) {
      document.cookie = cookie+'='+encodeURIComponent(sel.dataset.code)+';path=/;max-age='+(60*60*24*365);
      location.reload();
    }
  });
})();
</script>
HTML;
}

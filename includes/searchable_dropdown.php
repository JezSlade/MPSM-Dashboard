<?php declare(strict_types=1);
// /includes/searchable_dropdown.php

/**
 * Render a compact, Tailwind‐styled searchable dropdown.
 *
 * @param string $id            ID for the <input> element
 * @param string $datalistId    ID for the <datalist> element
 * @param string $apiEndpoint   URL to fetch options
 * @param string $cookieName    Cookie key to store the selected Code
 * @param string $placeholder   Placeholder text
 * @param string $cssClasses    Tailwind classes for the <input>
 */
function renderSearchableDropdown(
    string $id,
    string $datalistId,
    string $apiEndpoint,
    string $cookieName,
    string $placeholder,
    string $cssClasses = 'w-full text-xs bg-gray-800 text-white border border-gray-600 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500'
): void {
    // Read current code from cookie
    $currCode = $_COOKIE[$cookieName] ?? '';

    echo <<<HTML
<div class="relative z-10 flex-1 max-w-xs">
  <label for="{$id}" class="sr-only">{$placeholder}</label>
  <input
    list="{$datalistId}"
    id="{$id}"
    class="{$cssClasses}"
    placeholder="{$placeholder}"
    value=""
  />
  <datalist id="{$datalistId}"></datalist>
</div>
<script>
(function(){
  const input      = document.getElementById('{$id}');
  const datalist   = document.getElementById('{$datalistId}');
  const cookieName = '{$cookieName}';
  const apiUrl     = '{$apiEndpoint}';

  // Grab current code from cookie
  const m = document.cookie.match(new RegExp('(?:^|; )' + cookieName + '=([^;]+)'));
  const current = m ? decodeURIComponent(m[1]) : '';

  fetch(apiUrl)
    .then(res => res.json())
    .then(resp => {
      // Try multiple shapes
      const list = resp.customers || resp.Result || resp || [];
      datalist.innerHTML = '';
      list.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.Description || item.Name || item.Code || '';
        opt.dataset.code = item.Code || '';
        datalist.appendChild(opt);
      });
      // Preselect if found
      if (current) {
        const found = Array.from(datalist.options)
                           .find(o => o.dataset.code === current);
        if (found) {
          input.value = found.value;
        }
      }
    })
    .catch(err => {
      console.error('Searchable dropdown load error:', err);
    });

  input.addEventListener('change', () => {
    const sel = Array.from(datalist.options)
                     .find(o => o.value === input.value);
    const code = sel ? sel.dataset.code : '';
    if (code) {
      document.cookie = cookieName + '=' + encodeURIComponent(code)
                      + ';path=/;max-age=' + (60*60*24*365);
      location.reload();
    }
  });
})();
</script>
HTML;
}

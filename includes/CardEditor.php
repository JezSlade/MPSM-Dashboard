<?php
declare(strict_types=1);
/**
 * CardEditor.php - renders interactive API-testing cards.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// 1) Session must already be started by config.php

class CardEditor {
    private array $endpoints = [];
    private string $apiRoot;

    public function __construct() {
        $this->apiRoot = API_BASE_URL;
        $this->loadEndpoints();
    }

    private function loadEndpoints(): void {
        $file = __DIR__ . '/../AllEndpoints.json';
        if (! file_exists($file)) {
            debug_log("AllEndpoints.json missing", 'ERROR');
            return;
        }
        $json = json_decode(file_get_contents($file), true);
        if (! is_array($json)) {
            debug_log("AllEndpoints.json parse error", 'ERROR');
            return;
        }
        foreach ($json as $k => $ep) {
            if (isset($ep['Method'], $ep['Url'])) {
                $this->endpoints[$k] = [
                    'method'  => strtoupper($ep['Method']),
                    'url'     => $ep['Url'],
                    'payload' => $ep['Request'] ?? []
                ];
            }
        }
    }

    public function render(): void {
        echo '<link rel="stylesheet" href="' . SITE_BASE_URL . 'css/card-editor.css">';
        echo '<div class="cards-container">';
        foreach ($this->endpoints as $key => $ep) {
            if ($ep['method'] === 'GET') {
                $this->renderCard($key, $ep);
            }
        }
        echo '</div>';
        $this->renderScripts();
    }

    private function renderCard(string $k, array $ep): void {
        $cfg = htmlspecialchars(json_encode($ep), ENT_QUOTES|ENT_SUBSTITUTE);
        echo <<<HTML
<div class="card" id="card_{$k}">
  <div class="card-inner">
    <div class="card-front" onclick="CE_flipCard('card_{$k}')">
      <h4>{$k}</h4>
      <small>{$ep['method']} {$ep['url']}</small>
    </div>
    <div class="card-back">
      <input type="hidden" id="{$k}_config" value="{$cfg}">
      <button onclick="CE_flipCard('card_{$k}')">× Close</button>
      <form id="card_{$k}_form"></form>
      <div id="card_{$k}_result" class="api-result"></div>
    </div>
  </div>
</div>
<script>CE_updateForm('{$k}','card_{$k}');</script>
HTML;
    }

    private function renderScripts(): void {
        $root = htmlspecialchars($this->apiRoot, ENT_QUOTES|ENT_SUBSTITUTE);
        echo <<<HTML
<script>
(function(){
  const ROOT = "{$root}";
  function _flip(id){ const c=document.getElementById(id); c.classList.toggle('flipped'); c.classList.toggle('enlarged'); }
  function _updateForm(key,card){
    const ep = JSON.parse(document.getElementById(key + '_config').value);
    const form = document.getElementById(card + '_form');
    form.innerHTML = '';
    if (ep.payload && typeof ep.payload==='object'){
      let html = '<fieldset><legend>Parameters</legend>';
      for (let p in ep.payload){
        const cfg = String(ep.payload[p]);
        const opt = cfg.includes('optional');
        const type = cfg.replace('|optional','');
        const fid = card+'_'+p;
        html += '<label>'+p+' ('+type+')'+(opt?' (opt)':'')+'</label>';
        html += '<input id="'+fid+'" name="'+p+'" type="'+(type==='integer'?'number':'text')+'" '+(opt?'':'required')+'>';
      }
      html += '</fieldset>';
      form.innerHTML = html;
    }
    form.innerHTML += '<button type="button" onclick="CE_test(\''+key+'\',\''+card+'\')">Test</button>';
  }
  async function _test(key,card){
    const ep = JSON.parse(document.getElementById(key + '_config').value);
    const form = document.getElementById(card + '_form');
    const data = {};
    Array.from(form.elements).forEach(e=>{ if(e.name && e.value) data[e.name]=e.value; });
    let url = ROOT + ep.url;
    for (let k in data) url = url.replace('{'+k+'}',encodeURIComponent(data[k]));
    const qs = new URLSearchParams(data).toString();
    if(qs) url += (url.includes('?')?'&':'?')+qs;
    try {
      const res = await fetch(url,{ headers:{ 'Authorization':'Bearer '+document.getElementById('api-token').value }});
      const js = await res.json();
      document.getElementById(card + '_result').innerHTML = '<pre>'+JSON.stringify(js,null,2)+'</pre>';
    } catch(err){
      document.getElementById(card + '_result').innerHTML = '<div class="error">Error: '+err.message+'</div>';
    }
  }
  window.CE_flipCard = _flip;
  window.CE_updateForm = _updateForm;
  window.CE_test       = _test;
})();
</script>
HTML;
    }
}

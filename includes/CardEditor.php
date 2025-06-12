<?php
declare(strict_types=1);
/**
 * includes/CardEditor.php
 *
 * Interactive card‐based interface to explore GET endpoints
 * defined in AllEndpoints.json.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CardEditor
{
    /** @var array<string,array{method:string,url:string,payload:array<string,mixed>}> */
    private array $endpoints = [];

    /** @var string */
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = BASE_URL;
        $this->loadEndpoints();
    }

    private function loadEndpoints(): void
    {
        $file = __DIR__ . '/../AllEndpoints.json';
        if (! file_exists($file)) {
            debug_log("AllEndpoints.json not found at {$file}", 'ERROR');
            return;
        }

        $raw  = file_get_contents($file);
        $data = json_decode($raw, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            debug_log("JSON parse error in AllEndpoints.json: " . json_last_error_msg(), 'ERROR');
            return;
        }

        foreach ($data as $key => $cfg) {
            if (
                isset($cfg['Method'], $cfg['Url'])
                && is_string($cfg['Method'])
                && is_string($cfg['Url'])
            ) {
                $this->endpoints[$key] = [
                    'method'  => strtoupper($cfg['Method']),
                    'url'     => $cfg['Url'],
                    'payload' => $cfg['Request'] ?? []
                ];
            }
        }
    }

    public function render(): void
    {
        echo '<div class="cards-container">' . PHP_EOL;
        foreach ($this->endpoints as $key => $ep) {
            if ($ep['method'] === 'GET') {
                $this->renderCard($key, $ep);
            }
        }
        echo '</div>' . PHP_EOL;
        $this->renderScripts();
    }

    private function renderCard(string $key, array $ep): void
    {
        $cfgJson = htmlspecialchars(json_encode($ep), ENT_QUOTES|ENT_SUBSTITUTE);
        echo <<<HTML
<div class="card" id="card_{$key}">
  <div class="card-inner">
    <div class="card-front" onclick="flipCard('card_{$key}')">
      <div class="card-title">{$key}</div>
      <div class="card-meta">
        <span class="method">{$ep['method']}</span>
        <span class="url">{$ep['url']}</span>
      </div>
    </div>
    <div class="card-back">
      <input type="hidden" id="{$key}_config" value="{$cfgJson}">
      <div class="card-header">
        <h3>{$key}</h3>
        <button type="button" class="close-btn" onclick="flipCard('card_{$key}')">&times;</button>
      </div>
      <div class="card-body">
        <form id="card_{$key}_form"></form>
        <div id="card_{$key}_result" class="api-result"></div>
      </div>
    </div>
  </div>
</div>
<script>updatePayloadForm('{$key}','card_{$key}');</script>

HTML;
    }

    private function renderScripts(): void
    {
        $base = htmlspecialchars($this->apiBaseUrl, ENT_QUOTES|ENT_SUBSTITUTE);
        echo <<<HTML
<script>
const API_BASE_URL = "{$base}";

function flipCard(cardId) {
  const c = document.getElementById(cardId);
  c.classList.toggle('flipped');
  c.classList.toggle('enlarged');
}

function updatePayloadForm(endpointKey, cardId) {
  const raw  = document.getElementById(endpointKey + '_config').value;
  const ep   = JSON.parse(raw);
  const form = document.getElementById(cardId + '_form');
  form.innerHTML = '';

  if (ep.payload && typeof ep.payload === 'object') {
    const grp = document.createElement('div');
    grp.className = 'payload-params';
    grp.innerHTML = '<h4>Parameters</h4>';
    Object.entries(ep.payload).forEach(([p, cfg]) => {
      const isOpt = String(cfg).includes('optional');
      const type  = String(cfg).replace('|optional','');
      const fid   = \`\${cardId}_\${p}\`;
      grp.innerHTML += \`
        <div class="form-group">
          <label for="\${fid}">\${p} (\${type})\${isOpt?' (optional)':''}</label>
          <input type="\${type==='integer'?'number':'text'}" id="\${fid}" name="\${p}" \${isOpt?'':'required'}>
        </div>\`;
    });
    form.appendChild(grp);
  }

  form.innerHTML += \`
    <div class="form-actions">
      <button type="button" onclick="testEndpoint('\${endpointKey}','\${cardId}')">Test API</button>
      <button type="button" onclick="flipCard('\${cardId}')">Close</button>
    </div>\`;
}

async function testEndpoint(endpointKey, cardId) {
  const raw  = document.getElementById(endpointKey + '_config').value;
  const ep   = JSON.parse(raw);
  const form = document.getElementById(cardId + '_form');
  const params = {};
  Array.from(form.elements).forEach(el => {
    if (el.name && el.value) params[el.name] = el.value;
  });

  let url = API_BASE_URL + ep.url;
  for (const [k, v] of Object.entries(params)) {
    url = url.replace(\`{\${k}}\`, encodeURIComponent(v));
  }
  const qs = new URLSearchParams();
  for (const [k, v] of Object.entries(params)) {
    if (!ep.url.includes(\`{\${k}}\`)) qs.append(k, v);
  }
  if (qs.toString()) {
    url += (url.includes('?') ? '&' : '?') + qs;
  }

  try {
    const res  = await fetch(url, {
      headers: { 'Authorization': 'Bearer ' + (document.getElementById('api-token')?.value || '') }
    });
    const data = await res.json();
    document.getElementById(cardId + '_result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
  } catch (err) {
    document.getElementById(cardId + '_result').innerHTML = '<div class="error">API Error: ' + err.message + '</div>';
  }
}
</script>
HTML;
    }
}

// USAGE in index.php:
// require_once __DIR__ . '/includes/CardEditor.php';
// (new CardEditor())->render();

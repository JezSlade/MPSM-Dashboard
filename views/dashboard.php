<?php declare(strict_types=1);
// views/dashboard.php
error_reporting(E_ALL); ini_set('display_errors','1'); ini_set('log_errors','1'); ini_set('error_log', __DIR__.'/../logs/debug.log');
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');
// Determine visible cards
$cardsConfig = include __DIR__ . '/../config/cards.php';
$allIds = array_keys($cardsConfig);
if(isset($_COOKIE['visible_cards'])){
  $sel = array_filter(array_map('trim', explode(',', $_COOKIE['visible_cards'])), 'strlen');
  $visible = count($sel)>0 ? array_intersect($sel, $allIds) : $allIds;
}else $visible = $allIds;
?>
<main class="flex-1 overflow-auto p-6">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($visible as$id):
      $file=__DIR__ . "/../cards/card_{$id}.php";
      if(file_exists($file)) include $file;
      else echo "<div class='panel text-red-500 tooltip' title='Card missing'>{$cardsConfig[$id]??$id}</div>";
    endforeach; ?>
  </div>
</main>

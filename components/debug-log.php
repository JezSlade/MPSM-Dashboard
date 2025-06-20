<?php declare(strict_types=1);
// components/debug-log.php
header('Content-Type: text/html; charset=utf-8');
$possible=[__DIR__.'/../logs/debug.log',__DIR__.'/../../logs/debug.log'];
$log='';foreach($possible as$p) if(is_readable($p)){$log=file($p,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);break;}
$content = $log ? implode("\n",array_slice($log,-200)) : 'Log not found: '.implode(',',$possible);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><title>Debug Log</title><style>body{background:#111;color:#eee;font-family:monospace;padding:1rem}pre{white-space:pre-wrap;}</style></head><body><h1>Debug Log</h1><pre><?=htmlspecialchars($content)?></pre></body></html>

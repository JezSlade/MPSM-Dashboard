<?php
function log_request(): void {
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) mkdir($dir,0755,true);
    $entry = ['time'=>date('c'),'method'=>$_SERVER['REQUEST_METHOD'],'uri'=>$_SERVER['REQUEST_URI'],'get'=>$_GET,'post'=>json_decode(file_get_contents('php://input'),true),'ip'=>$_SERVER['REMOTE_ADDR']];
    file_put_contents($dir.'/api.log',json_encode($entry).PHP_EOL,FILE_APPEND);
}

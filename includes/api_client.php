<?php
// central HTTP client—no headers
require_once __DIR__.'/auth.php';
function api_request(string $path,array $body): array {
  for($i=1;$i<=2;$i++){
    $t=get_bearer_token();
    $ch=curl_init(API_BASE_URL.$path);
    curl_setopt_array($ch,[
      CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true,
      CURLOPT_HTTPHEADER=>['Content-Type:application/json','Authorization: Bearer '.$t],
      CURLOPT_POSTFIELDS=>json_encode($body)
    ]);
    $resp=curl_exec($ch); $err=curl_error($ch);
    $st=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($err) throw new RuntimeException($err);
    if($st===401 && $i===1){ @unlink(__DIR__.'/../.token_cache.json'); continue; }
    $j=json_decode($resp,true);
    if(json_last_error()!==JSON_ERROR_NONE) throw new RuntimeException('Invalid JSON');
    return ['status'=>$st,'data'=>$j];
  }
  throw new RuntimeException('Unknown API error');
}

<?php
// downstream MPS token (client-credentials/password)
function get_bearer_token(): string {
  $cache = __DIR__ . '/../.token_cache.json';
  if (file_exists($cache)){
    $d=json_decode(file_get_contents($cache),true);
    if(isset($d['access_token'],$d['expires_at'])&&time()<$d['expires_at']) return $d['access_token'];
  }
  $fields = http_build_query([
    'grant_type'=>'password',
    'client_id'=>CLIENT_ID,
    'client_secret'=>CLIENT_SECRET,
    'username'=>USERNAME,
    'password'=>PASSWORD,
    'scope'=>SCOPE
  ]);
  $ch=curl_init(TOKEN_URL);
  curl_setopt_array($ch,[
    CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>['Content-Type:application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS=>$fields
  ]);
  $resp=curl_exec($ch); if($err=curl_error($ch)) throw new RuntimeException($err);
  curl_close($ch);
  $j=json_decode($resp,true);
  if(!isset($j['access_token'],$j['expires_in'])) throw new RuntimeException('Bad token');
  $exp=time()+$j['expires_in']-30;
  file_put_contents($cache,json_encode(['access_token'=>$j['access_token'],'expires_at'=>$exp]));
  return $j['access_token'];
}

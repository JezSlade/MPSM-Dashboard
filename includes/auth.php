<?php
function get_bearer_token(): string {
    $cache = __DIR__ . '/../.token_cache.json';
    if (file_exists($cache)) {
        $data = json_decode(file_get_contents($cache), true);
        if (isset($data['access_token'],$data['expires_at']) && time()< $data['expires_at']) {
            return $data['access_token'];
        }
    }
    $ch = curl_init(TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type'=>'password','client_id'=>CLIENT_ID,'client_secret'=>CLIENT_SECRET,
            'username'=>USERNAME,'password'=>PASSWORD,'scope'=>SCOPE
        ]),
    ]);
    $resp = curl_exec($ch); if ($err=curl_error($ch)) throw new RuntimeException($err);
    curl_close($ch);
    $json = json_decode($resp,true);
    if (!isset($json['access_token'],$json['expires_in'])) throw new RuntimeException('Invalid token');
    $exp = time()+$json['expires_in']-30;
    file_put_contents($cache,json_encode(['access_token'=>$json['access_token'],'expires_at'=>$exp]));
    return $json['access_token'];
}

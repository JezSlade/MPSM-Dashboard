<?php
declare(strict_types=1);

// ─── Assumes debug.php already loaded ─────────────────────────

if (!function_exists('load_env')) {
    function load_env(string $path): array {
        if (!is_readable($path)) return [];
        $lines = file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $env   = [];
        foreach ($lines as $ln) {
            if (!$ln || $ln[0]==='#') continue;
            [$k,$v] = array_map('trim', explode('=', $ln, 2));
            $env[$k] = $v;
        }
        return $env;
    }
}

if (!function_exists('get_token')) {
    function get_token(array $env): string {
        $body = http_build_query([
            'grant_type'=>'password',
            'username'=>$env['USERNAME']        ?? '',
            'password'=>$env['PASSWORD']        ?? '',
            'client_id'=>$env['CLIENT_ID']      ?? '',
            'client_secret'=>$env['CLIENT_SECRET']?? '',
            'scope'=>$env['SCOPE']              ?? 'account',
        ]);
        $ch = curl_init($env['TOKEN_URL'] ?? '');
        curl_setopt_array($ch, [
            CURLOPT_POST         => true,
            CURLOPT_POSTFIELDS   => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT      => 10,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $j   = $raw ? json_decode($raw, true) : null;
        return $j['access_token'] ?? '';
    }
}

if (!function_exists('api_call')) {
    /**
     * @param array  $env     from load_env()
     * @param string $method  'GET' or 'POST'
     * @param string $path    leading slash, e.g. '/Device/List'
     * @param array  $payload for GET → query params, POST → JSON body
     */
    function api_call(array $env, string $method, string $path, array $payload = []): array {
        $base  = rtrim($env['API_BASE_URL'] ?? '', '/');
        $token = get_token($env);
        if (!$token) {
            return ['IsValid'=>false,'Errors'=>[['Code'=>'Auth','Description'=>'Token failed']]];
        }

        $url = $base . $path;
        $ch  = curl_init();
        if (strcasecmp($method,'GET')===0) {
            $url .= '?' . http_build_query($payload);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
        } else {
            $json = json_encode($payload);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['IsValid'=>false,'Errors'=>[['Code'=>'Curl','Description'=>$err]]];
        }
        curl_close($ch);

        $resp = json_decode($raw, true);
        if ($resp === null) {
            return ['IsValid'=>false,'Errors'=>[['Code'=>'JSON','Description'=>'Bad JSON']]];
        }
        return $resp;
    }
}

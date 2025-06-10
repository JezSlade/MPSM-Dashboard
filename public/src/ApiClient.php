<?php
// public/src/ApiClient.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DebugPanel.php';

class ApiClient
{
    public function getTokenData(): array
    {
        if (empty(TOKEN_URL)) {
            DebugPanel::log("TOKEN_URL is not set");
            return [];
        }

        $form = http_build_query([
            'grant_type'=>'password',
            'client_id'=>CLIENT_ID,
            'client_secret'=>CLIENT_SECRET,
            'username'=>USERNAME,
            'password'=>PASSWORD,
            'scope'=>SCOPE
        ]);

        DebugPanel::log("Requesting token from ".TOKEN_URL);
        $opts = ['http'=>[
            'method'=>'POST',
            'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
            'content'=>$form,'ignore_errors'=>true
        ]];
        $resp = file_get_contents(TOKEN_URL, false, stream_context_create($opts));
        if ($resp === false) {
            DebugPanel::log("Failed to fetch token");
            return [];
        }
        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            DebugPanel::log("Token JSON parse error: " . json_last_error_msg());
            return [];
        }
        return $data;
    }

    public function postJson(string $path, string $token, array $payload): array
    {
        // ...
    }
}

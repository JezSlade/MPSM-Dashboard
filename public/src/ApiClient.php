<?php
// public/src/ApiClient.php
// -------------------------------------
// Helper for OAuth2 token (with expiry) & JSON POST.
// -------------------------------------

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DebugPanel.php';

class ApiClient
{
    // Fetch raw token data (access_token, expires_in, etc.)
    public function getTokenData(): array
    {
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
        $resp = @file_get_contents(TOKEN_URL, false, stream_context_create($opts));
        if (!$resp) {
            DebugPanel::log("No response for token");
            return [];
        }
        $data = json_decode($resp, true);
        if (json_last_error()!==JSON_ERROR_NONE) {
            DebugPanel::log("Token JSON parse error: ".json_last_error_msg());
            return [];
        }
        return $data;
    }

    // Convenience: extract access_token
    public function getAccessToken(): ?string
    {
        $data = $this->getTokenData();
        return $data['access_token'] ?? null;
    }

    public function postJson(string $path, string $token, array $payload): array
    {
        $url = rtrim(API_BASE_URL,'/').'/'.ltrim($path,'/');
        $body = json_encode($payload);
        $hdrs = [
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: application/json"
        ];
        DebugPanel::log("POST $url");
        $opts=['http'=>[
            'method'=>'POST',
            'header'=>implode("\r\n",$hdrs)."\r\n",
            'content'=>$body,'ignore_errors'=>true
        ]];
        $resp=@file_get_contents($url,false,stream_context_create($opts));
        if (!$resp) {
            DebugPanel::log("POST failed for $url");
            return [];
        }
        $data=json_decode($resp,true);
        if (json_last_error()!==JSON_ERROR_NONE) {
            DebugPanel::log("POST JSON parse error: ".json_last_error_msg());
            return [];
        }
        DebugPanel::log("POST success");
        return $data;
    }
}

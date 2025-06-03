<?php
// src/ApiClient.php
require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/DebugLogger.php';

class ApiClient {
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $username;
    private $password;
    private $token;
    private $dealerCode;

    public function __construct() {
        EnvLoader::load(__DIR__ . '/../.env');
        $this->baseUrl      = rtrim($_ENV['MPSM_BASE_URL'] ?? 'https://api.abassetmanagement.com/api3', '/');
        $this->clientId     = $_ENV['CLIENT_ID']            ?? '';
        $this->clientSecret = $_ENV['CLIENT_SECRET']        ?? '';
        $this->username     = $_ENV['USERNAME']             ?? '';
        $this->password     = $_ENV['PASSWORD']             ?? '';
        $this->dealerCode   = $_ENV['DEALER_CODE']          ?? '';
    }

    private function postJson(string $endpoint, array $payload): array {
        $url = "{$this->baseUrl}/$endpoint";
        $headers = ["Content-Type: application/json"];
        if ($this->token) {
            $headers[] = "Authorization: Bearer {$this->token}";
        }
        $opts = [
            'http' => [
                'method'        => 'POST',
                'header'        => implode("\r\n", $headers) . "\r\n",
                'content'       => json_encode($payload),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);
        $decoded  = json_decode($response, true);
        if (!$decoded) {
            DebugLogger::log("ApiClient::postJson ERROR: invalid JSON from $url → $response");
            return [];
        }
        return $decoded;
    }

    private function authenticate(): string {
        if ($this->token) return $this->token;
        $resp = $this->postJson('Auth/Login', [
            "Username"      => $this->username,
            "Password"      => $this->password,
            "DealerCode"    => $this->dealerCode,
            "ExternalToken" => null
        ]);
        $token = $resp['Result']['AccessToken'] ?? '';
        if (!$token) {
            DebugLogger::log("ApiClient::authenticate ERROR: " . json_encode($resp));
        }
        $this->token = $token;
        return $token;
    }

    public function getCustomers(): array {
        $this->authenticate();
        $resp = $this->postJson('Customer/GetCustomers', [
            "DealerCode" => $this->dealerCode,
            "Code"       => null,
            "HasHpSds"   => null,
            "FilterText" => null,
            "PageNumber" => 1,
            "PageRows"   => 2147483647,
            "SortColumn" => "Id",
            "SortOrder"  => 0
        ]);
        return $resp['Result']['Data'] ?? [];
    }

    public function getDeviceList(string $customerCode, int $page = 1, int $rows = 10): array {
        $this->authenticate();
        $resp = $this->postJson('Device/List', [
            "DealerCode"          => $this->dealerCode,
            "FilterDealerId"      => null,
            "FilterCustomerCodes" => [$customerCode],
            "SortColumn"          => "IsAlertGenerator DESC, AlertOnDisplay DESC, IsOffline DESC",
            "SortOrder"           => 0,
            "PageNumber"          => $page,
            "PageRows"            => $rows
        ]);
        return [
            'devices' => $resp['Result']['Data']   ?? [],
            'total'   => $resp['Result']['RowCount'] ?? 0
        ];
    }

    public function getDeviceDetails(string $deviceId): array {
        $this->authenticate();
        $resp = $this->postJson('Device/GetDetailedInformations', [
            "DealerCode" => $this->dealerCode,
            "Id"         => $deviceId
        ]);
        return $resp['Result'] ?? [];
    }
}

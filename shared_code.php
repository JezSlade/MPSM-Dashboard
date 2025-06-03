<?php
/**
 * -----------------------------------------------------------------------------
 * shared_code.php
 * -----------------------------------------------------------------------------
 * A standalone reference file containing all reusable PHP classes from src/.
 * Each block is labeled with its original filename, location, and usage.
 */

/**
 * -----------------------------------------------------------------------------
 * File: src/EnvLoader.php
 * Usage: Loads all key=value lines from .env into $_ENV
 */
class EnvLoader {
    public static function load(string $path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$name, $val] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($val);
        }
    }
}

/**
 * -----------------------------------------------------------------------------
 * File: src/Db.php
 * Usage: Establishes a PDO connection to MySQL using credentials from $_ENV.
 */
class Db {
    private static $pdo = null;
    public static function connect() {
        if (self::$pdo) return self::$pdo;
        $host    = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname  = $_ENV['DB_NAME'] ?? '';
        $user    = $_ENV['DB_USER'] ?? '';
        $pass    = $_ENV['DB_PASS'] ?? '';
        $charset = 'utf8mb4';
        $dsn     = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;
    }
}

/**
 * -----------------------------------------------------------------------------
 * File: src/DebugLogger.php
 * Usage: Static helper to append timestamped messages to a single log file.
 */
class DebugLogger {
    const LOGFILE = __DIR__ . '/../storage/debug.log';
    public static function log(string $message) {
        $time = date('Y-m-d H:i:s');
        error_log("[$time] $message\n", 3, self::LOGFILE);
    }
}

/**
 * -----------------------------------------------------------------------------
 * File: src/Auth.php
 * Usage: Handles session start, login checks, and isAdmin() stub.
 */
class Auth {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public static function checkLogin() {
        self::init();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }
    public static function isAdmin(): bool {
        self::init();
        return ($_SESSION['is_admin'] ?? false) === true;
    }
}

/**
 * -----------------------------------------------------------------------------
 * File: src/ApiClient.php
 * Usage: Centralizes all MPSM API calls (Auth/Login, GetCustomers, Device/List, etc.).
 */
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

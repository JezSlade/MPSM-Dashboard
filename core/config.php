<?php
/**
 * Configuration file for MPSM Dashboard
 */
class Config {
    private static $env = null;
    private static $db = null;
    
    /**
     * Initialize configuration
     */
    public static function init() {
        // Error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Session configuration
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Load environment variables
        self::loadEnv();
        
        // Initialize database connection
        self::initDatabase();
    }
    
    /**
     * Load environment variables from .env file
     */
    private static function loadEnv() {
        if (self::$env !== null) {
            return;
        }
        
        self::$env = [];
        
        if (file_exists('.env')) {
            $env = parse_ini_file('.env');
            foreach ($env as $key => $value) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                self::$env[$key] = $value;
            }
        }
    }
    
    /**
     * Initialize database connection
     */
    private static function initDatabase() {
        if (self::$db !== null) {
            return;
        }
        
        $db_host = self::$env['DB_HOST'] ?? 'localhost';
        $db_name = self::$env['DB_NAME'] ?? 'mpsm_dashboard';
        $db_user = self::$env['DB_USER'] ?? 'root';
        $db_pass = self::$env['DB_PASSWORD'] ?? '';
        
        try {
            self::$db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Make database available globally
            $GLOBALS['db'] = self::$db;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get environment variables
     * @return array
     */
    public static function getEnv() {
        if (self::$env === null) {
            self::loadEnv();
        }
        return self::$env;
    }
    
    /**
     * Get database connection
     * @return PDO
     */
    public static function getDB() {
        if (self::$db === null) {
            self::initDatabase();
        }
        return self::$db;
    }
    
    /**
     * Get site configuration
     * @return array
     */
    public static function getSiteConfig() {
        return [
            'name' => self::$env['SITE_NAME'] ?? 'MPSM Dashboard',
            'url' => self::$env['SITE_URL'] ?? 'http://localhost',
            'version' => '1.0.0'
        ];
    }
}

// Initialize configuration
Config::init();

// Initialize API client
require_once 'core/api.php';
$api_client = new ApiClient();

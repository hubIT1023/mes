<?php
// config/Database

require_once __DIR__ . '/EnvLoader.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
		
        // Load .env
        EnvLoader::load(__DIR__ . '/../../.env');

        $serverName = getenv('DB_SERVER');
        $database   = getenv('DB_DATABASE');
        $username   = getenv('DB_USERNAME');
        $password   = getenv('DB_PASSWORD');
        $useWindowsAuth = filter_var(getenv('DB_USE_WINDOWS_AUTH'), FILTER_VALIDATE_BOOLEAN);

        try {
            $dsn = "sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=true";

            if ($useWindowsAuth) {
                $this->conn = new PDO($dsn);
            } else {
                $this->conn = new PDO($dsn, $username, $password);
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die(" Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
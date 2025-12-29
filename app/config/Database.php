<?php
// config/Database.php

require_once __DIR__ . '/EnvLoader.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Load .env
        EnvLoader::load(__DIR__ . '/../../.env');

        $host     = getenv('DB_HOST') ?: 'postgres';      // Default to Docker service name
        $port     = getenv('DB_PORT') ?: '5432';
        $database = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        try {
            // Use pgsql driver for PostgreSQL
            $dsn = "pgsql:host=$host;port=$port;dbname=$database;";

            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
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
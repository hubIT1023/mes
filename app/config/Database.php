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

            // Run lightweight auto-migration
            $this->runAutoMigrations();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Checks database schema and adds any missing columns (col_17 to col_24) 
     * or tables/constraints if they don't exist.
     */
    private function runAutoMigrations(): void
    {
        try {
            // Fast check: check if machine_log table has col_20
            $stmt = $this->conn->query("
                SELECT EXISTS (
                    SELECT 1 
                    FROM information_schema.columns 
                    WHERE table_name = 'machine_log' AND column_name = 'col_20'
                )
            ");
            $hasCol20 = $stmt->fetchColumn();

            if (!$hasCol20) {
                // 1. Create tool_state_metadata if missing
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS tool_state_metadata (
                        org_id UUID NOT NULL REFERENCES organizations(org_id) ON DELETE CASCADE,
                        col_number VARCHAR(10) NOT NULL,
                        label VARCHAR(100) NOT NULL,
                        description VARCHAR(500),
                        data_type VARCHAR(50),
                        PRIMARY KEY (org_id, col_number)
                    )
                ");

                // 2. Adjust constraint on tool_state_metadata to allow columns up to col_24
                try {
                    $this->conn->exec("ALTER TABLE tool_state_metadata DROP CONSTRAINT IF EXISTS CHK_col_number;");
                    $this->conn->exec("ALTER TABLE tool_state_metadata ADD CONSTRAINT CHK_col_number CHECK (col_number ~ '^col_([1-9]|1[0-9]|2[0-4])$');");
                } catch (PDOException $e) {
                    // Ignore constraint adjustment errors
                }

                // 3. Create machine_log if missing
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS machine_log (
                        id SERIAL PRIMARY KEY,
                        org_id UUID NOT NULL REFERENCES organizations(org_id) ON DELETE CASCADE,
                        group_code VARCHAR(100),
                        location_code VARCHAR(100),
                        col_1 VARCHAR(255),
                        col_2 VARCHAR(255),
                        col_3 VARCHAR(100),
                        col_4 VARCHAR(100),
                        col_5 VARCHAR(100),
                        col_6 VARCHAR(50),
                        col_7 VARCHAR(100),
                        col_8 VARCHAR(100),
                        col_9 VARCHAR(100),
                        col_10 VARCHAR(100),
                        col_11 VARCHAR(100),
                        col_12 VARCHAR(100),
                        col_13 VARCHAR(100),
                        col_14 VARCHAR(100),
                        col_15 VARCHAR(100),
                        col_16 VARCHAR(100),
                        col_17 VARCHAR(100),
                        col_18 VARCHAR(100),
                        col_19 VARCHAR(100),
                        col_20 VARCHAR(100),
                        col_21 VARCHAR(100),
                        col_22 VARCHAR(100),
                        col_23 VARCHAR(100),
                        col_24 VARCHAR(100),
                        CONSTRAINT uq_machine_log_org_asset_ts UNIQUE (org_id, col_1, col_6)
                    )
                ");

                // 4. Add missing columns to tool_state
                for ($i = 17; $i <= 23; $i++) {
                    try {
                        $this->conn->exec("ALTER TABLE tool_state ADD COLUMN col_$i VARCHAR(100);");
                    } catch (PDOException $e) {
                        // Suppress if column already exists
                    }
                }

                // 5. Add missing columns to machine_log
                for ($i = 17; $i <= 24; $i++) {
                    try {
                        $this->conn->exec("ALTER TABLE machine_log ADD COLUMN col_$i VARCHAR(100);");
                    } catch (PDOException $e) {
                        // Suppress if column already exists
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Database auto-migration failed: " . $e->getMessage());
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
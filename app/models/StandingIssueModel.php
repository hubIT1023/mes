<?php
// app/models/StandingIssueModel.php

require_once __DIR__ . '/../config/Database.php';

class StandingIssueModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Insert a new standing issue record into machine_log
     */
    public function insertStandingIssue(array $data): void
    {
        $allowedColumns = [
            'org_id', 'group_code', 'location_code',
            'col_1', 'col_2', 'col_6', 'col_12', 'col_13',
            'col_14', 'col_15', 'col_16', 'col_17', 'col_18', 'col_19'
        ];

        // Filter to allowed columns only
        $insertData = array_intersect_key($data, array_flip($allowedColumns));

        // Separate NULL and non-NULL values for PostgreSQL compatibility
        $nonNullData = [];
        $placeholders = [];

        foreach ($insertData as $key => $value) {
            if ($value === null) {
                $placeholders[] = 'NULL';
            } else {
                $placeholders[] = ":{$key}";
                $nonNullData[$key] = $value;
            }
        }

        $columns = implode(', ', array_keys($insertData));
        $placeholderStr = implode(', ', $placeholders);

        $sql = "INSERT INTO machine_log ({$columns}) VALUES ({$placeholderStr})";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($nonNullData);
    }
}
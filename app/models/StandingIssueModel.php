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
        // Explicitly list allowed columns to prevent injection via extra POST fields
        $columns = [
            'org_id',
            'group_code',
            'location_code',
            'col_1',
            'col_2',
            'col_6',
            'col_12',
            'col_13',
            'col_14',
            'col_15',
            'col_16',
            'col_17',
            'col_18',
            'col_19'
        ];

        // Build dynamic INSERT with only allowed keys
        $insertData = array_intersect_key($data, array_flip($columns));

        // Remove any null values (PostgreSQL handles NULL, but optional)
        // Alternatively, keep them — your schema allows NULL

        $placeholders = ':' . implode(', :', array_keys($insertData));
        $cols = implode(', ', array_keys($insertData));

        $sql = "INSERT INTO machine_log ({$cols}) VALUES ({$placeholders})";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($insertData);
    }
}
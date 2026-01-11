<?php
// app/models/AnalyticsModel.php

require_once __DIR__ . '/../config/Database.php';

class AnalyticsModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * MTBF — Mean Time Between Failures (in hours)
     */
    public function getMTBF(string $orgId, array $filters = []): array
    {
        $sql = "
            SELECT
                col_1 AS asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (next_fail - fail)) / 3600), 2) AS mtbf_hours
            FROM (
                SELECT
                    col_1,
                    col_6::timestamp AS fail,
                    LEAD(col_6::timestamp)
                        OVER (PARTITION BY col_1 ORDER BY col_6) AS next_fail
                FROM machine_log
                WHERE org_id = :org_id
                  AND col_3 = 'FAIL'
            ) t
            WHERE next_fail IS NOT NULL
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $sql .= " AND col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        $sql .= " GROUP BY col_1 ORDER BY col_1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * MTTR — Mean Time To Repair (in hours)
     */
    public function getMTTR(string $orgId, array $filters = []): array
    {
        $sql = "
            SELECT
                asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (repair - failure)) / 3600), 2) AS mttr_hours
            FROM (
                SELECT
                    col_1 AS asset_id,
                    col_6::timestamp AS failure,
                    LEAD(col_6::timestamp)
                        OVER (PARTITION BY col_1 ORDER BY col_6) AS repair
                FROM machine_log
                WHERE org_id = :org_id
                  AND col_3 IN ('FAIL','PROD')
            ) t
            WHERE repair IS NOT NULL
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $sql .= " AND asset_id = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        $sql .= " GROUP BY asset_id ORDER BY asset_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

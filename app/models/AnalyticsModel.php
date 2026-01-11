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
     * Average time between consecutive FAIL events for each asset.
     */
    public function getMTBF(string $orgId, array $filters = []): array
    {
        $sql = "
            SELECT
                t.asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (t.next_fail - t.fail)) / 3600), 2) AS mtbf_hours
            FROM (
                SELECT
                    col_1 AS asset_id,
                    col_6::timestamp AS fail,
                    LEAD(col_6::timestamp) OVER (PARTITION BY col_1 ORDER BY col_6) AS next_fail
                FROM machine_log
                WHERE org_id = :org_id
                  AND col_10 = 'MAINT-COR'
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $sql .= " AND col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        $sql .= "
            ) t
            WHERE t.next_fail IS NOT NULL
            GROUP BY t.asset_id
            ORDER BY t.asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * MTTR — Mean Time To Repair (in hours)
     * Average time from each FAIL event to the next PROD event for the same asset.
     */
    public function getMTTR(string $orgId, array $filters = []): array
    {
        $sql = "
            SELECT
                t.asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (t.repair_time - t.failure_time)) / 3600), 2) AS mttr_hours
            FROM (
                SELECT
                    ml1.col_1 AS asset_id,
                    ml1.col_6::timestamp AS failure_time,
                    (
                        SELECT MIN(ml2.col_6::timestamp)
                        FROM machine_log ml2
                        WHERE ml2.org_id = ml1.org_id
                          AND ml2.col_1 = ml1.col_1
                          AND ml2.col_6 > ml1.col_6
                          AND ml2.col_3 = 'PROD'
                    ) AS repair_time
                FROM machine_log ml1
                WHERE ml1.org_id = :org_id
                  AND ml1.col_10 = 'MAINT-COR'
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $sql .= " AND ml1.col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        $sql .= "
            ) t
            WHERE t.repair_time IS NOT NULL
            GROUP BY t.asset_id
            ORDER BY t.asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
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

    public function getAvailability(array $mtbf, array $mttr): array
    {
        $mtbfMap = array_column($mtbf, 'mtbf_hours', 'asset_id');
        $mttrMap = array_column($mttr, 'mttr_hours', 'asset_id');
        $availability = [];

        foreach (array_keys($mtbfMap) as $assetId) {
            if (isset($mttrMap[$assetId])) {
                $mtbfVal = (float)$mtbfMap[$assetId];
                $mttrVal = (float)$mttrMap[$assetId];
                if ($mtbfVal + $mttrVal > 0) {
                    $pct = ($mtbfVal / ($mtbfVal + $mttrVal)) * 100;
                    $availability[] = [
                        'asset_id' => $assetId,
                        'availability_pct' => round($pct, 2)
                    ];
                }
            }
        }

        usort($availability, fn($a, $b) => strcmp($a['asset_id'], $b['asset_id']));
        return $availability;
    }
}
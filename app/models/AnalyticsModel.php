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
                t.entity,
                ROUND(AVG(EXTRACT(EPOCH FROM (t.next_fail - t.fail)) / 3600), 2) AS mtbf_hours
            FROM (
                SELECT
                    col_2 AS entity,
                    col_6::timestamp AS fail,
                    LEAD(col_6::timestamp) OVER (PARTITION BY col_2 ORDER BY col_6) AS next_fail
                FROM machine_log
                WHERE org_id = :org_id
                  AND col_10 = 'MAINT-COR'
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['entity'])) {
            $sql .= " AND col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }

        $sql .= "
            ) t
            WHERE t.next_fail IS NOT NULL
            GROUP BY t.entity
            ORDER BY t.entity
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMTTR(string $orgId, array $filters = []): array
    {
        $sql = "
            SELECT
                t.entity,
                ROUND(AVG(EXTRACT(EPOCH FROM (t.repair_time - t.failure_time)) / 3600), 2) AS mttr_hours
            FROM (
                SELECT
                    ml1.col_2 AS entity,
                    ml1.col_6::timestamp AS failure_time,
                    (
                        SELECT MIN(ml2.col_6::timestamp)
                        FROM machine_log ml2
                        WHERE ml2.org_id = ml1.org_id
                          AND ml2.col_2 = ml1.col_2
                          AND ml2.col_6 > ml1.col_6
                          AND ml2.col_3 = 'PROD'
                    ) AS repair_time
                FROM machine_log ml1
                WHERE ml1.org_id = :org_id
                  AND ml1.col_10 = 'MAINT-COR'
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['entity'])) {
            $sql .= " AND ml1.col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }

        $sql .= "
            ) t
            WHERE t.repair_time IS NOT NULL
            GROUP BY t.entity
            ORDER BY t.entity
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailability(array $mtbf, array $mttr): array
    {
        $mtbfMap = array_column($mtbf, 'mtbf_hours', 'entity');
        $mttrMap = array_column($mttr, 'mttr_hours', 'entity');
        $availability = [];

        foreach (array_keys($mtbfMap) as $entity) {
            if (isset($mttrMap[$entity])) {
                $mtbfVal = (float)$mtbfMap[$entity];
                $mttrVal = (float)$mttrMap[$entity];
                if ($mtbfVal + $mttrVal > 0) {
                    $pct = ($mtbfVal / ($mtbfVal + $mttrVal)) * 100;
                    $availability[] = [
                        'entity' => $entity,
                        'availability_pct' => round($pct, 2)
                    ];
                }
            }
        }

        usort($availability, fn($a, $b) => strcmp($a['entity'], $b['entity']));
        return $availability;
    }

    public function getReliabilityByDate(string $orgId, array $filters = []): array
    {
        // Build filter condition safely
        $entityCondition = '';
        if (!empty($filters['entity'])) {
            $entityCondition = "AND ml.col_2 = :entity";
        }

        $sql = "
            WITH failures AS (
                SELECT
                    ml.col_2 AS entity,
                    ml.col_6::timestamp AS fail_time,
                    LEAD(ml.col_6::timestamp) OVER (PARTITION BY ml.col_2 ORDER BY ml.col_6) AS next_fail
                FROM machine_log ml
                WHERE ml.org_id = :org_id
                  AND ml.col_10 = 'MAINT-COR'
                  $entityCondition
            ),
            mtbf_data AS (
                SELECT
                    DATE(fail_time) AS date,
                    AVG(EXTRACT(EPOCH FROM (next_fail - fail_time)) / 3600) AS avg_mtbf_hours
                FROM failures
                WHERE next_fail IS NOT NULL
                GROUP BY DATE(fail_time)
            ),
            repairs AS (
                SELECT
                    ml1.col_2 AS entity,
                    ml1.col_6::timestamp AS failure_time,
                    (
                        SELECT MIN(ml2.col_6::timestamp)
                        FROM machine_log ml2
                        WHERE ml2.org_id = ml1.org_id
                          AND ml2.col_2 = ml1.col_2
                          AND ml2.col_6 > ml1.col_6
                          AND ml2.col_3 = 'PROD'
                    ) AS repair_time
                FROM machine_log ml1
                WHERE ml1.org_id = :org_id
                  AND ml1.col_10 = 'MAINT-COR'
                  $entityCondition
            ),
            mttr_data AS (
                SELECT
                    DATE(failure_time) AS date,
                    AVG(EXTRACT(EPOCH FROM (repair_time - failure_time)) / 3600) AS avg_mttr_hours
                FROM repairs
                WHERE repair_time IS NOT NULL
                GROUP BY DATE(failure_time)
            )
            SELECT
                COALESCE(m.date, t.date) AS date,
                ROUND(COALESCE(m.avg_mtbf_hours, 0), 2) AS mtbf_hours,
                ROUND(COALESCE(t.avg_mttr_hours, 0), 2) AS mttr_hours,
                ROUND(
                    CASE 
                        WHEN COALESCE(m.avg_mtbf_hours, 0) + COALESCE(t.avg_mttr_hours, 0) > 0
                        THEN (COALESCE(m.avg_mtbf_hours, 0) / (COALESCE(m.avg_mtbf_hours, 0) + COALESCE(t.avg_mttr_hours, 0))) * 100
                        ELSE 0
                    END, 2
                ) AS availability_pct
            FROM mtbf_data m
            FULL OUTER JOIN mttr_data t ON m.date = t.date
            ORDER BY date DESC
            LIMIT 7
        ";

        $params = ['org_id' => $orgId];
        if (!empty($filters['entity'])) {
            $params['entity'] = $filters['entity'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueEntities(string $orgId): array
    {
        $sql = "
            SELECT DISTINCT col_2
            FROM tool_state
            WHERE org_id = :org_id
              AND col_2 IS NOT NULL
              AND TRIM(col_2) <> ''
            ORDER BY col_2
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['org_id' => $orgId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
<?php

require_once __DIR__ . '/../config/Database.php';

class AnalyticsModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /* =========================================================
       INTERNAL WHERE BUILDER (SAFE & REUSABLE)
    ========================================================= */
    private function buildWhere(string $orgId, array $filters = [], string $alias = ''): array
    {
        $where = ["{$alias}org_id = :org_id"];
        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $where[] = "{$alias}col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        if (!empty($filters['entity'])) {
            $where[] = "{$alias}col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }

        return [implode(' AND ', $where), $params];
    }

    /* =========================================================
       MTBF — Mean Time Between Failures (HOURS)
    ========================================================= */
    public function getMTBF(string $orgId, array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($orgId, $filters);

        $sql = "
            SELECT
                asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (next_fail - fail_time)) / 3600), 2) AS mtbf_hours
            FROM (
                SELECT
                    col_1 AS asset_id,
                    col_6::timestamp AS fail_time,
                    LEAD(col_6::timestamp)
                        OVER (PARTITION BY col_1 ORDER BY col_6::timestamp) AS next_fail
                FROM machine_log
                WHERE $where
                  AND col_10 = 'MAINT-COR'
            ) t
            WHERE next_fail IS NOT NULL
            GROUP BY asset_id
            ORDER BY asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       MTTR — Mean Time To Repair (HOURS)
    ========================================================= */
    public function getMTTR(string $orgId, array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($orgId, $filters, 'ml1.');

        $sql = "
            SELECT
                asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (repair_time - failure_time)) / 3600), 2) AS mttr_hours
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
                WHERE $where
                  AND ml1.col_10 = 'MAINT-COR'
            ) t
            WHERE repair_time IS NOT NULL
            GROUP BY asset_id
            ORDER BY asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       AVAILABILITY %
       Availability = MTBF / (MTBF + MTTR) * 100
    ========================================================= */
    public function getAvailability(array $mtbf, array $mttr): array
    {
        $mtbfMap = array_column($mtbf, 'mtbf_hours', 'asset_id');
        $mttrMap = array_column($mttr, 'mttr_hours', 'asset_id');
        $result = [];

        foreach ($mtbfMap as $asset => $mtbfVal) {
            if (!isset($mttrMap[$asset])) {
                continue;
            }

            $mttrVal = (float)$mttrMap[$asset];
            $total = $mtbfVal + $mttrVal;

            if ($total > 0) {
                $result[] = [
                    'asset_id' => $asset,
                    'availability_pct' => round(($mtbfVal / $total) * 100, 2)
                ];
            }
        }

        usort($result, fn($a, $b) => strcmp($a['asset_id'], $b['asset_id']));
        return $result;
    }

    /* =========================================================
       RELIABILITY OVER TIME (DAILY)
       - SAFE timestamp math
       - Supports asset + entity filters
    ========================================================= */
    public function getReliabilityByDate(string $orgId, array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($orgId, $filters);

        $sql = "
            WITH failures AS (
                SELECT
                    col_6::timestamp AS fail_time,
                    LEAD(col_6::timestamp) OVER (ORDER BY col_6::timestamp) AS next_fail
                FROM machine_log
                WHERE $where
                  AND col_10 = 'MAINT-COR'
            )
            SELECT
                DATE(fail_time) AS date,
                ROUND(AVG(EXTRACT(EPOCH FROM (next_fail - fail_time)) / 3600), 2) AS mtbf_hours,
                0 AS mttr_hours,
                0 AS availability_pct
            FROM failures
            WHERE next_fail IS NOT NULL
            GROUP BY DATE(fail_time)
            ORDER BY date DESC
            LIMIT 30
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       ENTITY LIST (FILTER DROPDOWN)
    ========================================================= */
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

<?php

require_once __DIR__ . '/../config/Database.php';

class AnalyticsModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    private function applyFilters(array &$sql, array &$params, array $filters, string $alias = ''): void
    {
        if (!empty($filters['asset_id'])) {
            $sql[] = "{$alias}col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }
        if (!empty($filters['entity'])) {
            $sql[] = "{$alias}col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }
    }

    public function getMTBF(string $orgId, array $filters = []): array
    {
        $conditions = ["ml.org_id = :org_id", "ml.col_10 = 'MAINT-COR'"];
        $params = ['org_id' => $orgId];
        $this->applyFilters($conditions, $params, $filters, 'ml.');

        $sql = "
            SELECT ml.col_1 AS asset_id,
                   ROUND(AVG(EXTRACT(EPOCH FROM (next_fail - fail)) / 3600), 2) AS mtbf_hours
            FROM (
                SELECT col_1, col_6::timestamp AS fail,
                       LEAD(col_6::timestamp) OVER (PARTITION BY col_1 ORDER BY col_6) AS next_fail
                FROM machine_log ml
                WHERE " . implode(' AND ', $conditions) . "
            ) t
            WHERE next_fail IS NOT NULL
            GROUP BY asset_id
            ORDER BY asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMTTR(string $orgId, array $filters = []): array
    {
        $conditions = ["ml1.org_id = :org_id", "ml1.col_10 = 'MAINT-COR'"];
        $params = ['org_id' => $orgId];
        $this->applyFilters($conditions, $params, $filters, 'ml1.');

        $sql = "
            SELECT asset_id,
                   ROUND(AVG(EXTRACT(EPOCH FROM (repair_time - failure_time)) / 3600), 2) AS mttr_hours
            FROM (
                SELECT ml1.col_1 AS asset_id,
                       ml1.col_6::timestamp AS failure_time,
                       (
                           SELECT MIN(ml2.col_6::timestamp)
                           FROM machine_log ml2
                           WHERE ml2.col_1 = ml1.col_1
                             AND ml2.col_6 > ml1.col_6
                             AND ml2.col_3 = 'PROD'
                       ) AS repair_time
                FROM machine_log ml1
                WHERE " . implode(' AND ', $conditions) . "
            ) t
            WHERE repair_time IS NOT NULL
            GROUP BY asset_id
            ORDER BY asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailability(array $mtbf, array $mttr): array
    {
        $mapBF = array_column($mtbf, 'mtbf_hours', 'asset_id');
        $mapTR = array_column($mttr, 'mttr_hours', 'asset_id');
        $out = [];

        foreach ($mapBF as $asset => $bf) {
            if (!isset($mapTR[$asset])) continue;
            $pct = ($bf + $mapTR[$asset]) > 0
                ? ($bf / ($bf + $mapTR[$asset])) * 100
                : 0;
            $out[] = ['asset_id' => $asset, 'availability_pct' => round($pct, 2)];
        }

        return $out;
    }

    public function getReliabilityByDate(string $orgId, array $filters = []): array
    {
        $conditions = ["org_id = :org_id", "col_10 = 'MAINT-COR'"];
        $params = ['org_id' => $orgId];
        $this->applyFilters($conditions, $params, $filters);

        $sql = "
            SELECT DATE(col_6) AS date,
                   ROUND(AVG(mtbf),2) AS mtbf_hours,
                   ROUND(AVG(mttr),2) AS mttr_hours,
                   ROUND(AVG(availability),2) AS availability_pct
            FROM (
                SELECT col_6,
                       EXTRACT(EPOCH FROM (LEAD(col_6) OVER w - col_6)) / 3600 AS mtbf,
                       EXTRACT(EPOCH FROM (
                           (SELECT MIN(col_6) FROM machine_log m2
                            WHERE m2.col_1 = m.col_1 AND m2.col_6 > m.col_6 AND m2.col_3 = 'PROD')
                           - col_6)) / 3600 AS mttr,
                       0 AS availability
                FROM machine_log m
                WHERE " . implode(' AND ', $conditions) . "
                WINDOW w AS (PARTITION BY col_1 ORDER BY col_6)
            ) t
            GROUP BY DATE(col_6)
            ORDER BY date DESC
            LIMIT 30
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueEntities(string $orgId): array
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT col_2
            FROM tool_state
            WHERE org_id = :org_id
              AND col_2 IS NOT NULL
              AND TRIM(col_2) != ''
            ORDER BY col_2
        ");
        $stmt->execute(['org_id' => $orgId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

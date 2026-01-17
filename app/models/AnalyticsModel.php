<<?php

require_once __DIR__ . '/../config/Database.php';

class AnalyticsModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /* ============================
       MTBF — Mean Time Between Failures
       ============================ */
    public function getMTBF(string $orgId, array $filters = []): array
    {
        $where = [
            "org_id = :org_id",
            "col_10 = 'MAINT-COR'"
        ];

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $where[] = "col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        if (!empty($filters['entity'])) {
            $where[] = "col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }

        $sql = "
            SELECT
                asset_id,
                ROUND(AVG(EXTRACT(EPOCH FROM (next_fail - fail_time)) / 3600), 2) AS mtbf_hours
            FROM (
                SELECT
                    col_1 AS asset_id,
                    col_6::timestamp AS fail_time,
                    LEAD(col_6::timestamp)
                        OVER (PARTITION BY col_1 ORDER BY col_6) AS next_fail
                FROM machine_log
                WHERE " . implode(' AND ', $where) . "
            ) t
            WHERE next_fail IS NOT NULL
            GROUP BY asset_id
            ORDER BY asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================
       MTTR — Mean Time To Repair
       ============================ */
    public function getMTTR(string $orgId, array $filters = []): array
    {
        $where = [
            "ml1.org_id = :org_id",
            "ml1.col_10 = 'MAINT-COR'"
        ];

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $where[] = "ml1.col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        if (!empty($filters['entity'])) {
            $where[] = "ml1.col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }

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
                        WHERE ml2.col_1 = ml1.col_1
                          AND ml2.col_6 > ml1.col_6
                          AND ml2.col_3 = 'PROD'
                    ) AS repair_time
                FROM machine_log ml1
                WHERE " . implode(' AND ', $where) . "
            ) t
            WHERE repair_time IS NOT NULL
            GROUP BY asset_id
            ORDER BY asset_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================
       Availability %
       ============================ */
    public function getAvailability(array $mtbf, array $mttr): array
    {
        $bf = array_column($mtbf, 'mtbf_hours', 'asset_id');
        $tr = array_column($mttr, 'mttr_hours', 'asset_id');
        $out = [];

        foreach ($bf as $asset => $mtbfVal) {
            if (!isset($tr[$asset])) continue;

            $mttrVal = $tr[$asset];
            $pct = ($mtbfVal + $mttrVal) > 0
                ? ($mtbfVal / ($mtbfVal + $mttrVal)) * 100
                : 0;

            $out[] = [
                'asset_id' => $asset,
                'availability_pct' => round($pct, 2)
            ];
        }

        return $out;
    }

    /* ============================
       Reliability Time Series (Daily)
       ============================ */
    public function getReliabilityByDate(string $orgId, array $filters = []): array
	{
		$where = [
			"org_id = :org_id",
			"col_10 = 'MAINT-COR'"
		];

		$params = ['org_id' => $orgId];

		if (!empty($filters['asset_id'])) {
			$where[] = "col_1 = :asset_id";
			$params['asset_id'] = $filters['asset_id'];
		}

		if (!empty($filters['entity'])) {
			$where[] = "col_2 = :entity";
			$params['entity'] = $filters['entity'];
		}

		$sql = "
			WITH failures AS (
				SELECT
					col_1 AS asset_id,
					col_6::timestamp AS fail_time,
					LEAD(col_6::timestamp)
						OVER (PARTITION BY col_1 ORDER BY col_6::timestamp) AS next_fail
				FROM machine_log
				WHERE " . implode(' AND ', $where) . "
			),
			mtbf AS (
				SELECT
					DATE(fail_time) AS date,
					AVG(EXTRACT(EPOCH FROM (next_fail - fail_time)) / 3600) AS mtbf_hours
				FROM failures
				WHERE next_fail IS NOT NULL
				GROUP BY DATE(fail_time)
			),
			mttr AS (
				SELECT
					DATE(f.col_6::timestamp) AS date,
					AVG(
						EXTRACT(EPOCH FROM (
							(
								SELECT MIN(m2.col_6::timestamp)
								FROM machine_log m2
								WHERE m2.col_1 = f.col_1
								  AND m2.col_6::timestamp > f.col_6::timestamp
								  AND m2.col_3 = 'PROD'
							) - f.col_6::timestamp
						)) / 3600
					) AS mttr_hours
				FROM machine_log f
				WHERE " . implode(' AND ', $where) . "
				GROUP BY DATE(f.col_6::timestamp)
			)
			SELECT
				COALESCE(m.date, t.date) AS date,
				ROUND(COALESCE(m.mtbf_hours, 0), 2) AS mtbf_hours,
				ROUND(COALESCE(t.mttr_hours, 0), 2) AS mttr_hours,
				ROUND(
					CASE
						WHEN COALESCE(m.mtbf_hours,0) + COALESCE(t.mttr_hours,0) > 0
						THEN (COALESCE(m.mtbf_hours,0) /
							 (COALESCE(m.mtbf_hours,0) + COALESCE(t.mttr_hours,0))) * 100
						ELSE 0
					END, 2
				) AS availability_pct
			FROM mtbf m
			FULL OUTER JOIN mttr t ON m.date = t.date
			ORDER BY date DESC
			LIMIT 30
		";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


    /* ============================
       Unique Entities
       ============================ */
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

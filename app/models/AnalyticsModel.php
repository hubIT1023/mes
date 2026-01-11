<?php
// app/models/AnalyticsModel.php

class AnalyticsModel
{
    // ... existing methods ...

    /**
     * Get MTBF, MTTR, and Availability aggregated by day (for time-series chart)
     */
    public function getReliabilityByDate(string $orgId, array $filters = []): array
    {
        $sql = "
            WITH failures AS (
                SELECT
                    col_1 AS asset_id,
                    col_6::timestamp AS fail_time,
                    LEAD(col_6::timestamp) OVER (PARTITION BY col_1 ORDER BY col_6) AS next_fail
                FROM machine_log
                WHERE org_id = :org_id
                  AND col_10 = 'MAINT-COR'
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
            LIMIT 7  -- last 7 days; adjust as needed
        ";

        $params = ['org_id' => $orgId];

        if (!empty($filters['asset_id'])) {
            $sql = str_replace(
                "WHERE org_id = :org_id",
                "WHERE org_id = :org_id AND col_1 = :asset_id",
                $sql
            );
            $params['asset_id'] = $filters['asset_id'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
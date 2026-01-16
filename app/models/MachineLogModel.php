<?php
require_once __DIR__ . '/../config/Database.php';

class MachineLogModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getLogs(string $orgId, array $filters = []): array
    {
        $sql = "
            SELECT
                col_6  AS event_time,
                group_code,
                location_code,
                col_1  AS asset_id,
                col_2  AS entity,
                col_3  AS stopcause,
                col_4  AS reason,
                col_5  AS action,
                col_8  AS reported_by,
                col_9  AS started_by,
                col_10 AS stopcause_start,
                col_11 AS status
            FROM machine_log
            WHERE org_id = :org_id
        ";

        $params = ['org_id' => $orgId];

        // Asset ID filter
        if (!empty($filters['asset_id'])) {
            $sql .= " AND col_1 = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }

        // ✅ Entity filter (FIXED)
        if (!empty($filters['entity'])) {
            $sql .= " AND col_2 = :entity";
            $params['entity'] = $filters['entity'];
        }

        // Stop cause start filter
        if (!empty($filters['stopcause_start'])) {
            $sql .= " AND col_10 = :stopcause_start";
            $params['stopcause_start'] = $filters['stopcause_start'];
        }

        // Date range filters
        if (!empty($filters['from'])) {
            $sql .= " AND col_6 >= :from";
            $params['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $sql .= " AND col_6 <= :to";
            $params['to'] = $filters['to'];
        }

        $sql .= " ORDER BY col_6 DESC LIMIT 1000";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

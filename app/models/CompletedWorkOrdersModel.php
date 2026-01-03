<?php
// app/models/CompletedWorkOrdersModel.php
require_once __DIR__ . '/../config/Database.php';

class CompletedWorkOrdersModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getCompletedWorkOrders(
        string $tenantId,
        string $workOrderRef = '',
        string $assetId = '',
        string $dateFrom = '',
        string $dateTo = '',
        int $page = 1,
        int $limit = 20
    ): array 
    {
        $tenantId = (int)$tenantId;
        $params = [$tenantId];

        $sql = "
            SELECT 
                maintenance_checklist_id,
                work_order_ref,
                asset_id,
                asset_name,
                checklist_id,
                technician_name,
                date_completed,
                created_at AS archived_at
            FROM completed_work_order
            WHERE tenant_id = ?
        ";

        if ($workOrderRef !== '') {
            $sql .= " AND work_order_ref ILIKE ?";
            $params[] = "%{$workOrderRef}%";
        }

        if ($assetId !== '') {
            $sql .= " AND asset_id ILIKE ?";
            $params[] = "%{$assetId}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND date_completed::date >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND date_completed::date <= ?";
            $params[] = $dateTo;
        }

        // Count total
        $countSql = "SELECT COUNT(*) FROM ({$sql}) AS t";
        $stmt = $this->conn->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        if ($total === 0) {
            return ['data' => [], 'total' => 0];
        }

        $offset = ($page - 1) * $limit;
        $sql .= " ORDER BY date_completed DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['data' => $data, 'total' => $total];
    }

    public function getCompletedWorkOrderDetails(string $tenantId, int $archiveId): ?array
    {
        $tenantId = (int)$tenantId;

        $sqlMaster = "
            SELECT *
            FROM completed_work_order
            WHERE maintenance_checklist_id = ? AND tenant_id = ?
        ";
        $stmt = $this->conn->prepare($sqlMaster);
        $stmt->execute([$archiveId, $tenantId]);
        $master = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$master) return null;

        $sqlTasks = "
            SELECT *
            FROM completed_work_order_tasks
            WHERE maintenance_checklist_id = ?
            ORDER BY task_order ASC NULLS LAST
        ";
        $stmt2 = $this->conn->prepare($sqlTasks);
        $stmt2->execute([$archiveId]);
        $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return ['master' => $master, 'tasks' => $tasks];
    }
}

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

    /**
     * LIST COMPLETED WORK ORDERS (FILTERED + PAGINATED) - POSTGRESQL
     */
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
        // Ensure tenantId is integer for PostgreSQL
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

        // Case-insensitive search
        if ($workOrderRef !== '') {
            $sql .= " AND work_order_ref ILIKE ?";
            $params[] = "%{$workOrderRef}%";
        }

        if ($assetId !== '') {
            $sql .= " AND asset_id ILIKE ?";
            $params[] = "%{$assetId}%";
        }

        // Date filters - cast date_completed to date for comparison
        if ($dateFrom !== '') {
            $sql .= " AND date_completed::date >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND date_completed::date <= ?";
            $params[] = $dateTo;
        }

        // Total count
        $countSql = "SELECT COUNT(*) FROM ({$sql}) AS subquery_alias";
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        if ($total === 0) {
            return ['data' => [], 'total' => 0];
        }

        // Pagination
        $offset = ($page - 1) * $limit;
        $sql .= " ORDER BY date_completed DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debugging (uncomment if needed)
        // error_log("SQL Executed: $sql");
        // error_log("Params: " . json_encode($params));

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * GET COMPLETED WORK ORDER DETAILS (MASTER + TASKS) - POSTGRESQL
     */
    public function getCompletedWorkOrderDetails(string $tenantId, int $archiveId): ?array
    {
        $tenantId = (int)$tenantId;

        // Master record
        $sqlMaster = "
            SELECT *
            FROM completed_work_order
            WHERE maintenance_checklist_id = ? AND tenant_id = ?
        ";
        $stmt = $this->conn->prepare($sqlMaster);
        $stmt->execute([$archiveId, $tenantId]);
        $master = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$master) {
            return null;
        }

        // Tasks associated with this work order
        $sqlTasks = "
            SELECT *
            FROM completed_work_order_tasks
            WHERE maintenance_checklist_id = ?
            ORDER BY task_order ASC NULLS LAST
        ";
        $stmt2 = $this->conn->prepare($sqlTasks);
        $stmt2->execute([$archiveId]);
        $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return [
            'master' => $master,
            'tasks' => $tasks
        ];
    }
}

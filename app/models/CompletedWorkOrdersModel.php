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
     * LIST COMPLETED WORK ORDERS (FILTERED + PAGINATED) - PostgreSQL
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
        $tenantId = (int)$tenantId; // ensure tenant ID is integer
        $params = [$tenantId];

        // Base SQL
        $sql = "
            SELECT 
                cwo.maintenance_checklist_id,
                cwo.work_order_ref,
                cwo.asset_id,
                cwo.asset_name,
                cwo.checklist_id,
                cwo.technician_name,
                cwo.date_completed,
                cwo.created_at AS archived_at
            FROM completed_work_order cwo
            WHERE cwo.tenant_id = ?
        ";

        // Filters
        if ($workOrderRef !== '') {
            $sql .= " AND cwo.work_order_ref ILIKE ?";
            $params[] = "%{$workOrderRef}%";
        }

        if ($assetId !== '') {
            $sql .= " AND cwo.asset_id ILIKE ?";
            $params[] = "%{$assetId}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND cwo.date_completed::date >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND cwo.date_completed::date <= ?";
            $params[] = $dateTo;
        }

        // Count total records
        $countSql = "SELECT COUNT(*) FROM ({$sql}) AS t";
        $stmt = $this->conn->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        if ($total === 0) {
            return ['data' => [], 'total' => 0];
        }

        // PostgreSQL pagination
        $offset = ($page - 1) * $limit;
        $sql .= " ORDER BY cwo.date_completed DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * GET COMPLETED WORK ORDER DETAILS (MASTER + TASKS) - PostgreSQL
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

        // Tasks
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

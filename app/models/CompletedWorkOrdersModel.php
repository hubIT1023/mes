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
        string $tenantId = '',
        string $workOrderRef = '',
        string $assetId = '',
        string $dateFrom = '',
        string $dateTo = '',
        int $page = 1,
        int $limit = 20
    ): array 
    {
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
            FROM public.completed_work_order
            WHERE 1=1
        ";

        $params = [];

        if ($tenantId !== '') {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        if ($workOrderRef !== '') {
            $sql .= " AND work_order_ref ILIKE ?";
            $params[] = "%{$workOrderRef}%";
        }

        if ($assetId !== '') {
            $sql .= " AND asset_id ILIKE ?";
            $params[] = "%{$assetId}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND date_completed >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND date_completed <= ?";
            $params[] = $dateTo . " 23:59:59";
        }

        // Total count
        $countSql = "SELECT COUNT(*) FROM ($sql) AS subquery";
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        if ($total === 0) {
            return ['data' => [], 'total' => 0];
        }

        // ✅ CORRECTED: Sort by date descending (newest first)
        $offset = ($page - 1) * $limit;
        $sql .= " ORDER BY date_completed DESC, maintenance_checklist_id DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * ✅ FIXED: Parameter order matches controller call
     */
    public function getCompletedWorkOrderDetails(string $tenantId, int $archiveId): ?array
    {
        $sqlMaster = "
            SELECT *
            FROM public.completed_work_order
            WHERE tenant_id = ? AND maintenance_checklist_id = ?
        ";

        $stmt = $this->conn->prepare($sqlMaster);
        $stmt->execute([$tenantId, $archiveId]); // ✅ tenantId first, archiveId second
        $master = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$master) return null;

        $sqlTasks = "
            SELECT *
            FROM public.completed_work_order_tasks
            WHERE maintenance_checklist_id = ?
            ORDER BY task_order ASC
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
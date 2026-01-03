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
        // ✅ USE ACTUAL TABLE NAME: maintenance_checklists
        $sql = "
            SELECT 
                maintenance_checklist_id,
                work_order_ref,        -- Verify this column exists
                asset_id,
                asset_name,
                checklist_id,
                technician,            -- Most likely column name (not technician_name)
                date_completed,
                archived_at            -- Most likely column name (not created_at)
            FROM maintenance_checklists
            WHERE tenant_id = ?
              AND status = 'completed' -- Only completed records
        ";

        $params = [$tenantId];

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

        // Get total count
        $countSql = "SELECT COUNT(*) FROM ($sql) AS sub";
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        if ($total === 0) {
            return ['data' => [], 'total' => 0];
        }

        // PostgreSQL pagination
        $offset = ($page - 1) * $limit;
        $sql .= " ORDER BY date_completed DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['data' => $data, 'total' => $total];
    }

    public function getCompletedWorkOrderDetails(string $tenantId, int $archiveId): ?array
    {
        // ✅ Query actual table
        $stmt = $this->conn->prepare("
            SELECT * 
            FROM maintenance_checklists
            WHERE maintenance_checklist_id = ? AND tenant_id = ?
        ");
        $stmt->execute([$archiveId, $tenantId]);
        $master = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$master) return null;

        // ✅ Task table (if exists)
        $tasks = [];
        if ($this->tableExists('maintenance_checklist_tasks')) {
            $stmt2 = $this->conn->prepare("
                SELECT * FROM maintenance_checklist_tasks
                WHERE maintenance_checklist_id = ?
                ORDER BY task_order ASC
            ");
            $stmt2->execute([$archiveId]);
            $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return ['master' => $master, 'tasks' => $tasks];
    }

    private function tableExists(string $tableName): bool {
        $stmt = $this->conn->prepare("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = ?
            );
        ");
        $stmt->execute([$tableName]);
        return (bool) $stmt->fetchColumn();
    }
}
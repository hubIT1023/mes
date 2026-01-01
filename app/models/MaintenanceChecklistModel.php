<?php
// app/models/MaintenanceChecklistModel.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/logger.php';

class MaintenanceChecklistModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Check if a checklist instance exists
     */
    public function isChecklistInstanceExists(string $asset_id, string $checklist_id, string $work_order_ref): bool
    {
        $sql = "
            SELECT 1 
            FROM maintenance_checklist  -- ✅ Removed 'dbo.'
            WHERE asset_id = ? AND checklist_id = ? AND work_order_ref = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$asset_id, $checklist_id, $work_order_ref]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Load routine work order + template + tasks
     */
    public function getChecklistData(string $tenant_id, string $asset_id, string $checklist_id, string $work_order_ref): array
    {
        $sql = "
            SELECT
                rwo.*,
                ct.id AS template_id,
                ct.maintenance_type AS template_maintenance_type,
                ct.work_order AS template_work_order,
                ctask.task_order,
                ctask.task_text
            FROM routine_work_orders AS rwo  -- ✅
            LEFT JOIN checklist_template AS ct
                ON rwo.tenant_id = ct.tenant_id
               AND rwo.checklist_id = ct.checklist_id
            LEFT JOIN checklist_tasks AS ctask
                ON ct.tenant_id = ctask.tenant_id
               AND ct.checklist_id = ctask.checklist_id
            WHERE rwo.tenant_id = :tenant_id
              AND rwo.asset_id = :asset_id
              AND rwo.checklist_id = :checklist_id
              AND rwo.work_order_ref = :work_order_ref
            ORDER BY ctask.task_order ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenant_id,
            ':asset_id' => $asset_id,
            ':checklist_id' => $checklist_id,
            ':work_order_ref' => $work_order_ref
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Associate a checklist instance: insert master + tasks (transaction)
     * Returns ['maintenance_checklist_id' => int, 'inserted_tasks' => int]
     */
    public function associateChecklist(string $tenant_id, string $asset_id, string $checklist_id, string $work_order_ref): array
    {
        if ($this->isChecklistInstanceExists($asset_id, $checklist_id, $work_order_ref)) {
            throw new Exception("Work order already exists");
        }

        $data = $this->getChecklistData($tenant_id, $asset_id, $checklist_id, $work_order_ref);
        if (empty($data)) {
            throw new Exception("Checklist source data not found");
        }

        $this->conn->beginTransaction();
        try {
            $first = $data[0];

            // ✅ Use NOW(), RETURNING id, and lowercase table name
            $sqlMaster = "
                INSERT INTO maintenance_checklist (
                    tenant_id, asset_id, asset_name,
                    location_id_1, location_id_2, location_id_3,
                    work_order_ref, checklist_id,
                    maintenance_type, 
                    status, date_started, created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                RETURNING id
            ";
            $stmtM = $this->conn->prepare($sqlMaster);
            $stmtM->execute([
                $tenant_id,
                $asset_id,
                $first['asset_name'] ?? null,
                $first['location_id_1'] ?? null,
                $first['location_id_2'] ?? null,
                $first['location_id_3'] ?? null,
                $work_order_ref,
                $checklist_id,
                $first['maintenance_type'] ?? null,
                'Assigned'
            ]);

            $result = $stmtM->fetch(PDO::FETCH_ASSOC);
            $newMasterId = $result ? (int)$result['id'] : 0;
            if ($newMasterId <= 0) {
                throw new Exception("Failed to obtain new maintenance_checklist_id");
            }

            // ✅ Insert tasks with NOW()
            $sqlTask = "
                INSERT INTO maintenance_checklist_tasks (
                    maintenance_checklist_id,
                    tenant_id,
                    task_order,
                    task_text,
                    created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ";
            $stmtT = $this->conn->prepare($sqlTask);
            $taskCount = 0;
            foreach ($data as $row) {
                if (!isset($row['task_order']) || trim($row['task_text'] ?? '') === '') continue;
                $stmtT->execute([
                    $newMasterId,
                    $tenant_id,
                    (int)$row['task_order'],
                    $row['task_text']
                ]);
                $taskCount++;
            }

            $this->conn->commit();
            return [
                'maintenance_checklist_id' => $newMasterId,
                'inserted_tasks' => $taskCount
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            log_error($e->getMessage(), 'maintenance_associate');
            throw $e;
        }
    }

    /**
     * Fetch checklist instance + tasks by master ID
     */
    public function getChecklistById(int $id): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM maintenance_checklist  -- ✅
            WHERE id = ?  -- ✅ Assuming PK is 'id', not 'maintenance_checklist_id'
        ");
        $stmt->execute([$id]);
        $header = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$header) return [null, []];

        $stmt2 = $this->conn->prepare("
            SELECT *
            FROM maintenance_checklist_tasks
            WHERE maintenance_checklist_id = ?
            ORDER BY task_order ASC
        ");
        $stmt2->execute([$id]);
        $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return [$header, $tasks];
    }
}
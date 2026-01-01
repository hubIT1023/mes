<?php
// app/models/MaintenanceChecklistUpdateModel.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/logger.php';

class MaintenanceChecklistUpdateModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function checklistBelongsToTenant(int $maintenanceChecklistId, string $tenantId): bool
    {
        $sql = "SELECT 1 FROM maintenance_checklist WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maintenanceChecklistId, $tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    public function updateChecklist(array $data): bool
    {
        if (empty($data['maintenance_checklist_id']) || empty($data['tenant_id'])) {
            throw new InvalidArgumentException('maintenance_checklist_id and tenant_id are required');
        }

        $id = (int)$data['maintenance_checklist_id'];
        $tenantId = $data['tenant_id'];
        $technician = $data['technician'] ?? null;
        $status = $data['status'] ?? null;
        $dateStarted = $data['date_started'] ?? null;

        $sets = [];
        $params = [];

        if ($technician !== null) {
            $sets[] = "technician = ?";
            $params[] = $technician;
        }

        if ($status !== null) {
            $sets[] = "status = ?";
            $params[] = $status;

            $sets[] = "date_completed = CASE 
                WHEN ? = 'Completed' AND date_completed IS NULL THEN NOW()
                WHEN ? <> 'Completed' THEN NULL 
                ELSE date_completed 
            END";
            $params[] = $status;
            $params[] = $status;
        }

        if ($dateStarted !== null) {
            $sets[] = "date_started = ?";
            $params[] = $dateStarted;
        }

        if (empty($sets)) {
            return true;
        }

        $sql = "UPDATE maintenance_checklist SET " . implode(', ', $sets) . " WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $params[] = $id;
        $params[] = $tenantId;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return true;
        } catch (Exception $e) {
            log_error("updateChecklist failed: " . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    public function updateTasksBatch(array $tasks, bool $wrapTransaction = true): int
    {
        if (empty($tasks)) return 0;

        $sql = "UPDATE maintenance_checklist_tasks SET result_value = ?, result_notes = ?, completed_at = ? WHERE task_id = ?";
        $stmt = $this->conn->prepare($sql);

        $updated = 0;

        try {
            if ($wrapTransaction) $this->conn->beginTransaction();

            foreach ($tasks as $t) {
                if (empty($t['task_id'])) continue;

                $taskId = (int)$t['task_id'];
                $resultValue = $t['result_value'] ?? null;
                $resultNotes = $t['result_notes'] ?? null;

                $completedAt = null;
                if (array_key_exists('completed_flag', $t)) {
                    $completedAt = $t['completed_flag'] ? date('Y-m-d H:i:s') : null;
                } else {
                    $completedAt = !empty($t['completed_at']) ? $t['completed_at'] : null;
                }

                $stmt->execute([$resultValue, $resultNotes, $completedAt, $taskId]);
                $updated += $stmt->rowCount();
            }

            if ($wrapTransaction) $this->conn->commit();
            return $updated;

        } catch (Exception $e) {
            if ($wrapTransaction) $this->conn->rollBack();
            log_error("updateTasksBatch failed: " . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    public function markChecklistComplete(int $maintenanceChecklistId, string $tenantId): bool
    {
        $sql = "UPDATE maintenance_checklist SET status = 'Completed', date_completed = NOW() WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$maintenanceChecklistId, $tenantId]);
            return ($stmt->rowCount() > 0);
        } catch (Exception $e) {
            log_error("markChecklistComplete failed: " . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    public function updateChecklistWithTasks(array $payload): array
    {
        if (empty($payload['maintenance_checklist_id']) || empty($payload['tenant_id'])) {
            throw new InvalidArgumentException('maintenance_checklist_id and tenant_id required');
        }

        $id = (int)$payload['maintenance_checklist_id'];
        $tenantId = $payload['tenant_id'];

        try {
            $this->conn->beginTransaction();

            $headerUpdated = false;
            if (!empty($payload['header']) && is_array($payload['header'])) {
                $headerData = $payload['header'];
                $headerData['maintenance_checklist_id'] = $id;
                $headerData['tenant_id'] = $tenantId;
                $this->updateChecklist($headerData);
                $headerUpdated = true;
            }

            $updatedTasks = 0;
            if (!empty($payload['tasks']) && is_array($payload['tasks'])) {
                $updatedTasks = $this->updateTasksBatch($payload['tasks'], false);
            }

            $this->conn->commit();
            return ['updated_tasks' => $updatedTasks, 'header_updated' => $headerUpdated];

        } catch (Exception $e) {
            $this->conn->rollBack();
            log_error('updateChecklistWithTasks failed: ' . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    public function fetchChecklistWithTasks(int $maintenanceChecklistId, string $tenantId): ?array
    {
        $sql = "SELECT * FROM maintenance_checklist WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maintenanceChecklistId, $tenantId]);
        $master = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$master) return null;

        $sql2 = "SELECT * FROM maintenance_checklist_tasks WHERE maintenance_checklist_id = ? ORDER BY task_order ASC";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$maintenanceChecklistId]);
        $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return ['master' => $master, 'tasks' => $tasks];
    }

    public function archiveAndCleanupCompletedChecklist(int $maintenanceChecklistId, string $tenantId, ?string $archivedBy = null): bool
    {
        try {
            $this->conn->beginTransaction();

            // 1. Verify checklist
            $checkSql = "
                SELECT 
                    maintenance_checklist_id, tenant_id, asset_id, asset_name,
                    location_id_1, location_id_2, location_id_3,
                    work_order_ref, checklist_id, maintenance_type, technician,
                    status, date_started, date_completed,
                    created_at, updated_at, created_by, updated_by
                FROM maintenance_checklist 
                WHERE maintenance_checklist_id = ? AND tenant_id = ? AND status = 'Completed'
            ";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([$maintenanceChecklistId, $tenantId]);
            $checklist = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$checklist) {
                throw new Exception("Checklist not found, not owned by tenant, or not completed");
            }

            if ($this->isChecklistArchived($maintenanceChecklistId)) {
                throw new Exception("Checklist already archived");
            }

            // 2. Insert into completed_work_order — ✅ INCLUDE maintenance_checklist_id
            $archiveMasterSql = "
                INSERT INTO completed_work_order (
                    maintenance_checklist_id,  -- ✅ Critical: include original ID
                    tenant_id, asset_id, asset_name,
                    location_id_1, location_id_2, location_id_3,
                    work_order_ref, checklist_id, maintenance_type, technician,
                    status, date_started, date_completed,
                    created_at, updated_at, created_by, updated_by
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?
                )";

            $archiveMasterStmt = $this->conn->prepare($archiveMasterSql);
            $archiveMasterStmt->execute([
                $checklist['maintenance_checklist_id'], // ✅
                $checklist['tenant_id'],
                $checklist['asset_id'],
                $checklist['asset_name'],
                $checklist['location_id_1'],
                $checklist['location_id_2'],
                $checklist['location_id_3'],
                $checklist['work_order_ref'],
                $checklist['checklist_id'],
                $checklist['maintenance_type'],
                $checklist['technician'],
                $checklist['status'],
                $checklist['date_started'],
                $checklist['date_completed'],
                $checklist['created_at'],
                $checklist['updated_at'],
                $checklist['created_by'],
                $checklist['updated_by']
            ]);

            // 3. Insert tasks — link to original maintenance_checklist_id
            $tasksSql = "
                SELECT 
                    task_order, task_text, task_status,
                    result_value, result_notes, completed_at,
                    created_at, created_by, completed_by
                FROM maintenance_checklist_tasks 
                WHERE maintenance_checklist_id = ?
            ";
            $tasksStmt = $this->conn->prepare($tasksSql);
            $tasksStmt->execute([$maintenanceChecklistId]);
            $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($tasks)) {
                $archiveTaskSql = "
                    INSERT INTO completed_work_order_tasks (
                        maintenance_checklist_id,  -- ✅ Same ID as master
                        tenant_id,
                        task_order, task_text, task_status,
                        result_value, result_notes, completed_at,
                        created_at, created_by, completed_by
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";
                
                $archiveTaskStmt = $this->conn->prepare($archiveTaskSql);
                
                foreach ($tasks as $task) {
                    $archiveTaskStmt->execute([
                        $maintenanceChecklistId, // ✅ Preserve original ID
                        $checklist['tenant_id'],
                        $task['task_order'],
                        $task['task_text'],
                        $task['task_status'],
                        $task['result_value'],
                        $task['result_notes'],
                        $task['completed_at'],
                        $task['created_at'],
                        $task['created_by'],
                        $task['completed_by']
                    ]);
                }
            }

            // 4. DELETE from active tables
            $this->conn->prepare("DELETE FROM maintenance_checklist_tasks WHERE maintenance_checklist_id = ?")
                ->execute([$maintenanceChecklistId]);

            $this->conn->prepare("DELETE FROM maintenance_checklist WHERE maintenance_checklist_id = ?")
                ->execute([$maintenanceChecklistId]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            log_error("Archive and cleanup checklist failed: " . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    public function isChecklistArchived(int $maintenanceChecklistId): bool
    {
        // ✅ Now works because we store maintenance_checklist_id in completed_work_order
        $sql = "SELECT 1 FROM completed_work_order WHERE maintenance_checklist_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maintenanceChecklistId]);
        return (bool)$stmt->fetchColumn();
    }
}
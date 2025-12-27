<?php
// app/models/MaintenanceChecklistUpdateModel.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/logger.php';

/**
 * MaintenanceChecklistUpdateModel
 *
 * Responsible for updating maintenance_checklist (master) and
 * maintenance_checklist_tasks (child) records. Uses transactions
 * and tenant scoping to prevent accidental cross-tenant updates.
 */
class MaintenanceChecklistUpdateModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Verify that the given maintenance_checklist instance belongs to provided tenant
     *
     * @param int $maintenanceChecklistId
     * @param string $tenantId (GUID string)
     * @return bool
     */
    public function checklistBelongsToTenant(int $maintenanceChecklistId, string $tenantId): bool
    {
        $sql = "SELECT 1 FROM dbo.maintenance_checklist WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maintenanceChecklistId, $tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Update master checklist header fields. If status becomes 'Completed', date_completed is set to current time.
     * Uses a transaction when called together with updateTasksBatch for atomicity.
     *
     * Accepted keys in \$data:
     *  - maintenance_checklist_id (required)
     *  - tenant_id (required)
     *  - technician (optional)
     *  - status (optional)
     *  - date_started (optional) - string in 'Y-m-d H:i:s' or null
     *
     * @param array $data
     * @return bool true on success
     * @throws Exception on failure
     */
    public function updateChecklist(array $data): bool
    {
        if (empty($data['maintenance_checklist_id']) || empty($data['tenant_id'])) {
            throw new InvalidArgumentException('maintenance_checklist_id and tenant_id are required');
        }

        $id = (int)$data['maintenance_checklist_id'];
        $tenantId = $data['tenant_id'];
        $technician = $data['technician'] ?? null;
        $status = $data['status'] ?? null;
        $dateStarted = $data['date_started'] ?? null; // allow null to keep existing

        // Build SQL
        $sql = "UPDATE dbo.maintenance_checklist SET ";
        $sets = [];
        $params = [];

        if ($technician !== null) {
            $sets[] = "technician = ?";
            $params[] = $technician;
        }

        if ($status !== null) {
            $sets[] = "status = ?";
            $params[] = $status;

            // If status set to Completed and date_completed is NULL, fill it; otherwise preserve or nullify accordingly
            $sets[] = "date_completed = CASE WHEN ? = 'Completed' AND (date_completed IS NULL) THEN GETDATE() WHEN ? <> 'Completed' THEN NULL ELSE date_completed END";
            // push status twice for CASE checks
            $params[] = $status;
            $params[] = $status;
        }

        if ($dateStarted !== null) {
            $sets[] = "date_started = ?";
            $params[] = $dateStarted;
        }

        if (empty($sets)) {
            // Nothing to update
            return true;
        }

        $sql .= implode(', ', $sets);
        $sql .= " WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $params[] = $id;
        $params[] = $tenantId;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return true;
        } catch (Exception $e) {
            // log and rethrow
            log_error("updateChecklist failed: " . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    /**
     * Update multiple task rows (batch). Each item must include:
     *  - task_id (int)
     *  - result_value (nullable string)
     *  - result_notes (nullable string)
     *  - completed_flag (optional boolean) - if true, set completed_at to current timestamp; if false, set to NULL
     *
     * This function performs all updates in a transaction if \$wrapTransaction is true.
     *
     * @param array $tasks
     * @param bool $wrapTransaction
     * @return int number of rows updated
     * @throws Exception
     */
    public function updateTasksBatch(array $tasks, bool $wrapTransaction = true): int
    {
        if (empty($tasks)) return 0;

        $sql = "UPDATE dbo.maintenance_checklist_tasks SET result_value = ?, result_notes = ?, completed_at = ? WHERE task_id = ?";
        $stmt = $this->conn->prepare($sql);

        $updated = 0;

        try {
            if ($wrapTransaction) $this->conn->beginTransaction();

            foreach ($tasks as $t) {
                if (empty($t['task_id'])) continue;

                $taskId = (int)$t['task_id'];
                $resultValue = $t['result_value'] ?? null;
                $resultNotes = $t['result_notes'] ?? null;

                // Determine completed_at value
                if (array_key_exists('completed_flag', $t)) {
                    $completedAt = $t['completed_flag'] ? date('Y-m-d H:i:s') : null;
                } else {
                    // if client provided explicit completed_at timestamp, respect it
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

    /**
     * Mark checklist completed and set date_completed
     * Ensures tenant scoping
     *
     * @param int $maintenanceChecklistId
     * @param string $tenantId
     * @return bool
     */
    public function markChecklistComplete(int $maintenanceChecklistId, string $tenantId): bool
    {
        $sql = "UPDATE dbo.maintenance_checklist SET status = 'Completed', date_completed = GETDATE() WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$maintenanceChecklistId, $tenantId]);
            return ($stmt->rowCount() > 0);
        } catch (Exception $e) {
            log_error("markChecklistComplete failed: " . $e->getMessage(), 'maintenance_checklist');
            throw $e;
        }
    }

    /**
     * Convenience method to update header + tasks atomically.
     * Accepts an array with keys:
     *  - maintenance_checklist_id
     *  - tenant_id
     *  - header (array)
     *  - tasks (array of task arrays)
     *
     * @param array $payload
     * @return array ['updated_tasks' => int, 'header_updated' => bool]
     * @throws Exception
     */
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

    /**
     * Fetch master + tasks (for view). Returns associative array with keys 'master' and 'tasks'
     *
     * @param int $maintenanceChecklistId
     * @param string $tenantId
     * @return array|null
     */
    public function fetchChecklistWithTasks(int $maintenanceChecklistId, string $tenantId): ?array
    {
        $sql = "SELECT * FROM dbo.maintenance_checklist WHERE maintenance_checklist_id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maintenanceChecklistId, $tenantId]);
        $master = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$master) return null;

        $sql2 = "SELECT * FROM dbo.maintenance_checklist_tasks WHERE maintenance_checklist_id = ? ORDER BY task_order ASC";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$maintenanceChecklistId]);
        $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return ['master' => $master, 'tasks' => $tasks];
    }
	
/**
 * Archive completed checklist to history tables and delete from active tables
 * 
 * @param int $maintenanceChecklistId
 * @param string $tenantId
 * @param string|null $archivedBy (optional: will populate created_by/updated_by if needed)
 * @return bool
 * @throws Exception
 */
public function archiveAndCleanupCompletedChecklist(int $maintenanceChecklistId, string $tenantId, ?string $archivedBy = null): bool
{
    try {
        $this->conn->beginTransaction();

        // 1. Verify checklist exists, belongs to tenant, and is completed
        $checkSql = "
            SELECT 
                maintenance_checklist_id, tenant_id, asset_id, asset_name,
                location_id_1, location_id_2, location_id_3,
                work_order_ref, checklist_id, maintenance_type, technician_name,
                status, date_started, date_completed,
                created_at, updated_at, created_by, updated_by
            FROM dbo.maintenance_checklist 
            WHERE maintenance_checklist_id = ? AND tenant_id = ? AND status = 'Completed'
        ";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->execute([$maintenanceChecklistId, $tenantId]);
        $checklist = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$checklist) {
            throw new Exception("Checklist not found, not owned by tenant, or not completed");
        }

        // 2. Check if already archived (prevent duplicates)
        if ($this->isChecklistArchived($maintenanceChecklistId)) {
            throw new Exception("Checklist already archived");
        }

        // 3. Insert into completed_work_order (master)
        // ✅ Only insert columns that EXIST in the table
        $archiveMasterSql = "
            INSERT INTO dbo.completed_work_order (
                tenant_id, asset_id, asset_name,
                location_id_1, location_id_2, location_id_3,
                work_order_ref, checklist_id, maintenance_type, technician_name,
                status, date_started, date_completed,
                created_at, updated_at, created_by, updated_by
            ) VALUES (
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?
            )";
        
        $archiveMasterStmt = $this->conn->prepare($archiveMasterSql);
        $archiveMasterStmt->execute([
            $checklist['tenant_id'],
            $checklist['asset_id'],
            $checklist['asset_name'],
            $checklist['location_id_1'],
            $checklist['location_id_2'],
            $checklist['location_id_3'],
            $checklist['work_order_ref'],
            $checklist['checklist_id'],
            $checklist['maintenance_type'],
            $checklist['technician_name'], // ✅ NOT 'technician'
            $checklist['status'],
            $checklist['date_started'],
            $checklist['date_completed'],
            $checklist['created_at'],
            $checklist['updated_at'],
            $checklist['created_by'],
            $checklist['updated_by']
        ]);

        // Get the new ID in completed_work_order (should match maintenance_checklist_id due to business logic)
        $archivedMasterId = (int) $this->conn->lastInsertId();

        // 4. Insert into completed_work_order_tasks (child)
        $tasksSql = "
            SELECT 
                task_order, task_text, task_status,
                result_value, result_notes, completed_at,
                created_at, created_by, completed_by
            FROM dbo.maintenance_checklist_tasks 
            WHERE maintenance_checklist_id = ?
        ";
        $tasksStmt = $this->conn->prepare($tasksSql);
        $tasksStmt->execute([$maintenanceChecklistId]);
        $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($tasks)) {
            // ✅ Only insert columns that exist in completed_work_order_tasks
            $archiveTaskSql = "
                INSERT INTO dbo.completed_work_order_tasks (
                    maintenance_checklist_id,
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
                    $archivedMasterId, // or $maintenanceChecklistId if you want to preserve original ID
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

        // 5. DELETE from active tables (child first, then master)
        $deleteTasksSql = "DELETE FROM dbo.maintenance_checklist_tasks WHERE maintenance_checklist_id = ?";
        $deleteTasksStmt = $this->conn->prepare($deleteTasksSql);
        $deleteTasksStmt->execute([$maintenanceChecklistId]);

        $deleteMasterSql = "DELETE FROM dbo.maintenance_checklist WHERE maintenance_checklist_id = ?";
        $deleteMasterStmt = $this->conn->prepare($deleteMasterSql);
        $deleteMasterStmt->execute([$maintenanceChecklistId]);

        $this->conn->commit();
        return true;
		
		

    } catch (Exception $e) {
        $this->conn->rollBack();
        log_error("Archive and cleanup checklist failed: " . $e->getMessage(), 'maintenance_checklist');
        throw $e;
    }
}

/**
 * Check if checklist is already archived (prevent duplicates)
 */
public function isChecklistArchived(int $maintenanceChecklistId): bool
{
    $sql = "SELECT 1 FROM dbo.completed_work_order WHERE maintenance_checklist_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$maintenanceChecklistId]);
    return (bool)$stmt->fetchColumn();
}
}

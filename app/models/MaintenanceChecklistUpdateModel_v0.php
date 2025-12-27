<?php
// app/models/MaintenanceChecklistUpdateModel.php

require_once __DIR__ . '/../config/Database.php';

class MaintenanceChecklistUpdateModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Fetch checklist + tasks by checklist ID
     */
    public function getChecklistById($checklistId)
    {
        $sql = "
            SELECT 
                mc.id,
                mc.tenant_id,
                mc.asset_id,
                mc.asset_name,
                mc.location_id_1,
                mc.location_id_2,
                mc.location_id_3,
                mc.work_order_ref,
                mc.checklist_id,
                mc.technician,
                mc.status,
                mc.date_started,
                mc.date_completed,
                mct.id AS task_id,
                mct.task_order,
                mct.task_text,
                mct.result_value,
                mct.result_notes
            FROM maintenance_checklist mc
            LEFT JOIN maintenance_checklist_tasks mct
                ON mc.id = mct.maintenance_checklist_id
            WHERE mc.id = ?
            ORDER BY mct.task_order ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$checklistId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save/update tasks and technician info
     */
    public function saveChecklist($checklistId, $tenantId, $technician, $taskData, $summaryNotes)
    {
        try {
            $this->conn->beginTransaction();

            // Update checklist master info
            $sqlUpdateMaster = "
                UPDATE maintenance_checklist
                SET technician = :technician,
                    status = 'ONGOING'
                WHERE id = :id AND tenant_id = :tenant_id
            ";
            $stmt = $this->conn->prepare($sqlUpdateMaster);
            $stmt->execute([
                ':technician' => $technician,
                ':id' => $checklistId,
                ':tenant_id' => $tenantId
            ]);

            // Update tasks
            $sqlUpdateTask = "
                UPDATE maintenance_checklist_tasks
                SET result_value = :result_value,
                    result_notes = :result_notes
                WHERE id = :task_id
            ";
            $stmtTask = $this->conn->prepare($sqlUpdateTask);

            foreach ($taskData as $taskId => $task) {
                $stmtTask->execute([
                    ':result_value' => $task['result_value'] ?? null,
                    ':result_notes' => $task['result_notes'] ?? null,
                    ':task_id' => $taskId
                ]);
            }

            // Update summary notes if provided
            if (!empty($summaryNotes)) {
                $sqlUpdateSummary = "
                    UPDATE maintenance_checklist
                    SET summary_notes = :summary_notes
                    WHERE id = :id
                ";
                $stmtSummary = $this->conn->prepare($sqlUpdateSummary);
                $stmtSummary->execute([
                    ':summary_notes' => $summaryNotes,
                    ':id' => $checklistId
                ]);
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Complete the checklist
     */
    public function completeChecklist($checklistId, $tenantId)
    {
        $sql = "
            UPDATE maintenance_checklist
            SET status = 'COMPLETED',
                date_completed = GETDATE()
            WHERE id = :id AND tenant_id = :tenant_id
        ";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $checklistId,
            ':tenant_id' => $tenantId
        ]);
    }
}

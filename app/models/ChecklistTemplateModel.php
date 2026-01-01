<?php
// app/models/ChecklistTemplateModel.php

require_once __DIR__ . '/../config/Database.php';

class ChecklistTemplateModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Create a new checklist template
     */
    public function createTemplate($tenantId, $data) {
        try {
            $this->conn->beginTransaction();

            // ✅ Use NOW() instead of SYSDATETIME()
            $stmt = $this->conn->prepare("
                INSERT INTO checklist_template 
                (tenant_id, checklist_id, work_order, maintenance_type, interval_days, description, created_at, updated_at)
                VALUES (:tenant_id, :checklist_id, :work_order, :maintenance_type, :interval_days, :description, NOW(), NOW())
            ");
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':checklist_id' => $data['checklist_id'],
                ':work_order' => $data['work_order'],
                ':maintenance_type' => $data['maintenance_type'] ?? null,
                ':interval_days' => $data['interval_days'] ?? 30,
                ':description' => $data['description'] ?? null
            ]);

            // Insert tasks
            $tasks = $data['task_text'] ?? [];
            $order = 1;
            foreach ($tasks as $taskText) {
                if (trim($taskText) === '') continue;
                $taskStmt = $this->conn->prepare("
                    INSERT INTO checklist_tasks (tenant_id, checklist_id, task_order, task_text)
                    VALUES (:tenant_id, :checklist_id, :task_order, :task_text)
                ");
                $taskStmt->execute([
                    ':tenant_id' => $tenantId,
                    ':checklist_id' => $data['checklist_id'],
                    ':task_order' => $order++,
                    ':task_text' => $taskText
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error creating checklist template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing checklist template
     */
    public function updateTemplate($tenantId, $data) {
        try {
            $this->conn->beginTransaction();

            // ✅ Use NOW()
            $stmt = $this->conn->prepare("
                UPDATE checklist_template
                SET work_order = :work_order,
                    maintenance_type = :maintenance_type, 
                    interval_days = :interval_days,
                    description = :description, 
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id AND checklist_id = :checklist_id
            ");
            $stmt->execute([
                ':work_order' => $data['work_order'] ?? null,
                ':maintenance_type' => $data['maintenance_type'] ?? null,
                ':interval_days' => $data['interval_days'] ?? 30,
                ':description' => $data['description'] ?? null,
                ':tenant_id' => $tenantId,
                ':checklist_id' => $data['checklist_id']
            ]);

            // Delete old tasks
            $deleteStmt = $this->conn->prepare("
                DELETE FROM checklist_tasks WHERE tenant_id = :tenant_id AND checklist_id = :checklist_id
            ");
            $deleteStmt->execute([
                ':tenant_id' => $tenantId,
                ':checklist_id' => $data['checklist_id']
            ]);

            // Reinsert new tasks
            $tasks = $data['task_text'] ?? [];
            $order = 1;
            foreach ($tasks as $taskText) {
                if (trim($taskText) === '') continue;
                $insertTask = $this->conn->prepare("
                    INSERT INTO checklist_tasks (tenant_id, checklist_id, task_order, task_text)
                    VALUES (:tenant_id, :checklist_id, :task_order, :task_text)
                ");
                $insertTask->execute([
                    ':tenant_id' => $tenantId,
                    ':checklist_id' => $data['checklist_id'],
                    ':task_order' => $order++,
                    ':task_text' => $taskText
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error updating checklist template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a single checklist with its tasks
     */
    public function getTemplate($tenantId, $checklistId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM checklist_template 
            WHERE tenant_id = :tenant_id AND checklist_id = :checklist_id
        ");
        $stmt->execute([':tenant_id' => $tenantId, ':checklist_id' => $checklistId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($template) {
            $template['tasks'] = $this->getTasks($tenantId, $checklistId);
        }

        return $template;
    }

    /**
     * Get all tasks for a checklist
     */
    public function getTasks($tenantId, $checklistId) {
        $stmt = $this->conn->prepare("
            SELECT task_order, task_text 
            FROM checklist_tasks
            WHERE tenant_id = :tenant_id AND checklist_id = :checklist_id
            ORDER BY task_order ASC
        ");
        $stmt->execute([':tenant_id' => $tenantId, ':checklist_id' => $checklistId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
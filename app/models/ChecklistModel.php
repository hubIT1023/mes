<?php
// app/models/ChecklistModel.php

class ChecklistModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Check if checklist_id exists for tenant
     */
    public function checklistIdExists($tenantId, $checklistId) {
        $sql = "SELECT 1 FROM checklist_template WHERE tenant_id = ? AND checklist_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $checklistId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Create new checklist template + tasks
     */
    public function createChecklist($tenantId, $data) {
        $this->db->beginTransaction();
        try {
            $sql = "
                INSERT INTO checklist_template (
                    tenant_id, checklist_id, work_order, maintenance_type,
                    interval_days, description, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, NOW(), NOW()
                ) RETURNING id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $data['checklist_id'],
                $data['work_order'],
                $data['maintenance_type'],
                $data['interval_days'],
                $data['description']
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception("Failed to insert checklist header.");
            }

            $checklistId = $data['checklist_id'];

            // Insert tasks
            if (!empty($data['tasks'])) {
                $insertTask = $this->db->prepare("
                    INSERT INTO checklist_tasks (tenant_id, checklist_id, task_order, task_text)
                    VALUES (?, ?, ?, ?)
                ");

                foreach ($data['tasks'] as $order => $text) {
                    $text = trim($text);
                    if ($text === '') continue;
                    $insertTask->execute([
                        $tenantId,
                        $checklistId,
                        $order + 1,
                        $text
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create checklist error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch full list + tasks (flat)
     */
    public function getAllChecklists($tenantId, $filters = []) {
        $sql = "
            SELECT 
                t.id AS template_id,
                t.checklist_id,
                t.maintenance_type,
                t.work_order,
                t.interval_days,
                t.description,
                t.created_at,
                c.id AS task_id,
                c.task_order,
                c.task_text
            FROM checklist_template t
            LEFT JOIN checklist_tasks c
                ON t.tenant_id = c.tenant_id
                AND t.checklist_id = c.checklist_id
            WHERE t.tenant_id = ?
        ";

        $params = [$tenantId];

        if (!empty($filters['maintenance_type'])) {
            $sql .= " AND t.maintenance_type = ?";
            $params[] = $filters['maintenance_type'];
        }

        if (!empty($filters['checklist_id'])) {
            $sql .= " AND t.checklist_id LIKE ?";
            $params[] = "%" . $filters['checklist_id'] . "%";
        }

        $sql .= " ORDER BY t.checklist_id, c.task_order";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update checklist header + tasks
     */
    public function updateChecklist($tenantId, $checklistId, $data) {
        try {
            $this->db->beginTransaction();

            $sqlTemplate = "
                UPDATE checklist_template
                SET 
                    maintenance_type = :maintenance_type,
                    interval_days = :interval_days,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id
                  AND checklist_id = :checklist_id
            ";

            $stmt = $this->db->prepare($sqlTemplate);
            $stmt->execute([
                'maintenance_type' => $data['maintenance_type'],
                'interval_days'    => $data['interval_days'],
                'tenant_id'        => $tenantId,
                'checklist_id'     => $checklistId
            ]);

            $taskIds   = $data['tasks']['task_id'] ?? [];
            $taskTexts = $data['tasks']['task_text'] ?? [];
            $taskOrder = $data['tasks']['task_order'] ?? [];

            $keepIds = [];

            $stmtUpdate = $this->db->prepare("
                UPDATE checklist_tasks
                SET task_text = :task_text,
                    task_order = :task_order
                WHERE id = :task_id
                  AND tenant_id = :tenant_id
            ");

            $stmtInsert = $this->db->prepare("
                INSERT INTO checklist_tasks (tenant_id, checklist_id, task_order, task_text)
                VALUES (:tenant_id, :checklist_id, :task_order, :task_text)
                RETURNING id
            ");

            for ($i = 0; $i < count($taskTexts); $i++) {
                $text  = trim($taskTexts[$i]);
                $order = (int)($taskOrder[$i] ?? ($i + 1));
                $id    = !empty($taskIds[$i]) ? (int)$taskIds[$i] : null;

                if ($text === "") continue;

                if ($id) {
                    $stmtUpdate->execute([
                        'task_text'  => $text,
                        'task_order' => $order,
                        'task_id'    => $id,
                        'tenant_id'  => $tenantId
                    ]);
                    $keepIds[] = $id;
                } else {
                    $stmtInsert->execute([
                        'tenant_id'    => $tenantId,
                        'checklist_id' => $checklistId,
                        'task_order'   => $order,
                        'task_text'    => $text
                    ]);
                    $newTask = $stmtInsert->fetch(PDO::FETCH_ASSOC);
                    if ($newTask && isset($newTask['id'])) {
                        $keepIds[] = (int)$newTask['id'];
                    }
                }
            }

            if (!empty($keepIds)) {
                $placeholders = str_repeat('?,', count($keepIds) - 1) . '?';
                $sqlDelete = "
                    DELETE FROM checklist_tasks
                    WHERE tenant_id = ? 
                      AND checklist_id = ? 
                      AND id NOT IN ($placeholders)
                ";
                $stmtDelete = $this->db->prepare($sqlDelete);
                $stmtDelete->execute(array_merge([$tenantId, $checklistId], $keepIds));
            } else {
                $this->db->prepare("
                    DELETE FROM checklist_tasks
                    WHERE tenant_id = ?
                      AND checklist_id = ?
                ")->execute([$tenantId, $checklistId]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("ChecklistModel::updateChecklist Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get header + tasks by checklist_id
     */
    public function getChecklistById($tenantId, $checklistId) {
        $sql = "
            SELECT 
                t.id AS template_id,
                t.tenant_id,
                t.checklist_id,
                t.maintenance_type,
                t.work_order,
                t.interval_days,
                t.description,
                t.created_by,
                t.updated_by,
                t.created_at,
                t.updated_at,
                c.id AS task_id,
                c.task_order,
                c.task_text
            FROM checklist_template t
            LEFT JOIN checklist_tasks c
                ON t.tenant_id = c.tenant_id
               AND t.checklist_id = c.checklist_id
            WHERE 
                t.tenant_id = ?
                AND t.checklist_id = ?
            ORDER BY c.task_order
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $checklistId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) return null;

        $header = null;
        $tasks = [];
        foreach ($rows as $row) {
            if (!$header) {
                $header = $row;
            }
            if (!empty($row['task_text'])) {
                $tasks[] = [
                    'task_id'    => $row['task_id'],
                    'task_order' => $row['task_order'],
                    'task_text'  => $row['task_text']
                ];
            }
        }

        return [
            'header' => $header,
            'tasks'  => $tasks
        ];
    }
}
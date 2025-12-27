<?php
//ChecklistModel.php

class ChecklistModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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
				t.technician_name,
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

		if (!empty($filters['technician'])) {
			$sql .= " AND t.technician = ?";
			$params[] = $filters['technician'];
		}

		if (!empty($filters['checklist_id'])) {
			$sql .= " AND t.checklist_id LIKE ?";
			$params[] = "%".$filters['checklist_id']."%";
		}

		// ⚠️ FIX: Use .= to append, not = to overwrite
		$sql .= " ORDER BY t.checklist_id, c.task_order";

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    /**
     * UPDATE checklist header + tasks
     */
    public function updateChecklist($tenantId, $checklistId, $data) {
        try {
            $this->db->beginTransaction();

            // 🔹 1) Update template/header
            $sqlTemplate = "
                UPDATE checklist_template
                SET 
                    maintenance_type = :maintenance_type,
                    technician_name = :technician_name,
                    interval_days = :interval_days,
                    updated_at = GETDATE()
                WHERE tenant_id = :tenant_id
                  AND checklist_id = :checklist_id
            ";

            $stmt = $this->db->prepare($sqlTemplate);
            $stmt->execute([
                'maintenance_type' => $data['maintenance_type'],
                'technician_name'       => $data['technician_name'],
                'interval_days'    => $data['interval_days'],
                'tenant_id'        => $tenantId,
                'checklist_id'     => $checklistId
            ]);

            // 🔹 2) Process tasks
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
            ");

            for ($i = 0; $i < count($taskTexts); $i++) {
                $text  = trim($taskTexts[$i]);
                $order = (int)($taskOrder[$i] ?? ($i + 1));
                $id    = !empty($taskIds[$i]) ? (int)$taskIds[$i] : null;

                if ($text === "") {
                    continue; // Skip empty tasks
                }

                if ($id) {
                    // UPDATE existing task
                    $stmtUpdate->execute([
                        'task_text'  => $text,
                        'task_order' => $order,
                        'task_id'    => $id,
                        'tenant_id'  => $tenantId
                    ]);
                    $keepIds[] = $id;
                } else {
                    // INSERT new task
                    $stmtInsert->execute([
                        'tenant_id'    => $tenantId,
                        'checklist_id' => $checklistId,
                        'task_order'   => $order,
                        'task_text'    => $text
                    ]);

                    // Get the new task ID and add to keepIds
                    $newTaskId = $this->db->lastInsertId();
                    if ($newTaskId) {
                        $keepIds[] = (int)$newTaskId;
                    }
                }
            }

            // 🔹 3) DELETE removed tasks
            if (!empty($keepIds)) {
                $placeholders = implode(",", array_fill(0, count($keepIds), "?"));
                $sqlDelete = "
                    DELETE FROM checklist_tasks
                    WHERE tenant_id = ? 
                      AND checklist_id = ? 
                      AND id NOT IN ($placeholders)
                ";
                $stmtDelete = $this->db->prepare($sqlDelete);
                $stmtDelete->execute(array_merge([$tenantId, $checklistId], $keepIds));
            } else {
                // No tasks left — delete all
                $stmtDel = $this->db->prepare("
                    DELETE FROM checklist_tasks
                    WHERE tenant_id = ?
                      AND checklist_id = ?
                ");
                $stmtDel->execute([$tenantId, $checklistId]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("ChecklistModel::updateChecklist Error: " . $e->getMessage());
            throw $e;
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
                t.technician_name,
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
                $header = $row; // Get header info from first row
            }
            if (!empty($row['task_text'])) {
                $tasks[] = $row;
            }
        }

        return [
            'header' => $header,
            'tasks'  => $tasks
        ];
    }
}
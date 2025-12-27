<?php
// app/models/MaintenanceChecklistViewModel.php

require_once __DIR__ . '/../config/Database.php';

class MaintenanceChecklistViewModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getChecklistById($id)
    {
        $sql = "
            SELECT 
                mc.maintenance_checklist_id,
                mc.tenant_id,
                mc.asset_id,
                mc.asset_name,
                mc.location_id_1,
                mc.location_id_2,
                mc.location_id_3,
                mc.work_order_ref,
                mc.checklist_id,
                mc.technician_name,
                mc.status,
                mc.date_started,
                mc.date_completed,
                mct.task_id,
                mct.task_order,
                mct.task_text,
                mct.result_value,
                mct.result_notes,
                mct.completed_at
            FROM dbo.maintenance_checklist mc
            LEFT JOIN dbo.maintenance_checklist_tasks mct 
                ON mc.maintenance_checklist_id = mct.maintenance_checklist_id
            WHERE mc.maintenance_checklist_id = ?
            ORDER BY mct.task_order
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
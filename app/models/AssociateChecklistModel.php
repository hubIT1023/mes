<?php
// app/models/AssociateChecklistModel.php

require_once __DIR__ . '/../config/Database.php';

class AssociateChecklistModel 
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Fetch Routine Work Order + Checklist Template + Tasks
     */
    public function getChecklistAssociation($tenant_id, $asset_id, $checklist_id, $work_order_ref)
    {
        // ✅ Removed 'dbo.', use lowercase table names
        $sql = "
            SELECT 
                rwo.id AS rwo_id,
                rwo.tenant_id,
                rwo.asset_id,
                rwo.asset_name,
                rwo.location_id_1,
                rwo.location_id_2,
                rwo.location_id_3,
                rwo.checklist_id,
                rwo.maintenance_type,
                rwo.maint_start_date,
                rwo.maint_end_date,
                rwo.technician,
                rwo.work_order_ref,
                rwo.description,
                rwo.next_maintenance_date,
                rwo.status,

                ct.id AS template_id,
                ct.maintenance_type AS template_maintenance_type,
                ct.work_order AS template_work_order,
                ct.technician AS template_technician,  -- ✅ Use 'technician' (assume schema matches)

                ctask.task_order,
                ctask.task_text

            FROM routine_work_orders AS rwo
            LEFT JOIN checklist_template AS ct
                ON rwo.tenant_id = ct.tenant_id
               AND rwo.checklist_id = ct.checklist_id
            LEFT JOIN checklist_tasks AS ctask
                ON ct.tenant_id = ctask.tenant_id
               AND ct.checklist_id = ctask.checklist_id
            WHERE 
                rwo.tenant_id = :tenant_id
                AND rwo.asset_id = :asset_id
                AND rwo.checklist_id = :checklist_id
                AND rwo.work_order_ref = :work_order_ref
            ORDER BY ctask.task_order ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':asset_id', $asset_id);
        $stmt->bindParam(':checklist_id', $checklist_id);
        $stmt->bindParam(':work_order_ref', $work_order_ref);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if maintenance checklist instance exists
     */
    public function getMaintenanceChecklistInstance($tenant_id, $asset_id, $checklist_id, $work_order_ref)
    {
        // ✅ Use LIMIT 1 instead of TOP 1
        $sql = "
            SELECT id, status
            FROM maintenance_checklist
            WHERE tenant_id = :tenant_id
              AND asset_id = :asset_id
              AND checklist_id = :checklist_id
              AND work_order_ref = :work_order_ref
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':tenant_id'    => $tenant_id,
            ':asset_id'     => $asset_id,
            ':checklist_id' => $checklist_id,
            ':work_order_ref' => $work_order_ref
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Associate a checklist with an asset/work order
     */
    public function associateChecklist($tenant_id, $asset_id, $checklist_id, $work_order_ref, $technician_name = null)
    {
        if (!$technician_name) {
            // Get technician from routine_work_orders
            $sqlTech = "
                SELECT technician
                FROM routine_work_orders
                WHERE tenant_id = ? AND asset_id = ?
                  AND checklist_id = ? AND work_order_ref = ?
            ";
            $stmtTech = $this->conn->prepare($sqlTech);
            $stmtTech->execute([$tenant_id, $asset_id, $checklist_id, $work_order_ref]);
            $technician_name = $stmtTech->fetchColumn() ?: null;
        }

        // ✅ Use NOW(), RETURNING id
        $sqlInsert = "
            INSERT INTO maintenance_checklist
                (tenant_id, asset_id, checklist_id, work_order_ref, technician, status, created_at)
            VALUES
                (?, ?, ?, ?, ?, 'On-Going', NOW())
            RETURNING id
        ";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute([
            $tenant_id,
            $asset_id,
            $checklist_id,
            $work_order_ref,
            $technician_name
        ]);

        $result = $stmtInsert->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['id'] : 0;
    }

    /**
     * Update routine_work_orders status to 'On-Going'
     */
    public function updateRoutineWorkOrderStatus(string $tenant_id, string $asset_id, string $checklist_id, string $work_order_ref): void
    {
        $sql = "
            UPDATE routine_work_orders 
            SET status = 'On-Going'
            WHERE tenant_id = ? 
              AND asset_id = ? 
              AND checklist_id = ? 
              AND work_order_ref = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tenant_id, $asset_id, $checklist_id, $work_order_ref]);
    }

    /**
     * Check if checklist is already associated
     */
    public function isChecklistAssociated($tenant_id, $asset_id, $checklist_id, $work_order_ref)
    {
        $sql = "
            SELECT 1
            FROM maintenance_checklist
            WHERE tenant_id = ?
              AND asset_id = ?
              AND checklist_id = ?
              AND work_order_ref = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tenant_id, $asset_id, $checklist_id, $work_order_ref]);
        return (bool) $stmt->fetchColumn();
    }
}
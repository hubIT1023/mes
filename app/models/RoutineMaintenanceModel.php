<?php
// RoutineMaintenanceModel.php

require_once __DIR__ . '/../config/Database.php';

class RoutineMaintenanceModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get filter options
     */
    public function getFilterOptions($tenantId) {
        $pdo = $this->db;

        // ASSETS
        $assets = $pdo->prepare("
            SELECT asset_id, asset_name 
            FROM assets 
            WHERE tenant_id = :tenant_id 
            ORDER BY asset_name
        ");
        $assets->execute(['tenant_id' => $tenantId]);
        $assetsList = $assets->fetchAll(PDO::FETCH_ASSOC);

        // WORK ORDERS
        $woStmt = $pdo->prepare("
            SELECT DISTINCT work_order 
            FROM checklist_template 
            WHERE tenant_id = :tenant_id 
              AND work_order IS NOT NULL 
              AND work_order <> ''
            ORDER BY work_order
        ");
        $woStmt->execute(['tenant_id' => $tenantId]);
        $workOrderList = $woStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        // TYPES
        $types = $pdo->prepare("
            SELECT DISTINCT maintenance_type 
            FROM checklist_template 
            WHERE tenant_id = :tenant_id 
              AND maintenance_type IS NOT NULL 
              AND maintenance_type <> ''
            ORDER BY maintenance_type
        ");
        $types->execute(['tenant_id' => $tenantId]);
        $typeList = $types->fetchAll(PDO::FETCH_COLUMN) ?: [];

        // TECHNICIANS
        $techs = $pdo->prepare("
            SELECT DISTINCT technician 
            FROM checklist_template 
            WHERE tenant_id = :tenant_id 
              AND technician_name IS NOT NULL 
              AND technician <> ''
            ORDER BY technician
        ");
        $techs->execute(['tenant_id' => $tenantId]);
        $techList = $techs->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return [
            'assets'       => $assetsList,
            'work_order'   => $workOrderList,
            'types'        => $typeList,
            'technician'  => $techList
        ];
    }

    /**
     * Get maintenance type by work order
     */
    public function getMaintenanceTypeByWorkOrder($tenantId, $workOrder) {
        $stmt = $this->db->prepare("
            SELECT maintenance_type
            FROM checklist_template
            WHERE tenant_id = :tenant_id
              AND work_order = :work_order
            LIMIT 1
        ");
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':work_order' => $workOrder
        ]);

        return $stmt->fetchColumn();
    }

    /**
     * Generate Routine Work Orders
     */
    public function generateRoutineWorkOrders($tenantId, $filters = []) {
        // Base SQL: join assets + checklist_template
        $sql = "
            SELECT 
                a.asset_id,
                a.asset_name,
                a.location_id_1,
                a.location_id_2,
                a.location_id_3,
                c.checklist_id,
                c.maintenance_type,
                c.work_order,
                c.technician,  -- ✅ Fixed: was 'technician'
                c.description,
                c.interval_days
            FROM assets a
            INNER JOIN checklist_template c 
                ON a.tenant_id = c.tenant_id
            WHERE a.tenant_id = ?
        ";

        $params = [$tenantId];

        // Filter by selected assets
        if (!empty($filters['asset_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['asset_ids']), '?'));
            $sql .= " AND a.asset_id IN ($placeholders)";
            $params = array_merge($params, $filters['asset_ids']);
        }

        // Filter by specific work order
        if (!empty($filters['work_order'])) {
            $sql .= " AND c.work_order = ?";
            $params[] = $filters['work_order'];
        }

        // Filter by maintenance type (optional)
        if (!empty($filters['maintenance_type'])) {
            $sql .= " AND c.maintenance_type = ?";
            $params[] = $filters['maintenance_type'];
        }

        // Filter by technician (optional)
        if (!empty($filters['technician'])) {
            $sql .= " AND c.technician_name = ?"; // ✅ Correct column
            $params[] = $filters['technician'];
        }

        // Execute the query
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return 0;

        $today = new DateTime();
        $count = 0;

        // Insert into routine_work_orders (✅ lowercase table name)
        $insertQuery = "
            INSERT INTO routine_work_orders (
                tenant_id, asset_id, asset_name,
                location_id_1, location_id_2, location_id_3,
                checklist_id, maintenance_type, work_order_ref,
                technician, description,
                maint_start_date, maint_end_date,
                next_maintenance_date, status
            ) VALUES (
                :tenant_id, :asset_id, :asset_name,
                :loc1, :loc2, :loc3,
                :checklist_id, :type, :work_order,
                :tech, :desc,
                :start_date, :end_date,
                :next_date, :status
            )
        ";

        $insertStmt = $this->db->prepare($insertQuery);

        foreach ($rows as $row) {
            $interval = (int)($row['interval_days'] ?? 30);
            $nextDate = (clone $today)->modify("+$interval days")->format('Y-m-d');

            // Prevent duplicates: same tenant + asset + checklist + scheduled
            $dupCheck = $this->db->prepare("
                SELECT COUNT(*) 
                FROM routine_work_orders
                WHERE tenant_id = ?
                  AND asset_id = ?
                  AND checklist_id = ?
                  AND status = 'scheduled'
            ");
            $dupCheck->execute([
                $tenantId,
                $row['asset_id'],
                $row['checklist_id']
            ]);

            if ($dupCheck->fetchColumn() > 0) continue;

            // Insert new routine work order
            $insertStmt->execute([
                'tenant_id'     => $tenantId,
                'asset_id'      => $row['asset_id'],
                'asset_name'    => $row['asset_name'],
                'loc1'          => $row['location_id_1'],
                'loc2'          => $row['location_id_2'],
                'loc3'          => $row['location_id_3'],
                'checklist_id'  => $row['checklist_id'],
                'type'          => $row['maintenance_type'],
                'work_order'    => $row['work_order'],  // maps to work_order_ref
                'tech'          => $row['technician'], // ✅
                'desc'          => $row['description'],
                'start_date'    => $today->format('Y-m-d'),
                'end_date'      => $nextDate,
                'next_date'     => $nextDate,
                'status'        => 'scheduled'
            ]);

            $count++;
        }

        return $count;
    }
}
<?php
// app/models/RoutineMaintenanceModel.php

require_once __DIR__ . '/../config/Database.php';

class RoutineMaintenanceModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

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
              AND technician IS NOT NULL 
              AND technician <> ''
            ORDER BY technician
        ");
        $techs->execute(['tenant_id' => $tenantId]);
        $techList = $techs->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return [
            'assets'       => $assetsList,
            'work_order'   => $workOrderList,
            'types'        => $typeList,
            'technicians'  => $techList
        ];
    }

    /**
     * ✅ FIXED: Handle PostgreSQL fetchColumn() returning false
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

        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    }

    public function generateRoutineWorkOrders($tenantId, $filters = []) {
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
                c.technician,
                c.description,
                c.interval_days
            FROM assets a
            INNER JOIN checklist_template c 
                ON a.tenant_id = c.tenant_id
            WHERE a.tenant_id = ?
        ";

        $params = [$tenantId];

        if (!empty($filters['asset_ids'])) {
            $placeholders = str_repeat('?,', count($filters['asset_ids']) - 1) . '?';
            $sql .= " AND a.asset_id IN ($placeholders)";
            $params = array_merge($params, $filters['asset_ids']);
        }

        if (!empty($filters['work_order'])) {
            $sql .= " AND c.work_order = ?";
            $params[] = $filters['work_order'];
        }

        if (!empty($filters['maintenance_type'])) {
            $sql .= " AND c.maintenance_type = ?";
            $params[] = $filters['maintenance_type'];
        }

        if (!empty($filters['technician'])) {
            $sql .= " AND c.technician = ?";
            $params[] = $filters['technician'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return 0;
        }

        $today = new DateTime();
        $count = 0;

        // ✅ CORRECTED: Use 'technician' (not 'technician_name')
        // ✅ Verify your table has: work_order_ref, technician
        $insertQuery = "
            INSERT INTO routine_work_orders (
                tenant_id, asset_id, asset_name,
                location_id_1, location_id_2, location_id_3,
                checklist_id, maintenance_type, work_order_ref,
                technician, description,
                maint_start_date, maint_end_date,
                next_maintenance_date, status
            ) VALUES (
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?,
                ?, ?,
                ?, ?
            )
        ";

        $insertStmt = $this->db->prepare($insertQuery);

        foreach ($rows as $row) {
            $interval = (int)($row['interval_days'] ?? 30);
            $nextDate = (clone $today)->modify("+$interval days")->format('Y-m-d');

            // Check for duplicates
            $dupCheck = $this->db->prepare("
                SELECT 1
                FROM routine_work_orders
                WHERE tenant_id = ?
                  AND asset_id = ?
                  AND checklist_id = ?
                  AND status = 'scheduled'
                LIMIT 1
            ");
            $dupCheck->execute([$tenantId, $row['asset_id'], $row['checklist_id']]);

            if ($dupCheck->fetch()) {
                continue;
            }

            $insertStmt->execute([
                $tenantId,
                $row['asset_id'],
                $row['asset_name'],
                $row['location_id_1'],
                $row['location_id_2'],
                $row['location_id_3'],
                $row['checklist_id'],
                $row['maintenance_type'],
                $row['work_order'],      // → work_order_ref
                $row['technician'],      // → technician (✅ corrected)
                $row['description'],
                $today->format('Y-m-d'), // maint_start_date
                $nextDate,               // maint_end_date
                $nextDate,               // next_maintenance_date
                'scheduled'
            ]);

            $count++;
        }

        return $count;
    }
}
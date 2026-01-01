<?php
// app/models/RoutineWorkOrderModel.php

require_once __DIR__ . '/../config/Database.php';

class RoutineWorkOrderModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUpcomingMaintenance($tenantId, $filters = []) {
        // ✅ PostgreSQL: use CURRENT_DATE + INTERVAL
        $sql = "
            SELECT 
                id, tenant_id, asset_id, asset_name,
                location_id_1, location_id_2, location_id_3,
                maintenance_type, checklist_id, maint_start_date, maint_end_date,
                technician, work_order_ref, description,
                next_maintenance_date, status
            FROM routine_work_orders  -- ✅ lowercase table name
            WHERE tenant_id = :tenant_id
              AND next_maintenance_date IS NOT NULL
              AND next_maintenance_date <= (CURRENT_DATE + INTERVAL '30 days')
        ";

        $params = ['tenant_id' => $tenantId];

        // 🟢 Dynamic filters
        if (!empty($filters['asset_id'])) {
            $sql .= " AND asset_id = :asset_id";
            $params['asset_id'] = $filters['asset_id'];
        }
        if (!empty($filters['asset_name'])) {
            $sql .= " AND asset_name = :asset_name";
            $params['asset_name'] = $filters['asset_name'];
        }
        if (!empty($filters['work_order_ref'])) {
            $sql .= " AND work_order_ref = :work_order_ref";
            $params['work_order_ref'] = $filters['work_order_ref'];
        }
        if (!empty($filters['maintenance_type'])) {
            $sql .= " AND maintenance_type = :maintenance_type";
            $params['maintenance_type'] = $filters['maintenance_type'];
        }
        if (!empty($filters['technician'])) {
            $sql .= " AND technician = :technician";
            $params['technician'] = $filters['technician'];
        }

        $sql .= " ORDER BY next_maintenance_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilterOptions($tenantId) {
        $options = [];
        // ✅ Use lowercase table name in query
        $columns = ['asset_id', 'asset_name', 'work_order_ref', 'maintenance_type', 'technician'];

        foreach ($columns as $col) {
            $stmt = $this->db->prepare("
                SELECT DISTINCT $col 
                FROM routine_work_orders
                WHERE tenant_id = :tenant_id 
                  AND $col IS NOT NULL
                  AND $col != ''
                ORDER BY $col ASC
            ");
            $stmt->execute(['tenant_id' => $tenantId]);
            $options[$col] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $options;
    }
}
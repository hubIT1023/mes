<?php
// app/models/AssetMaintenanceModel.php

require_once __DIR__ . '/../config/Database.php';

class AssetMaintenanceModel
{
    private $conn;

    public function __construct()
    {
        //$this->conn = Database::getConnection();
		$this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Insert Maintenance Record
     */
    public function insertMaintenance(array $data): bool
    {
        $sql = "
            INSERT INTO asset_maintenance (
                tenant_id,
                asset_id,
                maintenance_type,
                maintenance_date,
                technician_name,
                work_order,
                description,
                next_maintenance_date,
                status
            )
            VALUES (
                :tenant_id,
                :asset_id,
                :maintenance_type,
                :maintenance_date,
                :technician_name,
                :work_order,
                :description,
                :next_maintenance_date,
                :status
            )
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':tenant_id'             => $data['tenant_id'],
            ':asset_id'              => $data['asset_id'],
            ':maintenance_type'      => $data['maintenance_type'],
            ':maintenance_date'      => $data['maintenance_date'],
            ':technician_name'       => $data['technician_name'],
            ':work_order'            => $data['work_order'],
            ':description'           => $data['description'],
            ':next_maintenance_date' => $data['next_maintenance_date'],
            ':status'                => $data['status']
        ]);
    }
}

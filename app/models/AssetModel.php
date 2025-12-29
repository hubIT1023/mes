<?php
// app/models/AssetModel.php

require_once __DIR__ . '/../config/Database.php';

class AssetModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Check if asset_id exists FOR A SPECIFIC TENANT
     */
    public function assetIdExistsForTenant(string $assetId, string $tenantId): bool
    {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM assets 
            WHERE asset_id = ? AND tenant_id = ?
            LIMIT 1
        ");
        $stmt->execute([$assetId, $tenantId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Insert asset (asset_id provided by user)
     */
    public function addAsset(array $data): bool
    {
        $sql = "
            INSERT INTO assets (
                tenant_id, 
                asset_id, 
                asset_name,
                serial_no,
                cost_center,
                department,
                location_id_1, 
                location_id_2, 
                location_id_3,
                vendor_id, 
                mfg_code, 
                status,
                equipment_description,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tenant_id'],
            $data['asset_id'],
            $data['asset_name'],
            $data['serial_no'] ?? '',
            $data['cost_center'] ?? '',
            $data['department'] ?? '',
            $data['location_id_1'] ?? '',
            $data['location_id_2'] ?? '',
            $data['location_id_3'] ?? '',
            $data['vendor_id'] ?? '',
            $data['mfg_code'] ?? '',
            $data['status'] ?? 'active',
            $data['equipment_description'] ?? ''
        ]);
    }
}
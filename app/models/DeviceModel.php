<?php
// app/models/DeviceModel.php

require_once __DIR__ . '/../config/Database.php';

class DeviceModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Generate a secure, cryptographically random device key (32 hex chars)
     */
    public function generateDeviceKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Register a new device
     */
    public function register(array $data): bool
    {
        $sql = "INSERT INTO registered_devices (
            org_id, device_key, device_name,
            parameter_name, parameter_value, action,
            hi_limit, lo_limit, trigger_condition,
            description, location_level_1, location_level_2, location_level_3,
            created_at
        ) VALUES (
            :org_id, :device_key, :device_name,
            :parameter_name, :parameter_value, :action,
            :hi_limit, :lo_limit, :trigger_condition,
            :description, :location_level_1, :location_level_2, :location_level_3,
            NOW()
        )";

        $stmt = $this->conn->prepare($sql);
        return (bool) $stmt->execute($data);
    }

    /**
     * Ensure device_key is unique (fallback safety)
     */
    public function deviceKeyExists(string $deviceKey): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM registered_devices WHERE device_key = ?");
        $stmt->execute([$deviceKey]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Get all devices for current tenant
     */
    public function getAllByOrg(string $orgId): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                id, device_name, device_key, parameter_name, 
                hi_limit, lo_limit, location_level_1, location_level_2, location_level_3,
                created_at
            FROM registered_devices 
            WHERE org_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate UUID format
     */
    public function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Get device by key (for future API use)
     */
    public function getByDeviceKey(string $deviceKey): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM registered_devices WHERE device_key = ?");
        $stmt->execute([$deviceKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
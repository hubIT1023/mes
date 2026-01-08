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

    // --- Device Registration ---
    public function generateDeviceKey(): string
    {
        return bin2hex(random_bytes(16));
    }

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

    public function deviceKeyExists(string $deviceKey): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM registered_devices WHERE device_key = ?");
        $stmt->execute([$deviceKey]);
        return (bool) $stmt->fetchColumn();
    }

    public function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }

    // --- Device Lookup ---
    public function getByDeviceKey(string $deviceKey): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM registered_devices WHERE device_key = ?");
        $stmt->execute([$deviceKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getDeviceByOrgAndKey(string $orgId, string $deviceKey): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM registered_devices WHERE org_id = ? AND device_key = ?");
        $stmt->execute([$orgId, $deviceKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

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

    // --- Inline Field Update ---
    public function updateField(int $id, string $orgId, string $field, $value): bool
    {
        $allowed = ['description', 'hi_limit', 'lo_limit', 'trigger_condition', 'action'];
        if (!in_array($field, $allowed)) {
            return false;
        }
        $sql = "UPDATE registered_devices SET $field = ? WHERE id = ? AND org_id = ?";
        $stmt = $this->conn->prepare($sql);
        return (bool) $stmt->execute([$value, $id, $orgId]);
    }

    // --- 🆕 Device Data Ingestion ---
    public function insertDeviceData(array $data): bool
    {
        $sql = "INSERT INTO device_data (device_key, parameter_name, parameter_value, unit, org_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return (bool) $stmt->execute([
            $data['device_key'],
            $data['parameter_name'],
            $data['parameter_value'],
            $data['unit'] ?? null,
            $data['org_id']
        ]);
    }

    // --- 🆕 Data Retrieval ---
    public function getRecentDeviceData(string $deviceKey, string $orgId, int $limit = 60): array
    {
        $stmt = $this->conn->prepare("
            SELECT parameter_name, parameter_value, recorded_at 
            FROM device_data 
            WHERE device_key = ? AND org_id = ?
            ORDER BY recorded_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$deviceKey, $orgId, $limit]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getDeviceHistory(string $deviceKey, string $orgId, string $interval = '24 hours'): array
    {
        $stmt = $this->conn->prepare("
            SELECT parameter_name, parameter_value, unit, recorded_at
            FROM device_data 
            WHERE device_key = ? 
              AND org_id = ?
              AND recorded_at >= NOW() - INTERVAL '$interval'
            ORDER BY recorded_at DESC
            LIMIT 500
        ");
        $stmt->execute([$deviceKey, $orgId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
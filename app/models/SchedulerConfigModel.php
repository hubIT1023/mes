<?php

class SchedulerConfigModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getTenantConfigs($tenantId)
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM tbpm_schedule_config
            WHERE tenant_id = :tenant
        ");
        $stmt->execute([':tenant' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveConfig($tenantId, $maintenanceType, $intervalDays, $enabled, $technician = null)
    {
        // Upsert
        $stmt = $this->pdo->prepare("
            INSERT INTO tbpm_schedule_config (tenant_id, maintenance_type, interval_days, enabled, technician)
            VALUES (:tenant_id, :type, :interval, :enabled, :tech)
            ON CONFLICT (tenant_id, maintenance_type)
            DO UPDATE SET interval_days = :interval, enabled = :enabled, technician = :tech, updated_at = NOW()
        ");
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':type'      => $maintenanceType,
            ':interval'  => $intervalDays,
            ':enabled'   => $enabled,
            ':tech'      => $technician
        ]);
    }
}

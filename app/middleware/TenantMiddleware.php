<?php
// TenantMiddleware.php
// Handles tenant-related logic for UI and CLI

class TenantMiddleware
{
    private $pdo;

    public function __construct()
    {
        global $pdo; // assumes PDO is initialized in bootstrap or index.php
        $this->pdo = $pdo;
    }

    /**
     * Get all tenants
     * @return array
     */
    public function getAllTenants(): array
    {
        $stmt = $this->pdo->query("SELECT org_id, tenant_name FROM organizations ORDER BY tenant_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tenant by ID
     */
    public function getTenantById(string $tenantId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT org_id, tenant_name FROM organizations WHERE org_id = :id LIMIT 1");
        $stmt->execute([':id' => $tenantId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tenant ?: null;
    }

    /**
     * Get maintenance config for a tenant
     */
    public function getMaintenanceConfig(string $tenantId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM tbpm_schedule_config
            WHERE tenant_id = :tenant
        ");
        $stmt->execute([':tenant' => $tenantId]);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($configs as $conf) {
            $result[strtolower($conf['maintenance_type'])] = $conf;
        }
        return $result;
    }
}

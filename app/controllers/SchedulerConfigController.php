<?php

class SchedulerConfigController
{
    private $model;
    private $tenantMiddleware;

    public function __construct()
    {
        $this->model = new SchedulerConfigModel();
        $this->tenantMiddleware = new TenantMiddleware();
    }

    public function edit()
    {
        $tenantId = $_GET['tenant_id'] ?? null;
        $tenants = $this->tenantMiddleware->getAllTenants(); // Get all tenants for the selection dropdown

        $configs = [];
        if ($tenantId) {
            $tenant = $this->tenantMiddleware->getTenantById($tenantId);
            if (!$tenant) {
                exit("Tenant not found");
            }
            $configs = $this->tenantMiddleware->getMaintenanceConfig($tenantId);
        }

        include __DIR__ . '/../views/forms_mms/scheduler_config.php';
    }

    public function update()
    {
        $tenantId = $_POST['tenant_id'] ?? null;
        if (!$tenantId) {
            exit("Tenant ID required");
        }

        if (isset($_POST['maintenance_type']) && is_array($_POST['maintenance_type'])) {
            foreach ($_POST['maintenance_type'] as $type => $val) {
                $interval = (int) ($_POST['interval_days'][$type] ?? 30);
                $enabled  = isset($_POST['enabled'][$type]) ? 1 : 0;
                $tech     = $_POST['technician'][$type] ?? null;

                $this->model->saveConfig($tenantId, $type, $interval, $enabled, $tech);
            }
        }

        header("Location: /form_mms/scheduler_config?tenant_id=" . urlencode($tenantId) . "&success=1");
        exit;
    }

}

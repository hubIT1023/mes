<?php
// MachineLogController.php

require_once __DIR__ . '/../models/MachineLogModel.php';

class MachineLogController
{
    private MachineLogModel $model;

    public function __construct()
    {
        $this->model = new MachineLogModel();
    }

    public function index(): void
    {
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /signin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        $filters = [
            'asset_id'  => $_GET['asset_id'] ?? null,
			'entity'  => $_GET['entity'] ?? null,
            'stopcause_start' => $_GET['stopcause_start'] ?? null,
            'from'      => $_GET['from'] ?? null,
            'to'        => $_GET['to'] ?? null,
        ];

        $logs = $this->model->getLogs($orgId, $filters);

        require __DIR__ . '/../views/reports/machine_logs.php';
    }
}

<?php
// app/controllers/AnalyticsController.php

require_once __DIR__ . '/../models/AnalyticsModel.php';

class AnalyticsController
{
    private AnalyticsModel $model;

    public function __construct()
    {
        $this->model = new AnalyticsModel();
    }

    public function index(): void
    {
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /signin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        $filters = [
        'asset_id' => trim($_GET['asset_id'] ?? '')
		];
		if ($filters['asset_id'] === '') {
			$filters['asset_id'] = null;
		}

		$mtbf = $this->model->getMTBF($orgId, $filters);
		$mttr = $this->model->getMTTR($orgId, $filters);
		$availability = $this->model->getAvailability($mtbf, $mttr);

        require __DIR__ . '/../views/reports/analytics.php';
    }
}

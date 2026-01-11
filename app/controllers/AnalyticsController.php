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

    public function index()
	{
		$orgId = $_SESSION['org_id'];
		$filters = [
			'asset_id' => trim($_GET['asset_id'] ?? '')
		];
		if ($filters['asset_id'] === '') {
			$filters['asset_id'] = null;
		}

		// For time-series chart
		$reliabilityByDate = $this->model->getReliabilityByDate($orgId, $filters);

		// Keep existing per-asset data for tables
		$mtbf = $this->model->getMTBF($orgId, $filters);
		$mttr = $this->model->getMTTR($orgId, $filters);
		$availability = $this->model->getAvailability($mtbf, $mttr);

		require_once __DIR__ . '/../views/analytics/index.php';
	}
}

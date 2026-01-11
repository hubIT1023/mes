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

   // In your controller method (e.g., index())
	public function index()
	{
		$orgId = $_SESSION['org_id']; // or however you get org ID
		$filters = [
			'asset_id' => trim($_GET['asset_id'] ?? '')
		];
		if ($filters['asset_id'] === '') {
			$filters['asset_id'] = null;
		}

		$mtbf = $this->model->getMTBF($orgId, $filters);
		$mttr = $this->model->getMTTR($orgId, $filters);
		$availability = $this->model->getAvailability($mtbf, $mttr);

		require_once __DIR__ . '/../views/analytics/index.php';
	}
}

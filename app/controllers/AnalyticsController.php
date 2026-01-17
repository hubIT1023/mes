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

   // app/controllers/AnalyticsController.php
	public function index(): void
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (!isset($_SESSION['tenant_id'])) {
			header("Location: /signin");
			exit;
		}

		$orgId = $_SESSION['tenant_id'];
		$model = new AnalyticsModel();

		// ✅ Fetch unique entities from tool_state
		$toolStateModel = new ToolStateModel(); // or use existing model instance
		$entities = $toolStateModel->getUniqueEntities($orgId);

		// Build filters (now includes 'entity')
		$filters = [
			'asset_id' => $_GET['asset_id'] ?? null,
			'entity'   => $_GET['entity']   ?? null,
		];

		// Get analytics data
		$mtbf = $model->getMTBF($orgId, $filters);
		$mttr = $model->getMTTR($orgId, $filters);
		$availability = $model->getAvailability($mtbf, $mttr);
		$reliabilityByDate = $model->getReliabilityByDate($orgId, $filters);

		// Pass $entities to view
		require __DIR__ . '/../views/analytics.php';
	}
}

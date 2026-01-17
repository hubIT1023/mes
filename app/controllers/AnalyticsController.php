<?php
// app/controllers/AnalyticsController.php

require_once __DIR__ . '/../models/AnalyticsModel.php';
require_once __DIR__ . '/../models/ToolStateModel.php'; // ✅ ADD THIS LINE

class AnalyticsController
{
    private AnalyticsModel $model;

    public function __construct()
    {
        $this->model = new AnalyticsModel();
    }

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

        // ✅ Fetch unique entities from tool_state
        $toolStateModel = new ToolStateModel();
        $entities = $toolStateModel->getUniqueEntities($orgId);

        // Build filters
        $filters = [
            'asset_id' => $_GET['asset_id'] ?? null,
            'entity'   => $_GET['entity']   ?? null,
        ];

        // Get analytics data
        $mtbf = $this->model->getMTBF($orgId, $filters);
        $mttr = $this->model->getMTTR($orgId, $filters);
        $availability = $this->model->getAvailability($mtbf, $mttr);
        $reliabilityByDate = $this->model->getReliabilityByDate($orgId, $filters);

        // Pass data to view
        require __DIR__ . '/../views/analytics.php';
    }
}

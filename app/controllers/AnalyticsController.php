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
            'entity' => trim($_GET['entity'] ?? '')
        ];

        // Normalize empty string to null for consistent filtering
        if ($filters['entity'] === '') {
            $filters['entity'] = null;
        }

        // Fetch data
        $mtbf = $this->model->getMTBF($orgId, $filters);
        $mttr = $this->model->getMTTR($orgId, $filters);
        $availability = $this->model->getAvailability($mtbf, $mttr);
        $reliabilityByDate = $this->model->getReliabilityByDate($orgId, $filters);

        // Fetch entities for dropdown
        $entities = $this->model->getUniqueEntities($orgId);

        require __DIR__ . '/../views/reports/analytics.php';
    }
}
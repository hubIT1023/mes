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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /signin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        // ----------------------------
        // Filters
        // ----------------------------
        $filters = [
            'asset_id' => trim($_GET['asset_id'] ?? ''),
            'entity'   => trim($_GET['entity'] ?? '')
        ];

        if ($filters['asset_id'] === '') {
            $filters['asset_id'] = null;
        }

        if ($filters['entity'] === '') {
            $filters['entity'] = null;
        }

        // ----------------------------
        // Entities dropdown list
        // ----------------------------
        $entities = $this->model->getUniqueEntities($orgId);

        // ----------------------------
        // Analytics data
        // ----------------------------
        $mtbf = $this->model->getMTBF($orgId, $filters);
        $mttr = $this->model->getMTTR($orgId, $filters);
        $availability = $this->model->getAvailability($mtbf, $mttr);
        $reliabilityByDate = $this->model->getReliabilityByDate($orgId, $filters);

        require __DIR__ . '/../views/reports/analytics.php';
    }
}

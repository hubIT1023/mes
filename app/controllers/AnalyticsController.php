<?php

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

        if (empty($_SESSION['tenant_id'])) {
            header('Location: /signin');
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        $filters = [
            'asset_id' => $_GET['asset_id'] ?? null,
            'entity'   => $_GET['entity'] ?? null
        ];

        $entities = $this->model->getUniqueEntities($orgId);

        $mtbf = $this->model->getMTBF($orgId, $filters);
        $mttr = $this->model->getMTTR($orgId, $filters);
        $availability = $this->model->getAvailability($mtbf, $mttr);
        $reliabilityByDate = $this->model->getReliabilityByDate($orgId, $filters);

        require __DIR__ . '/../views/analytics.php';
    }
}


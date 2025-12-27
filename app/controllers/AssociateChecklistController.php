<?php
// app/models/AssociateChecklistController.php

require_once __DIR__ . '/../models/AssociateChecklistModel.php';

class AssociateChecklistController
{
    private $model;

    public function __construct()
    {
        $this->model = new AssociateChecklistModel();
    }

    /**
     * AJAX action to fetch checklist + tasks
     */
    public function loadChecklist()
    {
        // Session validation
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant'])) {
            echo "<div class='alert alert-danger'>Session expired. Please log in.</div>";
            exit;
        }

        // Input validation
        $tenant_id      = $_GET['tenant_id']      ?? null;
        $asset_id       = $_GET['asset_id']       ?? null;
        $checklist_id   = $_GET['checklist_id']   ?? null;
        $work_order_ref = $_GET['work_order_ref'] ?? null;

        if (!$tenant_id || !$asset_id || !$checklist_id || !$work_order_ref) {
            echo "<div class='alert alert-danger'>Invalid request parameters.</div>";
            return;
        }

        // Fetch data
        $data = $this->model->getChecklistAssociation(
            $tenant_id,
            $asset_id,
            $checklist_id,
            $work_order_ref
        );

        if (empty($data)) {
            echo "<p>No checklist found.</p>";
            return;
        }

        $this->renderChecklistView($data);
    }

    /**
     * Render checklist + tasks as HTML for modal
     *
     * @param array $rows
     */
    private function renderChecklistView(array $rows)
    {
        $header = $rows[0];

        echo "<h5>Checklist: <strong>{$header['checklist_id']}</strong></h5>";
        echo "<p><strong>Asset:</strong> {$header['asset_name']} ({$header['asset_id']})</p>";
        echo "<p><strong>Work Order:</strong> {$header['work_order_ref']}</p>";
        echo "<p><strong>Maintenance Type:</strong> {$header['maintenance_type']}</p>";
        echo "<hr>";
        echo "<h6>Checklist Tasks</h6>";
        echo "<ul class='list-group'>";

        foreach ($rows as $row) {
            echo "<li class='list-group-item'>
                    <strong>Task {$row['task_order']}:</strong> {$row['task_text']}
                  </li>";
        }

        echo "</ul>";
    }
}

/**
 * Optional: if accessed directly, act as AJAX endpoint
 */
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new AssociateChecklistController();
    $controller->loadChecklist();
}

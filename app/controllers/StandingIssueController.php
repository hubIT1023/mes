<?php
// app/controllers/StandingIssueController.php

require_once __DIR__ . '/../models/StandingIssueModel.php';

class StandingIssueController
{
    private StandingIssueModel $model;

    public function __construct()
    {
        $this->model = new StandingIssueModel();
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Unauthorized");
            exit;
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security validation failed.";
            $referrer = $_SERVER['HTTP_REFERER'] ?? '/dashboard_admin';
            header("Location: " . $referrer);
            exit;
        }

        $data = [
            'org_id'          => trim($_SESSION['tenant_id']),
            'group_code'      => trim($_POST['group_code'] ?? ''),
            'location_code'   => trim($_POST['location_code'] ?? ''),
            'col_1'           => trim($_POST['col_1'] ?? ''),
            'col_2'           => trim($_POST['col_2'] ?? ''),
            'col_12'          => 'STANDING_ISSUE',
            'col_13'          => trim($_POST['col_13'] ?? ''),
            'col_14'          => trim($_POST['col_14'] ?? ''),
            'col_15'          => !empty($_POST['col_15']) ? trim($_POST['col_15']) : null,
            'col_16'          => trim($_POST['col_16'] ?? ''),
            'col_17'          => ($_POST['col_13'] ?? '') === 'DONE' ? date('Y-m-d H:i:s') : null,
            'col_18'          => trim($_POST['col_18'] ?? ''),
            'col_19'          => null,
            'col_6'           => trim($_POST['col_16'] ?? ''), // for UNIQUE constraint
        ];

        if (
            empty($data['org_id']) ||
            empty($data['col_1']) ||
            empty($data['col_13']) ||
            empty($data['col_14']) ||
            empty($data['col_16']) ||
            empty($data['col_18'])
        ) {
            $_SESSION['error'] = "Required fields missing.";
            $referrer = $_SERVER['HTTP_REFERER'] ?? '/dashboard_admin';
            header("Location: " . $referrer);
            exit;
        }

        try {
            $this->model->insertStandingIssue($data);
            $_SESSION['success'] = "✅ Standing issue posted successfully.";
        } catch (Exception $e) {
            error_log("StandingIssue insert error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to post standing issue. Please try again.";
        }

        $referrer = $_SERVER['HTTP_REFERER'] ?? '/dashboard_admin';
        header("Location: " . $referrer);
        exit;
    }
}
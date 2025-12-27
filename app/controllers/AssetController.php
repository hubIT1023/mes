<?php
// app/controllers/AssetController.php

require_once __DIR__ . '/../models/AssetModel.php';

class AssetController
{
    private $model;

    public function __construct()
    {
        $this->model = new AssetModel();
    }

    /**
     * SHOW the Add Asset form (GET request)
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $tenant = $_SESSION['tenant'];

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        include __DIR__ . '/../views/forms_mms/addAssets.php';
    }

    /**
     * HANDLE form submission with JSON debugging
     */
    public function store()
    {
        // ✅ START SESSION FIRST
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Always return JSON for debugging
        header('Content-Type: application/json');
        header('Cache-Control: no-cache');

        $response = [
            'success' => false,
            'message' => '',
            'post_data' => $_POST,
            'session' => [
                'tenant' => $_SESSION['tenant'] ?? null,
                'csrf_token' => $_SESSION['csrf_token'] ?? 'not set'
            ],
            'errors' => []
        ];

        // 1. Check authentication
        if (!isset($_SESSION['tenant'])) {
            $response['errors'][] = 'Not logged in';
            $response['message'] = 'Authentication required';
            //echo json_encode($response, JSON_PRETTY_PRINT);
			header("Location: /mes/dashboard_admin");
            exit;
        }

        // 2. Validate CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $response['errors'][] = 'CSRF token mismatch';
            $response['message'] = 'Security check failed';
           // echo json_encode($response, JSON_PRETTY_PRINT);
		   header("Location: /mes/dashboard_admin");
            exit;
        }

        // 3. Validate required fields
        $tenant = $_SESSION['tenant'];
        $requiredFields = ['asset_id', 'asset_name',
							'location_id_1', 'location_id_2','location_id_3',
							'vendor_id','mfg_code','serial_no', 
							'cost_center', 'department', 'equipment_description'];
        $data = [];

        foreach ($requiredFields as $field) {
            $data[$field] = trim($_POST[$field] ?? '');
            if (empty($data[$field])) {
                $response['errors'][] = "$field is required";
            }
        }

        // Add tenant_id
        $data['tenant_id'] = $tenant['org_id'] ?? null;
        if (empty($data['tenant_id'])) {
            $response['errors'][] = 'tenant_id missing';
        }

        $response['validated_data'] = $data;

        if (!empty($response['errors'])) {
            $response['message'] = 'Validation failed';
            //echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }

        // 4. Check for duplicate asset_id WITHIN TENANT
        if ($this->model->assetIdExistsForTenant($data['asset_id'], $data['tenant_id'])) {
            $response['errors'][] = "Asset ID '{$data['asset_id']}' already exists for your tenant";
            $response['message'] = 'Duplicate asset_id in tenant';
           // echo json_encode($response, JSON_PRETTY_PRINT);
		   header("Location: /mes/dashboard_admin");
            exit;
        }

        // 5. Try to insert
        try {
            $result = $this->model->addAsset($data);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Asset created successfully';
				$_SESSION['flash_success'] = "Asset $assetId created successfully!";
				header("Location: /mes/form_mms/addAsset");
				exit;
            } else {
                $response['errors'][] = 'Model addAsset() returned false';
                $response['message'] = 'Database insertion failed';
            }
        } catch (Exception $e) {
            $response['errors'][] = 'Exception: ' . $e->getMessage();
            $response['message'] = 'Unexpected error';
        }

        //echo json_encode($response, JSON_PRETTY_PRINT);
		header("Location: /mes/dashboard_admin");
        exit;
    }
}
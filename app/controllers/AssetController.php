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

        // ✅ Use consistent session key: 'tenant_id'
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $tenant_id = $_SESSION['tenant_id'];

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        include __DIR__ . '/../views/forms_mms/addAssets.php';
    }

    /**
     * HANDLE form submission
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Always redirect (not JSON) — remove debug JSON mode
        if (!isset($_SESSION['tenant_id'])) {
            $_SESSION['error'] = "Please log in first.";
            header("Location: /mes/signin");
            exit;
        }

        // Validate CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/form_mms/addAsset");
            exit;
        }

        // Validate required fields
        $requiredFields = [
            'asset_id', 'asset_name',
            'location_id_1', 'location_id_2', 'location_id_3',
            'vendor_id', 'mfg_code', 'serial_no',
            'cost_center', 'department', 'equipment_description'
        ];

        $data = [];
        $errors = [];

        foreach ($requiredFields as $field) {
            $data[$field] = trim($_POST[$field] ?? '');
            if (empty($data[$field])) {
                $errors[] = "$field is required";
            }
        }

        $data['tenant_id'] = $_SESSION['tenant_id'];
        if (empty($data['tenant_id'])) {
            $errors[] = 'Tenant ID missing';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header("Location: /mes/form_mms/addAsset");
            exit;
        }

        // Check duplicate asset_id within tenant
        if ($this->model->assetIdExistsForTenant($data['asset_id'], $data['tenant_id'])) {
            $_SESSION['error'] = "Asset ID '{$data['asset_id']}' already exists for your tenant.";
            header("Location: /mes/form_mms/addAsset");
            exit;
        }

        // Insert asset
        try {
            if ($this->model->addAsset($data)) {
                $_SESSION['success'] = "Asset {$data['asset_id']} created successfully!";
                header("Location: /mes/form_mms/addAsset");
            } else {
                $_SESSION['error'] = "Failed to save asset.";
                header("Location: /mes/form_mms/addAsset");
            }
        } catch (Exception $e) {
            error_log("Asset insert error: " . $e->getMessage());
            $_SESSION['error'] = "An unexpected error occurred.";
            header("Location: /mes/form_mms/addAsset");
        }

        exit;
    }

    /**
     * LIST all assets for the logged-in tenant (GET request)
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        // Ensure CSRF token is initialized for AJAX requests in this view
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $tenantId = $_SESSION['tenant_id'];
        $tenant = $_SESSION['tenant'] ?? ['org_name' => 'Your Organization'];
        $assets = $this->model->getAssetsByTenant($tenantId);

        include __DIR__ . '/../views/forms_mms/assets_list.php';
    }

    /**
     * UPDATE asset details scoped by tenant (POST AJAX request)
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['tenant_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized. Please sign in again.']);
            exit;
        }

        // Retrieve JSON input or POST form data
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $csrfToken = $input['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Security check failed. Please refresh.']);
            exit;
        }

        $assetId = trim($input['asset_id'] ?? '');
        $tenantId = $_SESSION['tenant_id'];

        if (empty($assetId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Asset ID is required.']);
            exit;
        }

        // Validate required fields
        $data = [
            'asset_name'            => trim($input['asset_name'] ?? ''),
            'serial_no'             => trim($input['serial_no'] ?? ''),
            'cost_center'           => trim($input['cost_center'] ?? ''),
            'department'            => trim($input['department'] ?? ''),
            'location_id_1'         => trim($input['location_id_1'] ?? ''),
            'location_id_2'         => trim($input['location_id_2'] ?? ''),
            'location_id_3'         => trim($input['location_id_3'] ?? ''),
            'status'                => trim($input['status'] ?? 'active'),
            'equipment_description' => trim($input['equipment_description'] ?? ''),
        ];

        if (empty($data['asset_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Asset Name is required.']);
            exit;
        }

        try {
            if ($this->model->updateAsset($assetId, $tenantId, $data)) {
                echo json_encode([
                    'success' => true,
                    'message' => "Asset '{$assetId}' updated successfully!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to save changes to the database.']);
            }
        } catch (Exception $e) {
            error_log("Asset update exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }
}
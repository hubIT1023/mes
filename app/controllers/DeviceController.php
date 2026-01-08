<?php
// app/controllers/DeviceController.php

require_once __DIR__ . '/../models/DeviceModel.php';

class DeviceController
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }
        $this->model = new DeviceModel();
    }

    // --- Registration ---
    public function create() { require_once __DIR__ . '/../views/devices/register_device.php'; }
    public function registerSuccess() {
        if (!isset($_SESSION['new_device_key'])) { header("Location: /device/register"); exit; }
        $deviceKey = $_SESSION['new_device_key']; unset($_SESSION['new_device_key']);
        require_once __DIR__ . '/../views/devices/register_success.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed."; header("Location: /device/register"); exit;
        }

        $orgId = $_SESSION['tenant_id'];
        if (!$this->model->isValidUuid($orgId)) {
            $_SESSION['error'] = "Invalid tenant ID."; header("Location: /device/register"); exit;
        }

        $deviceName = trim($_POST['device_name'] ?? '');
        if (empty($deviceName)) {
            $_SESSION['error'] = "Device name is required."; header("Location: /device/register"); exit;
        }

        $deviceKey = $this->model->generateDeviceKey();
        $attempts = 0;
        while ($this->model->deviceKeyExists($deviceKey) && $attempts < 5) {
            $deviceKey = $this->model->generateDeviceKey(); $attempts++;
        }
        if ($attempts >= 5) {
            $_SESSION['error'] = "Failed to generate unique key."; header("Location: /device/register"); exit;
        }

        $data = [
            'org_id' => $orgId,
            'device_key' => $deviceKey,
            'device_name' => $deviceName,
            'parameter_name' => trim($_POST['parameter_name'] ?? ''),
            'parameter_value' => trim($_POST['parameter_value'] ?? ''),
            'action' => trim($_POST['action'] ?? ''),
            'hi_limit' => !empty($_POST['hi_limit']) ? (float)$_POST['hi_limit'] : null,
            'lo_limit' => !empty($_POST['lo_limit']) ? (float)$_POST['lo_limit'] : null,
            'trigger_condition' => trim($_POST['trigger_condition'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'location_level_1' => trim($_POST['location_level_1'] ?? ''),
            'location_level_2' => trim($_POST['location_level_2'] ?? ''),
            'location_level_3' => trim($_POST['location_level_3'] ?? ''),
        ];

        if ($this->model->register($data)) {
            $_SESSION['new_device_key'] = $deviceKey;
            $_SESSION['success'] = "Device registered successfully!";
            header("Location: /device/register-success");
            exit;
        } else {
            $_SESSION['error'] = "Failed to register device."; header("Location: /device/register"); exit;
        }
    }

    // --- Device List & Inline Edit ---
    public function index()
    {
        $orgId = $_SESSION['tenant_id'];
        $devices = $this->model->getAllByOrg($orgId);
        require_once __DIR__ . '/../views/devices/list_devices.php';
    }

    public function updateField()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['message' => 'Method not allowed']); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'], $input['field'], $input['value'])) {
            http_response_code(400); echo json_encode(['message' => 'Invalid data']); exit;
        }

        $id = (int)($input['id'] ?? 0);
        $field = trim($input['field'] ?? '');
        $value = $input['value'];
        $orgId = $_SESSION['tenant_id'] ?? null;

        if (!$orgId || $id <= 0 || !hash_equals($_SESSION['csrf_token'] ?? '', $input['csrf_token'] ?? '')) {
            http_response_code(403); echo json_encode(['message' => 'Unauthorized']); exit;
        }

        $allowedFields = ['description', 'hi_limit', 'lo_limit', 'trigger_condition', 'action'];
        if (!in_array($field, $allowedFields)) {
            http_response_code(400); echo json_encode(['message' => 'Field not allowed']); exit;
        }

        if (in_array($field, ['hi_limit', 'lo_limit']) && ($value === '' || $value === null)) {
            $value = null;
        }

        if ($this->model->updateField($id, $orgId, $field, $value)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500); echo json_encode(['message' => 'Failed to update']);
        }
        exit;
    }

    // --- 🆕 Data Ingestion (No session needed) ---
    public function receiveData()
    {
        // Allow CORS for testing (remove in production!)
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(204);
			exit;
		}
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: POST, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type");
		
		//------------------------------
		
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['device_key'], $input['parameter_name'], $input['parameter_value'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $deviceKey = trim($input['device_key']);
        $paramName = trim($input['parameter_name']);
        $paramValue = $input['parameter_value'];
        $unit = trim($input['unit'] ?? '');

        if (!is_numeric($paramValue) || strlen($deviceKey) !== 32 || strlen($paramName) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid format']);
            exit;
        }

        // 🔒 TENANT ISOLATION: Resolve org_id from device_key
        $device = $this->model->getByDeviceKey($deviceKey);
        if (!$device) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid device key']);
            exit;
        }

        $data = [
            'device_key' => $deviceKey,
            'parameter_name' => $paramName,
            'parameter_value' => (float)$paramValue,
            'unit' => $unit,
            'org_id' => $device['org_id'] // 👈 Critical: bind to tenant
        ];

        if ($this->model->insertDeviceData($data)) {
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to store data']);
        }
        exit;
    }

    // --- 🆕 View Device Data (with tenant check) ---
    public function viewData()
    {
        $deviceKey = $_GET['key'] ?? '';
        $orgId = $_SESSION['tenant_id'] ?? '';

        if (!$deviceKey || !$orgId) {
            $_SESSION['error'] = "Invalid request.";
            header("Location: /device");
            exit;
        }

        // 🔒 Verify device belongs to tenant
        $device = $this->model->getDeviceByOrgAndKey($orgId, $deviceKey);
        if (!$device) {
            $_SESSION['error'] = "Device not found or access denied.";
            header("Location: /device");
            exit;
        }

        $recentData = $this->model->getRecentDeviceData($deviceKey, $orgId, 60);
        $history = $this->model->getDeviceHistory($deviceKey, $orgId, '24 hours');

        require_once __DIR__ . '/../views/devices/view_device_data.php';
    }
	
	// In DeviceController.php

	public function streamDeviceData()
	{
		    // 🔥 Add these TWO lines right at the top
		set_time_limit(300);        // Allow script to run for 5 minutes
		ignore_user_abort(true);    // Keep running even if browser disconnects
		
		// Prevent caching
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		header('Connection: keep-alive');
		header('Access-Control-Allow-Origin: *'); // Optional: if needed

		$deviceKey = $_GET['device_key'] ?? '';
		$orgId = $_SESSION['tenant_id'] ?? '';

		// Validate device ownership
		if (!$deviceKey || !$orgId) {
			exit;
		}

		$device = $this->model->getDeviceByOrgAndKey($orgId, $deviceKey);
		if (!$device) {
			exit;
		}

		// Keep connection alive
		while (true) {
			// Get the latest data point (only the newest)
			$recent = $this->model->getRecentDeviceData($deviceKey, $orgId, 1);
			
			if (!empty($recent)) {
				$last = $recent[0];
				// Send SSE message
				echo "data: " . json_encode($last) . "\n\n";
			}

			// Flush output to client
			if (ob_get_level()) {
				ob_flush();
			}
			flush();

			// Wait 2 seconds before next check
			sleep(2);

			// Optional: break if client disconnects (hard to detect in PHP)
		}
	}
}
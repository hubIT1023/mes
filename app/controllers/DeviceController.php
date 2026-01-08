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

    // Show registration form
    public function create()
    {
        require_once __DIR__ . '/../views/devices/register_device.php';
    }

    // Handle form submission
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /device/register");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        if (!$this->model->isValidUuid($orgId)) {
            $_SESSION['error'] = "Invalid tenant ID.";
            header("Location: /device/register");
            exit;
        }

        $deviceName = trim($_POST['device_name'] ?? '');
        if (empty($deviceName)) {
            $_SESSION['error'] = "Device name is required.";
            header("Location: /device/register");
            exit;
        }

        // Generate unique device key
        $deviceKey = $this->model->generateDeviceKey();
        $attempts = 0;
        while ($this->model->deviceKeyExists($deviceKey) && $attempts < 5) {
            $deviceKey = $this->model->generateDeviceKey();
            $attempts++;
        }

        if ($attempts >= 5) {
            $_SESSION['error'] = "Failed to generate unique device key. Please try again.";
            header("Location: /device/register");
            exit;
        }

        $data = [
            'org_id' 				=> $orgId,
            'device_key' 			=> $deviceKey,
            'device_name' 			=> $deviceName,
            'parameter_name' 		=> trim($_POST['parameter_name'] ?? ''),
            'parameter_value' 		=> trim($_POST['parameter_value'] ?? ''),
            'action' 				=> trim($_POST['action'] ?? ''),
            'hi_limit' 				=> !empty($_POST['hi_limit']) ? (float)$_POST['hi_limit'] : null,
            'lo_limit' 				=> !empty($_POST['lo_limit']) ? (float)$_POST['lo_limit'] : null,
            'trigger_condition' 	=> trim($_POST['trigger_condition'] ?? ''),
            'description' 			=> trim($_POST['description'] ?? ''),
            'location_level_1' 		=> trim($_POST['location_level_1'] ?? ''),
            'location_level_2' 		=> trim($_POST['location_level_2'] ?? ''),
            'location_level_3' 		=> trim($_POST['location_level_3'] ?? ''),
        ];

        if ($this->model->register($data)) {
            $_SESSION['new_device_key'] = $deviceKey;
            $_SESSION['success'] = "Device registered successfully!";
            header("Location: /device/register-success");
            exit;
        } else {
            $_SESSION['error'] = "Failed to register device. Please try again.";
            header("Location: /device/register");
            exit;
        }
    }

    // Show success page with device key
    public function registerSuccess()
    {
        if (!isset($_SESSION['new_device_key'])) {
            header("Location: /device/register");
            exit;
        }
        $deviceKey = $_SESSION['new_device_key'];
        unset($_SESSION['new_device_key']);
        require_once __DIR__ . '/../views/devices/register_success.php';
    }

    // List all registered devices
    public function index()
    {
        $orgId = $_SESSION['tenant_id'];
        $devices = $this->model->getAllByOrg($orgId);
        require_once __DIR__ . '/../views/devices/list_devices.php';
    }
}
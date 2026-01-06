<?php
// app/controllers/MachinePartController.php

require_once __DIR__ . '/../models/MachinePartModel.php';

class MachinePartController
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
        $this->model = new MachinePartModel();
    }

    // Store new part
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        $orgId = $_SESSION['tenant_id'];
        $assetId = trim($_POST['asset_id'] ?? '');
        $entity = trim($_POST['entity'] ?? '');
        $partId = trim($_POST['part_id'] ?? '');
        $partName = trim($_POST['part_name'] ?? '');

        if (empty($assetId) || empty($entity) || empty($partId) || empty($partName)) {
            $_SESSION['error'] = "Asset ID, Entity, Part ID, and Name are required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $imagePath = null;
        if (!empty($_FILES['part_image']['name'])) {
            $imagePath = $this->model->uploadImage($_FILES['part_image']);
            if ($imagePath === null) {
                $_SESSION['error'] = "Image upload failed. Please try again.";
                header("Location: /mes/dashboard_admin");
                exit;
            }
        }

        if ($this->model->partExistsForEntity($orgId, $assetId, $entity, $partId)) {
            $_SESSION['error'] = "This part ID already exists for the selected entity.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $data = [
            'org_id' => $orgId,
            'asset_id' => $assetId,
            'entity' => $entity,
            'part_id' => $partId,
            'part_name' => $partName,
            'serial_no' => trim($_POST['serial_no'] ?? ''),
            'vendor_id' => trim($_POST['vendor_id'] ?? ''),
            'mfg_code' => trim($_POST['mfg_code'] ?? ''),
            'sap_code' => trim($_POST['sap_code'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'parts_available_on_hand' => (int)($_POST['parts_available_on_hand'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'image_path' => $imagePath
        ];

        if ($this->model->create($data)) {
            $_SESSION['success'] = "Part added successfully!";
        } else {
            $_SESSION['error'] = "Failed to save part. Please try again.";
        }

        header("Location: /mes/dashboard_admin");
        exit;
    }

    // Delete part
    public function destroy()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        $orgId = $_SESSION['tenant_id'] ?? null;
        if (!$orgId) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid part ID.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if ($this->model->delete($id, $orgId)) {
            $_SESSION['success'] = "Part deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete part.";
        }

        header("Location: /mes/dashboard_admin");
        exit;
    }

    // List parts with filters
    public function list()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        $filters = [
            'entity' => trim($_GET['entity'] ?? ''),
            'part_name' => trim($_GET['part_name'] ?? ''),
            'vendor_id' => trim($_GET['vendor_id'] ?? ''),
            'part_id' => trim($_GET['part_id'] ?? '')
        ];

        // ✅ Use model methods (correct)
        $parts = $this->model->getFilteredParts($orgId, $filters);
        $entities = $this->model->getUniqueEntities($orgId);

        require_once __DIR__ . '/../views/forms_mms/list_parts.php';
    }
	
	public function edit()
{
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        exit('Invalid part ID');
    }

    $id = (int)$_GET['id'];
    $orgId = $_SESSION['tenant_id'];

    $part = $this->model->getById($id, $orgId);
    if (!$part) {
        $_SESSION['error'] = "Part not found.";
        header("Location: /mes/parts-list");
        exit;
    }

    require_once __DIR__ . '/../views/forms_mms/edit_part.php';
}

// In app/controllers/MachinePartController.php

public function updateDescription()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid JSON']);
        exit;
    }

    $id = (int)($input['id'] ?? 0);
    $description = trim($input['description'] ?? '');
    $csrfToken = $input['csrf_token'] ?? '';

    // Validate
    if (!$id || empty($_SESSION['tenant_id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required data']);
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        http_response_code(403);
        echo json_encode(['message' => 'Security check failed']);
        exit;
    }

    // Update
    if ($this->model->updateDescription($id, $_SESSION['tenant_id'], $description)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to update description']);
    }
    exit;
}

	// In MachinePartController.php
	public function update()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			http_response_code(405);
			exit('Method not allowed');
		}

		// Read JSON input
		$input = json_decode(file_get_contents('php://input'), true);
		if (!$input || !isset($input['id'])) {
			http_response_code(400);
			echo json_encode(['message' => 'Invalid data']);
			exit;
		}

		$id = (int)$input['id'];
		$orgId = $_SESSION['tenant_id'] ?? null;

		if (!$orgId) {
			http_response_code(403);
			echo json_encode(['message' => 'Not logged in']);
			exit;
		}

		if (!hash_equals($_SESSION['csrf_token'] ?? '', $input['csrf_token'] ?? '')) {
			http_response_code(403);
			echo json_encode(['message' => 'Security check failed']);
			exit;
		}

		// Validate required fields
		$required = ['asset_id', 'entity', 'part_id', 'part_name'];
		foreach ($required as $field) {
			if (empty(trim($input[$field] ?? ''))) {
				http_response_code(400);
				echo json_encode(['message' => ucfirst($field) . ' is required']);
				exit;
			}
		}

		$data = [
			'asset_id' => trim($input['asset_id']),
			'entity' => trim($input['entity']),
			'part_id' => trim($input['part_id']),
			'part_name' => trim($input['part_name']),
			'serial_no' => trim($input['serial_no'] ?? ''),
			'vendor_id' => trim($input['vendor_id'] ?? ''),
			'mfg_code' => trim($input['mfg_code'] ?? ''),
			'sap_code' => trim($input['sap_code'] ?? ''),
			'category' => trim($input['category'] ?? 'LOW'),
			'parts_available_on_hand' => (int)($input['parts_available_on_hand'] ?? 0),
			'description' => trim($input['description'] ?? ''),
			'image_path' => $input['image_path'] ?? null, // Note: image update not handled here
		];

		if ($this->model->update($id, $orgId, $data)) {
			echo json_encode(['success' => true]);
		} else {
			http_response_code(500);
			echo json_encode(['message' => 'Failed to update part']);
		}
		exit;
	}

}
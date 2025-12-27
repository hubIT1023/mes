<?php
// app/controllers/MachinePartController.php

require_once __DIR__ . '/../models/MachinePartModel.php';

class MachinePartController {
    private $model;
	

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }
        $this->model = new MachinePartModel();
    }

    // Store new part
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $assetId = trim($_POST['asset_id'] ?? '');
        $entity = trim($_POST['entity'] ?? '');
        $partId = trim($_POST['part_id'] ?? '');
        $partName = trim($_POST['part_name'] ?? '');

        // Validate required fields
        if (empty($assetId) || empty($entity) || empty($partId) || empty($partName)) {
            $_SESSION['error'] = "Asset ID, Entity, Part ID, and Name are required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Validate CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Handle image upload
        $imagePath = null;
        if (!empty($_FILES['part_image']['name'])) {
            $imagePath = $this->model->uploadImage($_FILES['part_image']);
            if ($imagePath === null) {
                $_SESSION['error'] = "Image upload failed. Please try again.";
                header("Location: /mes/dashboard_admin");
                exit;
            }
        }

        // Check for duplicate part on same entity
        if ($this->model->partExistsForEntity($orgId, $assetId, $entity, $partId)) {
            $_SESSION['error'] = "This part ID already exists for the selected entity.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Prepare data
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

        // Save to database
        if ($this->model->create($data)) {
            $_SESSION['success'] = "Part added successfully!";
        } else {
            $_SESSION['error'] = "Failed to save part. Please try again.";
        }

        header("Location: /mes/dashboard_admin");
        exit;
    }

    // Delete part
    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $orgId = $_SESSION['tenant_id'] ?? null;
        if (!$orgId) {
            header("Location: /mes/signin");
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
	
	// In MachinePartController.php
	public function list() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        $filters = [
            'entity' => trim($_GET['entity'] ?? ''),
            'part_name' => trim($_GET['part_name'] ?? ''),
            'vendor_id' => trim($_GET['vendor_id'] ?? ''),
            'part_id' => trim($_GET['part_id'] ?? '')
        ];

        // ✅ Use model methods instead of raw queries in controller
        $parts = $this->model->getFilteredParts($orgId, $filters);
        $entities = $this->model->getUniqueEntities($orgId);

        // ✅ Correct view path
        require_once __DIR__ . '/../views/forms_mms/list_parts.php';
    }

	private function getFilteredParts(string $orgId, array $filters): array {
		$sql = "SELECT * FROM machine_parts_list WHERE org_id = ?";
		$params = [$orgId];
		$conditions = [];

		if ($filters['entity']) {
			$conditions[] = "entity LIKE ?";
			$params[] = '%' . $filters['entity'] . '%';
		}
		if ($filters['part_name']) {
			$conditions[] = "part_name LIKE ?";
			$params[] = '%' . $filters['part_name'] . '%';
		}
		if ($filters['vendor_id']) {
			$conditions[] = "vendor_id LIKE ?";
			$params[] = '%' . $filters['vendor_id'] . '%';
		}
		if ($filters['part_id']) {
			$conditions[] = "part_id LIKE ?";
			$params[] = '%' . $filters['part_id'] . '%';
		}

		if ($conditions) {
			$sql .= " AND " . implode(" AND ", $conditions);
		}

		$sql .= " ORDER BY entity, part_name";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function getUniqueEntities(string $orgId): array {
		$stmt = $this->conn->prepare("
			SELECT DISTINCT entity FROM machine_parts_list 
			WHERE org_id = ? 
			ORDER BY entity
		");
		$stmt->execute([$orgId]);
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
}
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
        // No need to check session again — constructor already did
        $orgId = $_SESSION['tenant_id'];

        $filters = [
            'entity' => trim($_GET['entity'] ?? ''),
            'part_name' => trim($_GET['part_name'] ?? ''),
            'vendor_id' => trim($_GET['vendor_id'] ?? ''),
            'part_id' => trim($_GET['part_id'] ?? '')
        ];

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

    // ✅ FIXED: Use $_POST instead of JSON for consistency with store()
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        $id = (int)($_POST['id'] ?? 0);
        $orgId = $_SESSION['tenant_id'] ?? null;

        if (!$orgId || $id <= 0) {
            $_SESSION['error'] = "Invalid request.";
            header("Location: /mes/parts-list");
            exit;
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/parts-list");
            exit;
        }

        // Validate required fields
        $required = ['asset_id', 'entity', 'part_id', 'part_name'];
        foreach ($required as $field) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $_SESSION['error'] = ucfirst($field) . ' is required.';
                header("Location: /mes/edit-part?id=" . $id);
                exit;
            }
        }

        // Handle image upload (optional update)
        $imagePath = trim($_POST['existing_image'] ?? ''); // preserve if no new image
        if (!empty($_FILES['part_image']['name'])) {
            $newImagePath = $this->model->uploadImage($_FILES['part_image']);
            if ($newImagePath === null) {
                $_SESSION['error'] = "Image upload failed. Changes not saved.";
                header("Location: /mes/edit-part?id=" . $id);
                exit;
            }
            $imagePath = $newImagePath;
        }

        $data = [
            'asset_id' => trim($_POST['asset_id']),
            'entity' => trim($_POST['entity']),
            'part_id' => trim($_POST['part_id']),
            'part_name' => trim($_POST['part_name']),
            'serial_no' => trim($_POST['serial_no'] ?? ''),
            'vendor_id' => trim($_POST['vendor_id'] ?? ''),
            'mfg_code' => trim($_POST['mfg_code'] ?? ''),
            'sap_code' => trim($_POST['sap_code'] ?? ''),
            'category' => trim($_POST['category'] ?? 'LOW'),
            'parts_available_on_hand' => (int)($_POST['parts_available_on_hand'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'image_path' => $imagePath,
        ];

        // Optional: Prevent duplicate part_id for same entity
        if ($this->model->partExistsForEntityExcludingId($orgId, $data['asset_id'], $data['entity'], $data['part_id'], $id)) {
            $_SESSION['error'] = "This part ID already exists for the selected entity.";
            header("Location: /mes/edit-part?id=" . $id);
            exit;
        }

        if ($this->model->update($id, $orgId, $data)) {
            $_SESSION['success'] = "Part updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update part. Please try again.";
        }

        header("Location: /mes/parts-list");
        exit;
    }

    // Keep updateDescription as JSON if used for inline editing
    public function updateDescription()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON']);
            exit;
        }

        $id = (int)($input['id'] ?? 0);
        $description = trim($input['description'] ?? '');
        $csrfToken = $input['csrf_token'] ?? '';

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

        if ($this->model->updateDescription($id, $_SESSION['tenant_id'], $description)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update description']);
        }
        exit;
    }
}
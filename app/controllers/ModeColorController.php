<?php
// /app/controllers/ModeColorController.php

require_once __DIR__ . '/../models/ModeColorModel.php';

class ModeColorController
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

        $this->model = new ModeColorModel();
    }

    // List all mode colors
    public function index()
    {
        $orgId = $_SESSION['tenant_id'];
        $items = $this->model->getAll($orgId);
        require_once __DIR__ . '/../views/forms_bid/configModeColor.php';
    }

    // Show create form
    public function create()
    {
        require_once __DIR__ . '/../views/forms_bid/createModeColor.php';
    }

    // Store new mode
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // CSRF protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/mode-color/create");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $mode_key = trim($_POST['mode_key'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $tailwind_class = trim($_POST['tailwind_class'] ?? '');

        if (empty($mode_key) || empty($label) || empty($tailwind_class)) {
            $_SESSION['error'] = "All fields are required.";
        } elseif ($this->model->modeKeyExists($orgId, $mode_key)) {
            $_SESSION['error'] = "Mode key already exists.";
        } elseif ($this->model->create($orgId, $mode_key, $label, $tailwind_class)) {
            $_SESSION['success'] = "Mode color created!";
        } else {
            $_SESSION['error'] = "Failed to create mode color.";
        }

        header("Location: /mes/mode-color");
        exit;
    }

    // Show edit form
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = "Invalid ID.";
            header("Location: /mes/mode-color");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $item = $this->model->find($orgId, $id);
        if (!$item) {
            $_SESSION['error'] = "Record not found.";
            header("Location: /mes/mode-color");
            exit;
        }

        require_once __DIR__ . '/../views/forms_bid/editModeColor.php';
    }

    // Update record
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // CSRF protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/mode-color");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = "Invalid ID.";
            header("Location: /mes/mode-color");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $mode_key = trim($_POST['mode_key'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $tailwind_class = trim($_POST['tailwind_class'] ?? '');

        if (empty($mode_key) || empty($label) || empty($tailwind_class)) {
            $_SESSION['error'] = "All fields required.";
        } elseif ($this->model->modeKeyExists($orgId, $mode_key, $id)) {
            $_SESSION['error'] = "Mode key already exists.";
        } elseif ($this->model->update($id, $orgId, $mode_key, $label, $tailwind_class)) {
            $_SESSION['success'] = "Updated!";
        } else {
            $_SESSION['error'] = "Update failed.";
        }

        header("Location: /mes/mode-color");
        exit;
    }

    // Delete record
    public function destroy()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // CSRF protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/mode-color");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = "Invalid ID.";
            header("Location: /mes/mode-color");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];

        if ($this->model->delete($id, $orgId)) {
            $_SESSION['success'] = "Mode color deleted.";
        } else {
            $_SESSION['error'] = "Delete failed.";
        }

        header("Location: /mes/mode-color");
        exit;
    }
}
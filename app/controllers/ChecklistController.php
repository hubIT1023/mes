<?php
//checklistController.php
require_once __DIR__ . '/../models/ChecklistModel.php';

class ChecklistController {
    private $model;

    public function __construct() {
        $this->model = new ChecklistModel();
    }

    /**
     * Show checklist list with filters
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in");
            exit;
        }

        $tenantId = $_SESSION['tenant']['org_id'];

        // Filters
        $filters = [
            'maintenance_type' => $_GET['maintenance_type'] ?? '',
            'technician'       => $_GET['technician'] ?? '',
            'checklist_id'     => $_GET['checklist_id'] ?? ''
        ];

        // Fetch list
        $checklists = $this->model->getAllChecklists($tenantId, $filters);

        // Group tasks by checklist_id
        $grouped = [];
        foreach ($checklists as $row) {
            $id = $row['checklist_id'];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    "header" => $row,
                    "tasks"  => []
                ];
            }
            if (!empty($row['task_id'])) {
                $grouped[$id]['tasks'][] = $row;
            }
        }

        include __DIR__ . '/../views/forms_mms/checklist_lists.php';
    }

    /**
     * Update checklist + tasks inline
     */
	   public function update() {
		if (session_status() === PHP_SESSION_NONE) session_start();

		if (!isset($_SESSION['tenant'])) {
			header("Location: /mes/signin?error=Please+log+in");
			exit;
		}

		$tenantId = $_SESSION['tenant']['org_id'];

		$checklistId     = $_POST['checklist_id'] ?? null;
		$maintenanceType = $_POST['maintenance_type'] ?? null;
		$technician      = $_POST['technician'] ?? null;
		$intervalDays    = $_POST['interval_days'] ?? null;
		

		$taskIds   = $_POST['task_id'] ?? [];
		$taskTexts = $_POST['task_text'] ?? [];
		$taskOrder = $_POST['task_order'] ?? [];

		if (!$checklistId) {
			$_SESSION['error'] = "Missing checklist ID.";
			header("Location: /mes/form_mms/checklists");
			exit;
		}

		// In update():
		$updated = $this->model->updateChecklist($tenantId, $checklistId, [
			'maintenance_type' => $maintenanceType,
			'technician'       => $technician,      // ✅ This key must match what model expects
			'interval_days'    => $intervalDays,
			'tasks'            => [
				'task_id'   => $taskIds,
				'task_text' => $taskTexts,
				'task_order' => $taskOrder
			]
		]);

		if ($updated) {
			$_SESSION['success'] = "Checklist successfully updated!";
		} else {
			$_SESSION['error'] = "No changes were made or update failed.";
		}

		header("Location: /mes/form_mms/checklists");
		exit;
	}

	
	public function edit() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['tenant'])) {
        header("Location: /mes/signin?error=Please+log+in");
        exit;
    }

    $tenantId = $_SESSION['tenant']['org_id'];
    $checklistId = $_GET['checklist_id'] ?? null;

    if (!$checklistId) {
        $_SESSION['error'] = "Checklist ID is missing.";
        header("Location: /mes/form_mms/checklists");
        exit;
    }

    $checklist = $this->model->getChecklistById($tenantId, $checklistId);

    if (!$checklist) {
        $_SESSION['error'] = "Checklist not found.";
        header("Location: /mes/form_mms/checklists");
        exit;
    }

    include __DIR__ . '/../views/forms_mms/checklist_edit.php';
}

	
}

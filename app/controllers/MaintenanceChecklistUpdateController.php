<?php
// app/controllers/MaintenanceChecklistUpdateController.php

require_once __DIR__ . '/../models/MaintenanceChecklistUpdateModel.php';

class MaintenanceChecklistUpdateController
{
    private $model;

    public function __construct()
    {
        $this->model = new MaintenanceChecklistUpdateModel();
    }

    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $tenant_id = $_SESSION['tenant_id'];

        if (!is_string($tenant_id) || !$this->isValidGuid($tenant_id)) {
            error_log("Invalid tenant_id in session: " . json_encode($tenant_id));
            $_SESSION['flash_error'] = 'Authentication error. Please log in again.';
            header("Location: /mes/signin");
            exit;
        }

        $maintenance_checklist_id = $_POST['maintenance_checklist_id'] ?? null;
        $tasks = $_POST['tasks'] ?? [];
        $action = $_POST['action'] ?? 'save';

        if (empty($maintenance_checklist_id) || !ctype_digit((string)$maintenance_checklist_id)) {
            $_SESSION['flash_error'] = 'Invalid maintenance checklist ID.';
            $redirectId = urlencode($maintenance_checklist_id ?: '');
            header("Location: /mes/maintenance_checklist/view?id=" . $redirectId);
            exit;
        }
        $maintenance_checklist_id = (int)$maintenance_checklist_id;

        try {
            if (!$this->model->checklistBelongsToTenant($maintenance_checklist_id, $tenant_id)) {
                throw new Exception("Access denied: checklist not found or not owned by your organization.");
            }

            // Validate all tasks are filled if completing
            if ($action === 'archive') {
                foreach ($tasks as $task) {
                    $status = trim($task['status'] ?? '');
                    if ($status === '') {
                        throw new Exception("All tasks must have a result before archiving.");
                    }
                }
            }

            $payload = [
                'maintenance_checklist_id' => $maintenance_checklist_id,
                'tenant_id' => $tenant_id,
                'header' => [
                    'technician' => $_SESSION['user_name'] ?? null,
                    'status' => ($action === 'archive') ? 'Completed' : 'In Progress',
                    'date_started' => !empty($_POST['date_started']) ? $_POST['date_started'] : date('Y-m-d H:i:s')
                ],
                'tasks' => []
            ];

            foreach ($tasks as $task) {
                $raw_task_id = $task['task_id'] ?? null;
                if (empty($raw_task_id) || !ctype_digit((string)$raw_task_id)) {
                    continue;
                }

                $status = trim($task['status'] ?? '');
                $status = ($status === '') ? null : $status;
                $remarks = trim($task['remarks'] ?? '');
                $remarks = ($remarks === '') ? null : $remarks;

                $payload['tasks'][] = [
                    'task_id' => (int)$raw_task_id,
                    'result_value' => $status,
                    'result_notes' => $remarks,
                    'completed_flag' => $status !== null
                ];
            }

            $result = $this->model->updateChecklistWithTasks($payload);

            // Archive only if action is 'archive'
            if ($action === 'archive') {
                $this->model->archiveAndCleanupCompletedChecklist(
                    $maintenance_checklist_id, 
                    $tenant_id, 
                    $_SESSION['user_name'] ?? null
                );
            }

            $_SESSION['flash_message'] = 'Checklist updated successfully!';
            header("Location: /mes/dashboard_upcoming_maint");
            exit;

        } catch (Exception $e) {
            error_log("Maintenance checklist update failed: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Update failed: ' . htmlspecialchars($e->getMessage());
            $redirectId = urlencode($maintenance_checklist_id);
            header("Location: /mes/maintenance_checklist/view?id=" . $redirectId);
            exit;
        }
    }

    private function isValidGuid($guid)
    {
        return is_string($guid) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $guid);
    }
}
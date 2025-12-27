<?php
// /public/form/checklist.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    header("Location: /signin");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$checklistId = $_GET['checklist_id'] ?? null;

global $pdo;
if (!$pdo) {
    require_once __DIR__ . '/helpers/includes.php';
    $pdo = \App\helpers\includes::getPDO();
    if (!$pdo) {
        die("Database connection failed");
    }
}

$checklist = null;
if ($checklistId) {
    try {
        // Fetch checklist with tasks as JSON array
        $stmt = $pdo->prepare("
            SELECT  
                mc.org_id,
                mc.asset_id,
                mc.asset_name,
                mc.location_id,
                mc.checklist_id,
                mc.maintenance_type,
                mc.date_started,
                mc.date_completed,
                mc.work_order,
                mc.technician,
                mc.status,
                COALESCE(
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'task_order', mct.task_order,
                            'task_text', mct.task_text,
                            'result_value', mct.result_value,
                            'result_notes', mct.result_notes,
                            'completed_at', mct.completed_at,
                            'technician_id', mct.technician_id
                        )
                        ORDER BY mct.task_order
                    ) FILTER (WHERE mct.id IS NOT NULL),
                    '[]'::json
                ) AS tasks
            FROM maintenance_checklist mc
            LEFT JOIN maintenance_checklist_tasks mct 
                   ON mct.org_id = mc.org_id 
                  AND mct.checklist_id = mc.checklist_id
            WHERE mc.org_id = ? AND mc.checklist_id = ?
            GROUP BY 
                mc.org_id, mc.asset_id, mc.asset_name, mc.location_id,
                mc.checklist_id, mc.maintenance_type, mc.date_started,
                mc.date_completed, mc.work_order, mc.technician, mc.status
            ORDER BY mc.date_started DESC
        ");
        $stmt->execute([$orgId, $checklistId]);
        $checklist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($checklist) {
            $checklist['tasks'] = json_decode($checklist['tasks'], true);
        }
    } catch (Exception $e) {
        error_log("Fetch checklist failed: " . $e->getMessage());
    }
}

if (!$checklist) {
    die("Checklist not found");
}

$isCompleted = ($checklist['status'] === 'completed');
$workOrderValue = $checklist['work_order'] ?? '';

// Helper for escaping HTML
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checklist: <?= e($checklist['checklist_id']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-sm mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800">Maintenance Checklist</h3>
        <a href="/incoming-maintenance" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <p class="text-muted">
        ID: <?= e($checklist['checklist_id']) ?> | 
        Status: <strong><?= e($checklist['status']) ?></strong>
    </p>

    <table class="table table-bordered">
        <tr><th>Asset ID</th><td><?= e($checklist['asset_id']) ?></td></tr>
        <tr><th>Asset Name</th><td><?= e($checklist['asset_name']) ?></td></tr>
        <tr><th>Location</th><td><?= e($checklist['location_id']) ?></td></tr>
        <tr><th>Maintenance Type</th><td><?= e($checklist['maintenance_type']) ?></td></tr>
        <tr><th>Date Started</th><td><?= e($checklist['date_started']) ?></td></tr>
        <tr><th>Work Order</th><td><?= e($workOrderValue ?: 'N/A') ?></td></tr>
        <tr><th>Technician</th><td><?= e($checklist['technician'] ?? 'N/A') ?></td></tr>
    </table>

    <form method="POST" action="/handler/checklist_handler.php">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="checklist_id" value="<?= e($checklist['checklist_id']) ?>">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tasks</th>
                    <th>Result</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checklist['tasks'] as $task): ?>
                    <tr>
                        <td><?= e($task['task_text']) ?></td>
                        <td>
                            <select name="result_<?= e($task['task_order']) ?>" class="form-select" <?= $isCompleted ? 'disabled' : '' ?>>
                                <option value="">Select...</option>
                                <option value="Pass" <?= ($task['result_value'] ?? '') === 'Pass' ? 'selected' : '' ?>>Pass</option>
                                <option value="Fail" <?= ($task['result_value'] ?? '') === 'Fail' ? 'selected' : '' ?>>Fail</option>
                                <option value="N/A" <?= ($task['result_value'] ?? '') === 'N/A' ? 'selected' : '' ?>>N/A</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="comment_<?= e($task['task_order']) ?>" class="form-control"
                                   value="<?= e($task['result_notes'] ?? '') ?>"
                                   <?= $isCompleted ? 'disabled' : '' ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mb-3">
            <label class="form-label">Technician</label>
            <input type="text" name="technician" class="form-control"
                   value="<?= e($checklist['technician'] ?? '') ?>" 
                   <?= $isCompleted ? 'disabled' : '' ?> required>
        </div>

        <div class="mb-3">
            <label class="form-label">Work Order</label>
            <input type="text" name="work_order" class="form-control"
                   value="<?= e($workOrderValue) ?>" 
                   <?= $isCompleted ? 'disabled' : '' ?>>
        </div>

        <?php if (!$isCompleted): ?>
            <button type="submit" name="action" value="save" class="btn btn-warning">Save Progress</button>
            <button type="submit" name="action" value="complete" class="btn btn-success">Complete</button>
        <?php endif; ?>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

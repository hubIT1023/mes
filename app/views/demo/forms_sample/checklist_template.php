<?php
/*
// /public/forms/checklist_template.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    header("Location: /signin");
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load existing tasks (if editing)
$existingTasks = [];
if ($action === 'edit' && isset($model, $template)) {
    $existingTasks = $model->getTasks($orgId, $template['checklist_id']);
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $action === 'edit' ? 'Edit' : 'Create' ?> Checklist Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container mt-4" style="max-width: 600px;">
    <h3><?= $action === 'edit' ? 'Edit' : 'Create' ?> Checklist Template</h3>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= $action === 'edit' ? '/update-checklist-template' : '/create-checklist-template' ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Title -->
        <div class="mb-3">
            <label class="form-label">Task No. *</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($template['title'] ?? '', ENT_QUOTES) ?>" required placeholder = "ex. SERV020b">
        </div>

        <!-- Checklist ID -->
        <div class="mb-3">
            <label class="form-label">Checklist ID *</label>
            <input type="text" name="checklist_id" class="form-control" value="<?= htmlspecialchars($template['checklist_id'] ?? '', ENT_QUOTES) ?>" required>
        </div>

        <!-- Maintenance Type -->
        <div class="mb-3">
            <label class="form-label">Maintenance Type</label>
            <input type="text" name="maintenance_type" class="form-control" value="<?= htmlspecialchars($template['maintenance_type'] ?? '', ENT_QUOTES) ?>">
        </div>

        <!-- Interval Days -->
        <div class="mb-3">
            <label class="form-label">Interval (Days)</label>
            <input type="number" name="interval_days" class="form-control" value="<?= htmlspecialchars($template['interval_days'] ?? '30') ?>" min="1">
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($template['description'] ?? '', ENT_QUOTES) ?></textarea>
        </div>

        <!-- Tasks -->
        <div class="mb-3">
            <label class="form-label">Tasks *</label>
            <div id="tasks-container">
                <?php if (!empty($template['tasks'])): ?>
                    <?php foreach ($template['tasks'] as $index => $task): ?>
                        <div class="input-group mb-2 task-group">
                            <span class="input-group-text">Task <?= $task['task_order'] ?? ($index + 1) ?></span>
                            <input type="text" name="task_text[]" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($task['task_text'], ENT_QUOTES) ?>" required>
                            <?php if (($task['task_order'] ?? ($index + 1)) > 1): ?>
                                <button type="button" class="btn btn-outline-danger remove-task">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="input-group mb-2 task-group">
                        <span class="input-group-text">Task 1</span>
                        <input type="text" name="task_text[]" class="form-control" required>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" id="add-task" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>

        <!-- Submit -->
        <div class="d-grid gap-2 d-md-flex">
            <button type="submit" class="btn btn-success"><?= $action === 'edit' ? 'Update' : 'Create' ?> Template</button>
            <a href="/manage-checklist-templates" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('tasks-container');
    const addButton = document.getElementById('add-task');
    let taskCount = container.querySelectorAll('.task-group').length;

    addButton.addEventListener('click', function () {
        taskCount++;
        const group = document.createElement('div');
        group.className = 'input-group mb-2 task-group';
        group.innerHTML = `
            <span class="input-group-text">Task ${taskCount}</span>
            <input type="text" name="task_text[]" class="form-control" required>
            <button type="button" class="btn btn-outline-danger remove-task"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(group);
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-task')) {
            const group = e.target.closest('.task-group');
            if (container.querySelectorAll('.task-group').length > 1) {
                group.remove();
            } else {
                alert('You must have at least one task.');
            }
        }
    });
});
</script>
</body>
</html>

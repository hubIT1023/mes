<?php
// app/views/forms_mms/checklist_template.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['tenant']) || empty($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

$tenant = $_SESSION['tenant'];
$tenantId = $tenant['org_id'] ?? null;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$action = $action ?? 'create';
$template = $template ?? [];
$old = $_SESSION['old'] ?? [];

// Repopulate from old input
if (!empty($old)) {
    $template = array_merge($template, $old);
}

unset($_SESSION['old']);

// Prepare tasks for display
$tasks = [];
if (!empty($template['task_text']) && is_array($template['task_text'])) {
    foreach ($template['task_text'] as $text) {
        $tasks[] = ['task_text' => $text];
    }
} else {
    $tasks = [['task_text' => '']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($action) ?> Checklist Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container mt-4" style="max-width: 650px;">
    <h3><?= $action === 'edit' ? 'Edit' : 'Create New' ?> Checklist Template</h3>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" 
      action="<?= $action === 'edit' 
          ? '/mes/form_mms/checklist_template/update' 
          : '/mes/form_mms/checklist_template' ?>">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label class="form-label">Organization</label>
            <input type="text" class="form-control" 
                   value="<?= htmlspecialchars($tenant['org_name'] ?? 'Unknown Tenant') ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Task No. *</label>
            <input type="text" name="checklist_id" class="form-control" 
                   value="<?= htmlspecialchars($template['checklist_id'] ?? '', ENT_QUOTES) ?>" required>
        </div>
		
        <div class="mb-3">
            <label class="form-label">Work Order *</label>
            <input type="text" name="work_order" class="form-control" 
                   value="<?= htmlspecialchars($template['work_order'] ?? '', ENT_QUOTES) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Maintenance Type</label>
            <input type="text" name="maintenance_type" class="form-control" 
                   value="<?= htmlspecialchars($template['maintenance_type'] ?? '', ENT_QUOTES) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Interval (Days)</label>
            <input type="number" name="interval_days" class="form-control" 
                   value="<?= htmlspecialchars($template['interval_days'] ?? '30') ?>" min="1">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($template['description'] ?? '', ENT_QUOTES) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Tasks *</label>
            <div id="tasks-container">
                <?php foreach ($tasks as $i => $task): ?>
                    <div class="input-group mb-2 task-group">
                        <span class="input-group-text">Task <?= $i + 1 ?></span>
                        <input type="text" name="task_text[]" class="form-control" 
                               value="<?= htmlspecialchars($task['task_text'] ?? '', ENT_QUOTES) ?>" required>
                        <?php if ($i > 0): ?>
                            <button type="button" class="btn btn-outline-danger remove-task"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-task" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>

        <div class="d-grid gap-2 d-md-flex">
            <button type="submit" class="btn btn-success">
                <?= $action === 'edit' ? 'Update' : 'Create' ?> Template
            </button>
            <a href="/mes/checklist/manage" class="btn btn-secondary">Back</a>
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
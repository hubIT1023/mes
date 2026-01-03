<?php
// app/views/forms_mms/checklist_template.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['tenant']) || empty($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

$tenant = $_SESSION['tenant'];
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialization
$action = $action ?? 'create';
$template = $template ?? [];
$old = $_SESSION['old'] ?? [];
if (!empty($old)) {
    $template = array_merge($template, $old);
}
unset($_SESSION['old']);

// Task Normalization: Ensure we always have an array for the loop
$tasks = $template['task_text'] ?? ['']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($action) ?> Checklist Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 3rem; }
        .task-group .input-group-text { min-width: 85px; background-color: #e9ecef; font-weight: 600; }
        .section-header { border-bottom: 2px solid #0d6efd; padding-bottom: 8px; margin-bottom: 20px; color: #0d6efd; font-weight: 700; }
    </style>
</head>
<body>

<div class="container mt-5" style="max-width: 750px;">
    <div class="form-card">
        <h3 class="mb-4"><?= $action === 'edit' ? '<i class="fas fa-edit me-2"></i>Edit' : '<i class="fas fa-plus-circle me-2"></i>Create' ?> Checklist Template</h3>

        <?php foreach(['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $class): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert <?= $class ?> alert-dismissible fade show border-0 shadow-sm">
                    <?= htmlspecialchars($_SESSION[$key]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <form method="POST" action="<?= $action === 'edit' ? '/mes/form_mms/checklist_template/update' : '/mes/form_mms/checklist_template' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Organization</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($tenant['org_name'] ?? 'Unknown Tenant') ?>" readonly disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Task No. / ID *</label>
                    <input type="text" name="checklist_id" class="form-control" placeholder="e.g. TSK-101" 
                           value="<?= htmlspecialchars($template['checklist_id'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Work Order Reference *</label>
                    <input type="text" name="work_order" class="form-control" 
                           value="<?= htmlspecialchars($template['work_order'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Maintenance Type</label>
                    <select name="maintenance_type" class="form-select">
                        <option value="Preventive" <?= ($template['maintenance_type'] ?? '') == 'Preventive' ? 'selected' : '' ?>>Preventive</option>
                        <option value="Corrective" <?= ($template['maintenance_type'] ?? '') == 'Corrective' ? 'selected' : '' ?>>Corrective</option>
                        <option value="Inspection" <?= ($template['maintenance_type'] ?? '') == 'Inspection' ? 'selected' : '' ?>>Inspection</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Interval (Days)</label>
                    <input type="number" name="interval_days" class="form-control" 
                           value="<?= htmlspecialchars($template['interval_days'] ?? '30') ?>" min="1">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Template Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief details about this template..."><?= htmlspecialchars($template['description'] ?? '', ENT_QUOTES) ?></textarea>
                </div>

                <div class="col-12 mt-4">
                    <h5 class="section-header">Step-by-Step Tasks</h5>
                    <div id="tasks-container">
                        <?php foreach ($tasks as $i => $taskText): ?>
                            <div class="input-group mb-2 task-group animate__animated animate__fadeIn">
                                <span class="input-group-text task-label">Task <?= $i + 1 ?></span>
                                <input type="text" name="task_text[]" class="form-control" 
                                       value="<?= htmlspecialchars($taskText, ENT_QUOTES) ?>" required>
                                <button type="button" class="btn btn-outline-danger remove-task" <?= count($tasks) === 1 ? 'disabled' : '' ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" id="add-task" class="btn btn-sm btn-primary mt-2 shadow-sm">
                        <i class="fas fa-plus me-1"></i> Add Step
                    </button>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/mes/checklist/manage" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-success px-5 fw-bold">
                    <i class="fas fa-save me-2"></i><?= $action === 'edit' ? 'Update' : 'Save' ?> Template
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('tasks-container');
    const addButton = document.getElementById('add-task');

    // Function to re-index task labels (Task 1, Task 2...)
    function updateIndexes() {
        const groups = container.querySelectorAll('.task-group');
        groups.forEach((group, index) => {
            group.querySelector('.task-label').textContent = `Task ${index + 1}`;
            const removeBtn = group.querySelector('.remove-task');
            // Disable delete button if only one task remains
            removeBtn.disabled = (groups.length === 1);
        });
    }

    addButton.addEventListener('click', function () {
        const group = document.createElement('div');
        group.className = 'input-group mb-2 task-group animate__animated animate__fadeIn';
        group.innerHTML = `
            <span class="input-group-text task-label">Task</span>
            <input type="text" name="task_text[]" class="form-control" required>
            <button type="button" class="btn btn-outline-danger remove-task"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(group);
        updateIndexes();
        group.querySelector('input').focus();
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-task')) {
            const group = e.target.closest('.task-group');
            group.classList.add('animate__fadeOut'); // Simple animation hint
            setTimeout(() => {
                group.remove();
                updateIndexes();
            }, 100);
        }
    });
});
</script>
</body>
</html>
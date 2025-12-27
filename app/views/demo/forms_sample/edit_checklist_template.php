<?php
// /public/forms/edit_checklist_template.php

//session_start();
$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) { header("Location: /signin"); exit; }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$existingTasks = $template['tasks'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Checklist Template</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4" style="max-width: 600px;">
<h3>Edit Checklist Template</h3>

<form method="POST" action="/update-checklist-template">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="checklist_id" value="<?= htmlspecialchars($template['checklist_id']) ?>">

    <div class="mb-3">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($template['title']) ?>" required>
    
    </div>

    <div class="mb-3">
        <label class="form-label">Checklist ID *</label>
        <input type="text" name="checklist_id" class="form-control" value="<?= htmlspecialchars($template['checklist_id']) ?>" required>
    
    </div>

    <div class="mb-3">
        <label class="form-label">Maintenance Type</label>
        <input type="text" name="maintenance_type" class="form-control" value="<?= htmlspecialchars($template['maintenance_type']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Interval (Days)</label>
        <input type="number" name="interval_days" class="form-control" value="<?= htmlspecialchars($template['interval_days'] ?? 30) ?>" min="1">
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($template['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Tasks *</label>
        <div id="tasks-container">
            <?php foreach ($existingTasks as $task): ?>
            <div class="input-group mb-2 task-group">
                <span class="input-group-text">Task <?= $task['task_order'] ?></span>
                <input type="text" name="task_text[]" class="form-control" value="<?= htmlspecialchars($task['task_text']) ?>" required>
            </div>
            <?php endforeach; ?>
            <?php if (empty($existingTasks)): ?>
            <div class="input-group mb-2 task-group">
                <span class="input-group-text">Task 1</span>
                <input type="text" name="task_text[]" class="form-control" required>
            </div>
            <?php endif; ?>
        </div>
        <button type="button" id="add-task" class="btn btn-sm btn-outline-primary mt-2">Add Task</button>
    </div>

    <button type="submit" class="btn btn-success">Update Template</button>
    <a href="/manage-checklist-templates" class="btn btn-secondary">Cancel</a>
</form>
</div>

<script>
const container = document.getElementById('tasks-container');
document.getElementById('add-task').addEventListener('click', () => {
    const count = container.querySelectorAll('.task-group').length + 1;
    const div = document.createElement('div');
    div.className = 'input-group mb-2 task-group';
    div.innerHTML = `<span class="input-group-text">Task ${count}</span>
                     <input type="text" name="task_text[]" class="form-control" required>`;
    container.appendChild(div);
});
</script>
</body>
</html>

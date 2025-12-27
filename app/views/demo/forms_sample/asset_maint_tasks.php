<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Tasks for <?= htmlspecialchars($asset['asset_name'], ENT_QUOTES) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container mt-4" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Edit Maintenance Tasks</h3>
        <a href="/dashboard" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Asset:</strong> <?= htmlspecialchars($asset['asset_name']) ?> (<?= htmlspecialchars($asset['asset_id']) ?>)
        </div>
        <div class="card-body">
            <p><strong>Checklist:</strong> <?= htmlspecialchars($asset['checklist_id']) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($asset['maintenance_type'] ?? 'N/A') ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($asset['description'] ?? 'N/A') ?></p>
        </div>
    </div>

    <form method="POST" action="/asset/tasks/save">
        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($asset['asset_id']) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

        <div class="mb-3">
            <label class="form-label">Tasks *</label>
            <div id="tasks-container">
                <?php if (!empty($asset['tasks'])): ?>
                    <?php foreach ($asset['tasks'] as $task): ?>
                        <div class="input-group mb-2 task-group">
                            <span class="input-group-text">Task <?= (int)$task['task_order'] ?></span>
                            <input type="text" name="task_text[]" class="form-control" value="<?= htmlspecialchars($task['task_text'], ENT_QUOTES) ?>" required>
                            <?php if ($task['task_order'] > 1): ?>
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

        <div class="d-grid gap-2 d-md-flex">
            <button type="submit" class="btn btn-success">Save Tasks</button>
            <a href="/dashboard" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('tasks-container');
    const addButton = document.getElementById('add-task');
    let taskCount = container.querySelectorAll('.task-group').length;

    // Add new task
    addButton.addEventListener('click', function () {
        taskCount++;
        const group = document.createElement('div');
        group.className = 'input-group mb-2 task-group';
        group.innerHTML = `
            <span class="input-group-text">Task ${taskCount}</span>
            <input type="text" name="task_text[]" class="form-control" required>
            <button type="button" class="btn btn-outline-danger remove-task">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(group);
    });

    // Remove task
    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-task')) {
            if (container.querySelectorAll('.task-group').length > 1) {
                e.target.closest('.task-group').remove();
            } else {
                alert('You must have at least one task.');
            }
        }
    });
});
</script>
</body>
</html>
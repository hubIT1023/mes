<?php 
// checklist_edit.php

if (session_status() === PHP_SESSION_NONE) session_start(); 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Checklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/form_mms/checklists">Checklist List</a></li>
            <li class="breadcrumb-item active">Edit Checklist</li>
        </ol>
    </nav>

    <h3>Edit Checklist: <?= htmlspecialchars($checklist['header']['checklist_id']) ?></h3>

    <form action="/mes/form_mms/checklist_update" method="POST">
        <input type="hidden" name="checklist_id" value="<?= htmlspecialchars($checklist['header']['checklist_id']) ?>">

        <div class="mb-3">
            <label class="form-label">Maintenance Type</label>
            <input type="text" name="maintenance_type" class="form-control"
                   value="<?= htmlspecialchars($checklist['header']['maintenance_type'] ?? '') ?>">
        </div>

        <!-- ❌ REMOVED: Technician field (not part of checklist templates) -->

        <div class="mb-3">
            <label class="form-label">Interval (Days)</label>
            <input type="number" name="interval_days" class="form-control"
                   value="<?= htmlspecialchars($checklist['header']['interval_days'] ?? '') ?>">
        </div>

        <h5>Tasks</h5>
        <button type="button" class="btn btn-success mb-3" id="addTaskBtn">+ Add Task</button>

        <div id="tasksContainer">
            <?php foreach ($checklist['tasks'] as $i => $task): ?>
                <div class="mb-2 taskRow d-flex align-items-start gap-2">
                    <input type="hidden" name="task_id[]" value="<?= (int)$task['task_id'] ?>">
                    <input type="number" class="form-control" name="task_order[]" value="<?= (int)$task['task_order'] ?>" placeholder="Order" style="width:80px;">
                    <textarea class="form-control" name="task_text[]" rows="1" placeholder="Task description"><?= htmlspecialchars($task['task_text']) ?></textarea>
                    <button type="button" class="btn btn-danger btn-sm deleteTaskBtn">Delete</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-success mt-3">Update Checklist</button>
        <a href="/mes/form_mms/checklists" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tasksContainer = document.getElementById('tasksContainer');

    document.getElementById("addTaskBtn").addEventListener("click", () => {
        const taskCount = tasksContainer.querySelectorAll('.taskRow').length + 1;
        const newRow = document.createElement("div");
        newRow.className = "mb-2 taskRow d-flex align-items-start gap-2";
        newRow.innerHTML = `
            <input type="hidden" name="task_id[]" value="">
            <input type="number" class="form-control" name="task_order[]" value="${taskCount}" placeholder="Order" style="width:80px;">
            <textarea class="form-control" name="task_text[]" rows="1" placeholder="Task description"></textarea>
            <button type="button" class="btn btn-danger btn-sm deleteTaskBtn">Delete</button>
        `;
        tasksContainer.appendChild(newRow);
    });

    tasksContainer.addEventListener("click", function(e) {
        if (e.target.classList.contains("deleteTaskBtn")) {
            const row = e.target.closest(".taskRow");
            const taskIdInput = row.querySelector("input[name='task_id\\[\\]']");
            const textArea = row.querySelector("textarea[name='task_text\\[\\]']");
            
            if (taskIdInput.value) {
                // Mark for deletion by clearing text (handled in model)
                textArea.value = "";
                row.style.opacity = "0.5";
                e.target.textContent = "Deleted";
                e.target.disabled = true;
            } else {
                row.remove();
            }
        }
    });
});
</script>

</body>
</html>
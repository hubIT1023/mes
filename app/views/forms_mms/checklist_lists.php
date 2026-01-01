<?php 
// checklist_lists.php

if (session_status() === PHP_SESSION_NONE) session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checklist List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checklist List</li>
        </ol>
    </nav>

    <h3>Checklist Templates</h3>

    <!-- Filters -->
    <form class="row g-3 mb-4" method="GET" action="">
        <div class="col-md-4">
            <input type="text" name="checklist_id" class="form-control"
                   placeholder="Checklist ID"
                   value="<?= htmlspecialchars($_GET['checklist_id'] ?? '') ?>">
        </div>

        <div class="col-md-4">
            <select class="form-select" name="maintenance_type">
                <option value="">-- Maintenance Type --</option>
                <option value="PM" <?= (($_GET['maintenance_type'] ?? '') === 'PM') ? 'selected' : '' ?>>PM</option>
                <option value="CM" <?= (($_GET['maintenance_type'] ?? '') === 'CM') ? 'selected' : '' ?>>CM</option>
                <option value="Inspection" <?= (($_GET['maintenance_type'] ?? '') === 'Inspection') ? 'selected' : '' ?>>Inspection</option>
            </select>
        </div>

        <div class="col-md-4">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Checklist Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Checklist ID</th>
                    <th>Maintenance Type</th>
                    <th>Work Order Code</th>
                    <th>Interval (Days)</th>
                    <th>Description</th>
                    <th>Tasks</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            if (!empty($checklists)):

                // Group rows by checklist ID
                $grouped = [];
                foreach ($checklists as $row) {
                    $grouped[$row['checklist_id']][] = $row;
                }

                foreach ($grouped as $checklistId => $rows): 
                    $first = $rows[0];
            ?>
                <tr>
                    <td><?= htmlspecialchars($checklistId) ?></td>
                    <td><?= htmlspecialchars($first['maintenance_type'] ?? '') ?></td>
                    <td><?= htmlspecialchars($first['work_order'] ?? '') ?></td>
                    <td><?= htmlspecialchars($first['interval_days'] ?? '') ?></td>
                    <td><?= htmlspecialchars($first['description'] ?? '') ?></td>
                    <td>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($rows as $task): ?>
                                <?php if (!empty($task['task_text'])): ?>
                                    <li><?= htmlspecialchars($task['task_text']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td class="text-center">
                        <a href="/mes/form_mms/checklist_edit?checklist_id=<?= urlencode($checklistId) ?>" 
                           class="btn btn-sm btn-warning">
                            Edit
                        </a>
                    </td>
                </tr>
            <?php 
                endforeach;

            else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No checklists found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
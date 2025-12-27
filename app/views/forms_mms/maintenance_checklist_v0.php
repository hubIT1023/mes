<?php 
// /app/views/forms_mms/maintenance_checklist.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Checklist - <?= htmlspecialchars($checklist['template_name'] ?? '') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: auto; background-color: white; padding: 20px; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 2px solid #0056b3; padding-bottom: 5px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .task-input { width: 95%; padding: 4px; font-size: 0.9em; }
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em; }
        .btn-save { background-color: #28a745; color: white; margin-right: 10px; }
        .btn-complete { background-color: #007bff; color: white; }
        .btn-row { margin-top: 20px; }
    </style>
</head>

<body>
<div class="container">

    <h1>Maintenance Checklist Form</h1>

    <!-- SINGLE CHECKLIST ASSET INFO -->
    <table class="table table-sm mb-3">
        <tr><th>Asset ID</th><td><?= htmlspecialchars($checklist['asset_id'] ?? '') ?></td></tr>
        <tr><th>Asset Name</th><td><?= htmlspecialchars($checklist['asset_name'] ?? '') ?></td></tr>
        <tr><th>Location</th><td><?= htmlspecialchars($checklist['location_id_3'] ?? '') ?></td></tr>
        <tr><th>Due Date</th><td><?= htmlspecialchars($checklist['date_started'] ?? '') ?></td></tr>
        <tr><th>Maintenance Type</th><td><?= htmlspecialchars($checklist['maintenance_type'] ?? '') ?></td></tr>
        <tr><th>Status</th><td><?= htmlspecialchars($checklist['status'] ?? 'Pending') ?></td></tr>
        <tr><th>Technician</th><td><?= htmlspecialchars($checklist['technician'] ?? '') ?></td></tr>
        <tr><th>Work Order</th><td><?= htmlspecialchars($checklist['work_order_ref'] ?? '') ?></td></tr>
        <tr><th>Checklist Template</th><td><?= htmlspecialchars($checklist['template_name'] ?? '') ?></td></tr>
    </table>

    <!-- CHECKLIST TASKS (Editable) -->
    <form method="POST" action="/mes/maintenance_checklist/save">
        <input type="hidden" name="maintenance_id" value="<?= $checklist['id'] ?>">

        <h2>Checklist Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Order</th>
                    <th style="width: 50%;">Task Description</th>
                    <th style="width: 120px;">Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tasks)): ?>
                    <?php foreach ($tasks as $i => $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['task_order'] ?? '') ?></td>
                        <td><?= htmlspecialchars($t['task_text'] ?? '') ?></td>
                        <td>
                            <select name="tasks[<?= $i ?>][status]" class="task-input">
                                <option value="Pending" <?= ($t['result_value']=='Pending' ? 'selected' : '') ?>>Pending</option>
                                <option value="OK" <?= ($t['result_value']=='OK' ? 'selected' : '') ?>>OK</option>
                                <option value="Not OK" <?= ($t['result_value']=='Not OK' ? 'selected' : '') ?>>Not OK</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="tasks[<?= $i ?>][remarks]" value="<?= htmlspecialchars($t['result_notes'] ?? '') ?>" class="task-input">
                        </td>
                        <input type="hidden" name="tasks[<?= $i ?>][task_id]" value="<?= $t['task_id'] ?? '' ?>">
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No tasks found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="btn-row">
            <button type="submit" class="btn btn-save">Save Progress</button>
        </div>
    </form>

    <!-- COMPLETE CHECKLIST -->
    <form method="POST" action="/mes/maintenance_checklist/complete">
        <input type="hidden" name="maintenance_id" value="<?= $checklist['id'] ?>">
        <button type="submit" class="btn btn-complete">Mark as Completed</button>
    </form>

</div>

<!-- MULTI-ASSET MODALS -->
<?php if (!empty($assets)): ?>
    <?php foreach ($assets as $a): 
        $tasks = $checklistModel->getChecklistAssociation(
            $a['tenant_id'],
            $a['asset_id'],
            $a['checklist_id'],
            $a['work_order_ref']
        );
        $modalId = "modalChecklist_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $a['asset_id']) . "_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $a['checklist_id']);
    ?>
    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="<?= $modalId ?>Label">Checklist Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Asset Info -->
                    <table class="table table-sm mb-3">
                        <tr><th>Asset ID</th><td><?= htmlspecialchars($a['asset_id'] ?? '') ?></td></tr>
                        <tr><th>Asset Name</th><td><?= htmlspecialchars($a['asset_name'] ?? '') ?></td></tr>
                        <tr><th>Location</th><td><?= htmlspecialchars(trim($a['location_id_3'] ?? '')) ?></td></tr>
                        <tr><th>Due Date</th><td><?= htmlspecialchars($a['next_maintenance_date'] ?? '') ?></td></tr>
                        <tr><th>Maintenance Type</th><td><?= htmlspecialchars($a['maintenance_type'] ?? '') ?></td></tr>
                        <tr><th>Status</th><td><?= htmlspecialchars($a['status'] ?? 'Upcoming') ?></td></tr>
                        <tr><th>Technician</th><td><?= htmlspecialchars($a['technician_name'] ?? '') ?></td></tr>
                        <tr><th>Work Order</th><td><?= htmlspecialchars($a['work_order_ref'] ?? '') ?></td></tr>
                        <tr><th>Checklist</th><td><?= htmlspecialchars($a['checklist_id'] ?? '') ?></td></tr>
                    </table>

                    <!-- Checklist Tasks -->
                    <table class="table table-bordered">
                        <thead><tr><th>Task Order</th><th>Task Description</th></tr></thead>
                        <tbody>
                            <?php if (!empty($tasks)): ?>
                                <?php foreach ($tasks as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['task_order'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($t['task_text'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center">No tasks found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="/mes/maintenance_checklist/view">
                        <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($a['tenant_id'] ?? '') ?>">
                        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($a['asset_id'] ?? '') ?>">
                        <input type="hidden" name="checklist_id" value="<?= htmlspecialchars($a['checklist_id'] ?? '') ?>">
                        <input type="hidden" name="work_order_ref" value="<?= htmlspecialchars($a['work_order_ref'] ?? '') ?>">

                        <button type="submit" class="btn btn-primary">ASSOCIATE</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>

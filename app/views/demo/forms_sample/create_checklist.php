<!-- /public/forms/create_checklist.php -->
<?php
/*
if (session_status() === PHP_SESSION_NONE) session_start();
$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    header("Location: /signin");
    exit;
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Checklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-sm mt-4">
    <h3>Create Maintenance Checklist</h3>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/handler/createChecklist_handler.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="mb-3">
            <label class="form-label">Asset ID *</label>
            <input type="text" name="asset_id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Asset Name *</label>
            <input type="text" name="asset_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location_id" class="form-control">
        </div>

        <!--- link to table scheduled_maintenance -->
        <!--div class="mb-3">
            <label class="form-label">Maintenance Type *</label>
            <select name="maintenance_type" class="form-select" required>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Semi-Annual">Semi-Annual</option>
                <option value="Annual">Annual</option>
                <option value="Routine">Routine</option>
            </select>
        </div-->

        <div class="mb-3">
            <label class="form-label">Maintenance Name</label>
            <input type="text" name="maintenance_type" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Work Order</label>
            <input type="text" name="work_order" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Technician</label>
            <input type="text" name="technician" class="form-control">
        </div>

        <h5>Tasks</h5>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="mb-3">
                <input type="text" name="task_<?= $i ?>" class="form-control"
                       placeholder="Task <?= $i ?>" required>
            </div>
        <?php endfor; ?>

        <button type="submit" class="btn btn-primary">Create Checklist</button>
    </form>
</div>
</body>
</html>
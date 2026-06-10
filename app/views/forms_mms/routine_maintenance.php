<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Routine Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:700px;">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">Home</a></li>
            <li class="breadcrumb-item active">Routine Maintenance</li>
        </ol>
		
		
    </nav>

    <h3>Create Routine Maintenance</h3>
    <p class="text-muted">Select filters to generate work orders for specific assets or maintenance types.</p>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="/mes/form_mms/routine_maintenance">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- ASSETS -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Select Assets</label>
            <select class="form-select" name="asset_ids[]" multiple required>
                <?php foreach ($filters['assets'] as $asset): ?>
                    <option value="<?= htmlspecialchars($asset['asset_id']) ?>">
                        <?= htmlspecialchars($asset['asset_name']) ?> (<?= htmlspecialchars($asset['asset_id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Hold CTRL (Windows) or CMD (Mac) to select multiple assets.</small>
        </div>

        <!-- WORK ORDER / TEMPLATE SELECTOR -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Select Work Order Template</label>
            <select class="form-select" name="work_order" id="work_order" onchange="loadMaintenanceType()" required>
                <option value="">-- Select Work Order / Checklist --</option>
                <?php foreach ($filters['work_order'] as $wo): ?>
                    <?php 
                        $label = htmlspecialchars($wo['work_order']) . ' (' 
                               . 'ID: ' . htmlspecialchars($wo['checklist_id']) . ' | '
                               . 'Type: ' . htmlspecialchars($wo['maintenance_type']) . ' | '
                               . 'Interval: ' . htmlspecialchars($wo['interval_days']) . ' days'
                               . (!empty($wo['description']) ? ' | ' . htmlspecialchars($wo['description']) : '')
                               . ')';
                    ?>
                    <option value="<?= htmlspecialchars($wo['work_order']) ?>"
                            data-type="<?= htmlspecialchars($wo['maintenance_type']) ?>"
                            data-checklist-id="<?= htmlspecialchars($wo['checklist_id']) ?>"
                            data-interval="<?= htmlspecialchars($wo['interval_days']) ?>"
                            data-desc="<?= htmlspecialchars($wo['description'] ?? '') ?>">
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- AUTO-FILLED MAINTENANCE TYPE -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Maintenance Type</label>
            <input type="text" class="form-control bg-light" name="maintenance_type" id="maintenance_type" readonly>
        </div>

        <!-- AUTO-FILLED CHECKLIST ID -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Checklist ID</label>
            <input type="text" class="form-control bg-light" id="checklist_id" readonly>
        </div>

        <!-- AUTO-FILLED INTERVAL -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Interval</label>
            <input type="text" class="form-control bg-light" id="interval_days" readonly>
        </div>

        <!-- AUTO-FILLED CHECKLIST DESCRIPTION -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Checklist Description</label>
            <textarea class="form-control bg-light" id="checklist_description" rows="2" readonly></textarea>
        </div>

        <!-- TECHNICIAN -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Technician Override</label>
            <select class="form-select" name="technician">
                <option value="">-- All Technicians --</option>
                <?php foreach ($filters['technicians'] as $tech): ?>
                    <option value="<?= htmlspecialchars($tech) ?>"><?= htmlspecialchars($tech) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mt-4 text-center">
            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">CREATE</button>
            <a href="/mes/mms_admin" class="btn btn-outline-primary ms-2">Back to Dashboard</a>
        </div>
      
    </form>
</div>

<script>
function loadMaintenanceType() {
    let select = document.getElementById("work_order");
    let selectedOption = select.options[select.selectedIndex];
    
    let typeField = document.getElementById("maintenance_type");
    let checklistIdField = document.getElementById("checklist_id");
    let intervalField = document.getElementById("interval_days");
    let descField = document.getElementById("checklist_description");

    if (select.value === "") {
        if (typeField) typeField.value = "";
        if (checklistIdField) checklistIdField.value = "";
        if (intervalField) intervalField.value = "";
        if (descField) descField.value = "";
        return;
    }

    // Read attributes directly from the selected option
    let type = selectedOption.getAttribute("data-type") || "";
    let checklistId = selectedOption.getAttribute("data-checklist-id") || "";
    let interval = selectedOption.getAttribute("data-interval") || "";
    let desc = selectedOption.getAttribute("data-desc") || "";

    if (typeField) typeField.value = type;
    if (checklistIdField) checklistIdField.value = checklistId;
    if (intervalField) intervalField.value = interval ? interval + " days" : "";
    if (descField) descField.value = desc;
}
</script>

</body>
</html>

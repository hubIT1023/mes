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

        <!-- WORK ORDER -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Work Order</label>
            <select class="form-select" name="work_order" id="work_order" onchange="loadMaintenanceType()" required>
                <option value="">-- Select Work Order --</option>
                <?php foreach ($filters['work_order'] as $wo): ?>
                    <option value="<?= htmlspecialchars($wo) ?>"><?= htmlspecialchars($wo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- AUTO-FILLED MAINTENANCE TYPE -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Maintenance Type</label>
            <input type="text" class="form-control" name="maintenance_type" id="maintenance_type" readonly>
        </div>

        <!-- TECHNICIAN -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Technician</label>
            <select class="form-select" name="technician">
                <option value="">-- All Technicians --</option>
                <?php foreach ($filters['technicians'] as $tech): ?>
                    <option value="<?= htmlspecialchars($tech) ?>"><?= htmlspecialchars($tech) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        	<div class="mt-4 text-center">
				<button type="submit" class="btn btn-success btn-lg">CREATE</button>
				<a href="/mes/dashboard_upcoming_maint" class="btn btn-outline-primary">Back </a>
			</div>
      
    </form>

    
</div>

<script>
function loadMaintenanceType() {
    let wo = document.getElementById("work_order").value;
    let field = document.getElementById("maintenance_type");

    if (wo === "") {
        field.value = "";
        return;
    }

    fetch("/mes/api/get_maintenance_type_by_work_order?work_order=" + encodeURIComponent(wo))
        .then(response => response.json())
        .then(data => {
            field.value = data.maintenance_type || "";
        })
        .catch(err => console.error("Error loading maintenance type:", err));
}
</script>

</body>
</html>

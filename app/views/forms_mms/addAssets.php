<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Asset</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { max-width: 800px; margin: 2rem auto; }
        .required::after { content: " *"; color: red; }
    </style>
</head>
<body>
<div class="container form-container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">Home</a></li>
            <li class="breadcrumb-item active">Add Asset</li>
        </ol>
    </nav>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800">Asset Registration</h3>
        <div>
            <a href="/mes/assets/list" class="btn btn-sm btn-outline-success me-2">Asset List</a>
            <a href="/mes/signout" class="btn btn-sm btn-outline-primary">Sign Out</a>
        </div>
    </div>

    <!-- ✅ Success/Error Alerts -->
	<?php if (!empty($success)): ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
			<?= htmlspecialchars($success) ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php elseif (!empty($error)): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
			<?= htmlspecialchars($error) ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php endif; ?>


    <form method="POST" action="/mes/form_mms/addAsset">
        <!-- Tenant UUID -->
        <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenant['org_id']) ?>">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label class="form-label required">Asset ID*</label>
            <input type="text" name="asset_id" class="form-control" required>
        </div>

		<div class="mb-3">
			<label class="form-label">Asset Name *</label>
			<input type="text" class="form-control" name="asset_name" required>
		</div>

		<div class="mb-3">
			<label class="form-label">Serial No *</label>
			<input type="text" class="form-control" name="serial_no" required>
		</div>

		<div class="mb-3">
			<label class="form-label">Cost Center *</label>
			<input type="text" class="form-control" name="cost_center" required>
		</div>

		<div class="mb-3">
			<label class="form-label">Department *</label>
			<input type="text" class="form-control" name="department" required>
		</div>

		<!-- Optional fields -->
		<div class="mb-3">
			<label class="form-label">Equipment Description</label>
			<input type="text" class="form-control" name="equipment_description">
		</div>

		<div class="mb-3">
			<label class="form-label">Location ID 1</label>
			<input type="text" class="form-control" name="location_id_1">
		</div>

		<div class="mb-3">
			<label class="form-label">Location ID 2</label>
			<input type="text" class="form-control" name="location_id_2">
		</div>

		<div class="mb-3">
			<label class="form-label">Location ID 3</label>
			<input type="text" class="form-control" name="location_id_3">
		</div>

		<div class="mb-3">
			<label class="form-label">Vendor ID</label>
			<input type="text" class="form-control" name="vendor_id">
		</div>

		<div class="mb-3">
			<label class="form-label">MFG Code</label>
			<input type="text" class="form-control" name="mfg_code">
		</div>

		<div class="mb-3">
			<label class="form-label">Status</label>
			<select class="form-control" name="status">
				<option value="active">Active</option>
				<option value="inactive">Inactive</option>
				<option value="decommissioned">Decommissioned</option>
			</select>
		</div>

        <div class="d-grid">
            <button type="submit" class="btn btn-success">Save Asset</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');

        if (successAlert) {
            setTimeout(() => bootstrap.Alert.getOrCreateInstance(successAlert).close(), 5000);
        }

        if (errorAlert) {
            setTimeout(() => bootstrap.Alert.getOrCreateInstance(errorAlert).close(), 5000);
        }
    });
</script>

</body>
</html>

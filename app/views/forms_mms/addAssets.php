<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Asset | Asset Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .form-container { max-width: 900px; margin: 3rem auto; }
        .card { border: none; border-radius: 12px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); }
        .card-header { background-color: #fff; border-bottom: 1px solid #eee; padding: 1.5rem; border-radius: 12px 12px 0 0 !important; }
        .required::after { content: " *"; color: #dc3545; font-weight: bold; }
        .form-label { font-weight: 500; color: #495057; font-size: 0.9rem; }
        .section-title { font-size: 1.1rem; font-weight: 600; color: #0d6efd; margin-bottom: 1.5rem; border-bottom: 2px solid #e9ecef; pb-2; }
    </style>
</head>
<body>

<div class="container form-container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active">Add Asset</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0 text-dark">Asset Registration</h3>
        <div>
            <a href="/mes/assets/list" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-list-ul"></i> Asset List
            </a>
            <a href="/mes/signout" class="btn btn-outline-danger btn-sm px-3 ms-2">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" id="success-alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" id="error-alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="/mes/form_mms/addAsset" class="needs-validation">
                <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenant['org_id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="section-title text-uppercase small tracking-wider">Primary Details</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label required">Asset ID</label>
                        <input type="text" name="asset_id" class="form-control" placeholder="e.g. AST-001" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label required">Asset Name</label>
                        <input type="text" name="asset_name" class="form-control" placeholder="Enter asset name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Serial No</label>
                        <input type="text" name="serial_no" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="decommissioned">Decommissioned</option>
                        </select>
                    </div>
                </div>

                <div class="section-title text-uppercase small tracking-wider">Classification & Location</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label required">Department</label>
                        <input type="text" name="department" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Cost Center</label>
                        <input type="text" name="cost_center" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location ID 1</label>
                        <input type="text" class="form-control" name="location_id_1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location ID 2</label>
                        <input type="text" class="form-control" name="location_id_2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location ID 3</label>
                        <input type="text" class="form-control" name="location_id_3">
                    </div>
                </div>

                <div class="section-title text-uppercase small tracking-wider">Additional Info</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Vendor ID</label>
                        <input type="text" class="form-control" name="vendor_id">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">MFG Code</label>
                        <input type="text" class="form-control" name="mfg_code">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Equipment Description</label>
                        <textarea class="form-control" name="equipment_description" rows="2"></textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-light px-4">Clear Form</button>
                    <button type="submit" class="btn btn-success px-5 fw-bold">
                        <i class="bi bi-save me-2"></i> Save Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 5000);
        });
    });
</script>

</body>
</html>
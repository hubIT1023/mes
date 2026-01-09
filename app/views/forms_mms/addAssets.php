<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Asset | Asset Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* Only keeping what Bootstrap doesn't have: the asterisk and smooth transition */
        .required::after { content: " *"; color: #dc3545; }
        .transition-all { transition: all 0.2s ease-in-out; }
    </style>
</head>
<body class="bg-body-tertiary">

<div class="container py-5" style="max-width: 900px;">
    
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/mes/mms_admin" class="link-underline-opacity-0">Home</a></li>
            <li class="breadcrumb-item active">Add Asset</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0">Asset Registration</h3>
            <p class="text-muted small mb-0">Register new equipment to the factory floor database.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/mes/assets/list" class="btn btn-white border shadow-sm btn-sm px-3">
                <i class="bi bi-list-ul me-1"></i> Asset List
            </a>
            <a href="/mes/signout" class="btn btn-outline-danger btn-sm px-3">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($success) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($error) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="/mes/form_mms/addAsset" class="needs-validation" novalidate>
                <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenant['org_id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="d-flex align-items-center mb-3 text-primary">
                    <i class="bi bi-info-square-fill me-2"></i>
                    <span class="fw-bold text-uppercase small tracking-wider">Primary Details</span>
                </div>
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold required">Asset ID</label>
                        <input type="text" name="asset_id" class="form-control form-control-lg bg-light border-0 shadow-none fs-6" placeholder="e.g. AST-001" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold required">Asset Name</label>
                        <input type="text" name="asset_name" class="form-control form-control-lg bg-light border-0 shadow-none fs-6" placeholder="Enter asset name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold required">Serial No</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-hash"></i></span>
                            <input type="text" name="serial_no" class="form-control form-control-lg bg-light border-0 shadow-none fs-6" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Status</label>
                        <select class="form-select form-select-lg bg-light border-0 shadow-none fs-6" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="decommissioned">Decommissioned</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3 text-primary">
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    <span class="fw-bold text-uppercase small tracking-wider">Classification & Location</span>
                </div>
                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold required text-muted">Department</label>
                        <input type="text" name="department" class="form-control bg-light border-0 shadow-none" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold required text-muted">Cost Center</label>
                        <input type="text" name="cost_center" class="form-control bg-light border-0 shadow-none" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Plant / Bldg</label>
                        <input type="text" class="form-control bg-light border-0 shadow-none" name="location_id_1" placeholder="Loc 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Floor / Area</label>
                        <input type="text" class="form-control bg-light border-0 shadow-none" name="location_id_2" placeholder="Loc 2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Line / Station</label>
                        <input type="text" class="form-control bg-light border-0 shadow-none" name="location_id_3" placeholder="Loc 3">
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3 text-primary">
                    <i class="bi bi-plus-circle-fill me-2"></i>
                    <span class="fw-bold text-uppercase small tracking-wider">Additional Info</span>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Vendor ID</label>
                        <input type="text" class="form-control bg-light border-0 shadow-none" name="vendor_id">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">MFG Code</label>
                        <input type="text" class="form-control bg-light border-0 shadow-none" name="mfg_code">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Equipment Description</label>
                        <textarea class="form-control bg-light border-0 shadow-none" name="equipment_description" rows="3"></textarea>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <button type="reset" class="btn btn-link link-secondary text-decoration-none small">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear form
                    </button>
                    <div class="d-flex gap-2 w-100 w-sm-auto">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold w-100 w-sm-auto shadow-sm transition-all">
                            Save Asset Registration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        'use strict'
        // Bootstrap Validation
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000);
    })()
</script>

</body>
</html>
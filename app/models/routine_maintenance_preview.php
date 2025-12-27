<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Session expired, please sign in again");
    exit;
}

$tenant = $_SESSION['tenant'];
$csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Optional alerts
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Routine Maintenance Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 1100px; margin-top: 2rem; }
        table { font-size: 0.95rem; }
        .table thead { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">Home</a></li>
            <li class="breadcrumb-item"><a href="/mes/form_mms/routine_maintenance">Routine Maintenance</a></li>
            <li class="breadcrumb-item active">Preview Work Orders</li>
        </ol>
    </nav>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800">Routine Maintenance Preview</h3>
        <div>
            <a href="/mes/dashboard_upcoming_maint" class="btn btn-sm btn-outline-success me-2">Dashboard</a>
            <a href="/mes/signout" class="btn btn-sm btn-outline-primary">Sign Out</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($data)): ?>
        <form method="POST" action="/mes/form_mms/routine_maintenance_generate">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenant['org_id']) ?>">

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">Preview Matching Work Orders</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Asset ID</th>
                                <th>Asset Name</th>
                                <th>Maintenance Type</th>
                                <th>Technician</th>
                                <th>Checklist Title</th>
                                <th>Work Order Ref</th>
                                <th>Interval (Days)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($row['asset_id']) ?></td>
                                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($row['maintenance_type']) ?></td>
                                    <td><?= htmlspecialchars($row['technician']) ?></td>
                                    <td><?= htmlspecialchars($row['checklist_title']) ?></td>
                                    <td><?= htmlspecialchars($row['work_order']) ?></td>
                                    <td><?= htmlspecialchars($row['interval_days']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="/mes/form_mms/routine_maintenance" class="btn btn-outline-secondary">
                    ← Back to Filters
                </a>
                <button type="submit" class="btn btn-success">
                    ✅ Generate Routine Work Orders
                </button>
            </div>
        </form>

    <?php else: ?>
        <div class="alert alert-warning text-center">
            ⚠️ No records found for the selected filters.
        </div>
        <div class="text-center mt-3">
            <a href="/mes/form_mms/routine_maintenance" class="btn btn-primary">🔁 Back to Filters</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


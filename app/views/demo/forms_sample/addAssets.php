<?php
// /public/forms/addAssets.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    header("Location: /signin");
    exit;
}

// Flash messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Asset</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- ? Fixed: Removed trailing space in CDN URL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
        }
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
<div class="container form-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Add Asset</li>
        </ol>
    </nav>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h3 class="h3 mb-0 text-gray-800">Dashboard</h3>
        <div>
            <a href="/assets-list" class="btn btn-sm btn-outline-success me-2">
                 Asset List
            </a>
            <a href="/signout" class="btn btn-sm btn-outline-primary">
                Sign Out
            </a>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="/handler/addAssets_handler.php">
        <!-- ? CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Asset ID -->
        <div class="mb-3">
            <label class="form-label required">Asset ID</label>
            <input type="text" name="asset_id" class="form-control" required>
        </div>

        <!-- Asset Name -->
        <div class="mb-3">
            <label class="form-label required">Asset Name</label>
            <input type="text" name="asset_name" class="form-control" required>
        </div>

        <!-- Location ID -->
        <div class="mb-3">
            <label class="form-label">Location ID</label>
            <input type="text" name="location_id" class="form-control">
        </div>

        <!-- Vendor ID -->
        <div class="mb-3">
            <label class="form-label">Vendor ID</label>
            <input type="text" name="vendor_id" class="form-control">
        </div>

        <!-- MFG Code -->
        <div class="mb-3">
            <label class="form-label">Manufacturer Code</label>
            <input type="text" name="mfg_code" class="form-control">
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" selected>Active</option>
                <option value="discarded">Discarded</option>
            </select>
        </div>

        <!-- Submit -->
        <div class="d-grid">
            <button type="submit" class="btn btn-success">Save Asset</button>
        </div>
    </form>

    <div class="mt-4">
        <a href="/dashboard" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<!-- ? Fixed: Removed trailing space in CDN URL -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
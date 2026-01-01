<?php
// create_checlist.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tenant = $_SESSION['tenant'] ?? null;
if (!$tenant) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

/*/  Fetch tenant assets for dropdown
require_once __DIR__ . '/../../../models/AssetModel.php';
$assetModel = new AssetModel();
$assets = $assetModel->getAssetsByTenant($tenant);
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Checklist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .form-container {
            max-width: 850px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-light">

<div class="container form-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Checklist</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h3 text-gray-800">Create Checklist</h3>
        <div>
            <a href="/mes/mms_admin" class="btn btn-outline-secondary btn-sm me-2">Dashboard</a>
            <a href="/mes/signout" class="btn btn-outline-primary btn-sm">Sign Out</a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php elseif (!empty($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="/mes/form_mms/addMaintenance">

        <!-- Asset Selection -->
        <div class="mb-3">
            <label for="asset_id" class="form-label required">Select Asset</label>
            <select name="asset_id" id="asset_id" class="form-select" required>
                <option value="">-- Select Asset --</option>
                <?php foreach ($assets as $asset): ?>
                    <option value="<?= htmlspecialchars($asset['asset_id']) ?>">
                        <?= htmlspecialchars($asset['asset_name']) ?> (<?= htmlspecialchars($asset['asset_id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Maintenance Type -->
        <div class="mb-3">
            <label class="form-label required">Maintenance Type</label>
            <select name="maintenance_type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="Preventive">Preventive</option>
                <option value="Corrective">Corrective</option>
                <option value="Inspection">Inspection</option>
            </select>
        </div>

        <!-- Maintenance Date -->
        <div class="mb-3">
            <label class="form-label required">Maintenance Date</label>
            <input type="date" name="maintenance_date" class="form-control" required>
        </div>

        <!-- Technician -->
        <div class="mb-3">
            <label class="form-label">Technician Name</label>
            <input type="text" name="technician" class="form-control" placeholder="Technician or Team">
        </div>

        <!-- Work Order -->
        <div class="mb-3">
            <label class="form-label">Work Order No.</label>
            <input type="text" name="work_order" class="form-control" placeholder="Enter work order number">
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description / Remarks</label>
            <textarea name="description" rows="3" class="form-control" placeholder="Enter details about this maintenance"></textarea>
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
            </select>
        </div>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg">Generate Checklist</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
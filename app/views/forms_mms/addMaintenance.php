<?php
// addMaintenance.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  Ensure tenant is logged in
if (!isset($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

$tenant = $_SESSION['tenant'];

// Optional success/error message passed from controller
$success = $success ?? null;
$error = $error ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Maintenance Record</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
<body class="bg-light">

<div class="container form-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">MMS Dashboard</a></li>
            <li class="breadcrumb-item active">Add Maintenance Record</li>
        </ol>
    </nav>

    <h3 class="mb-4 text-center">Scheduled Maintenance </h3>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="/mes/form_mms/addMaintenance">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Tenant UUID -->
        <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenant['org_id'] ?? '') ?>">

        <!-- Asset ID -->
        <div class="mb-3">
            <label class="form-label required">Asset ID</label>
            <input type="text" name="asset_id" class="form-control" required>
        </div>

        <!-- Maintenance Type -->
        <div class="mb-3">
            <label class="form-label required">Maintenance Type</label>
            <select name="maintenance_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="preventive">Preventive</option>
                <option value="corrective">Corrective</option>
                <option value="inspection">Inspection</option>
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
            <input type="text" name="technician" class="form-control">
        </div>

        <!-- Work Order -->
        <div class="mb-3">
            <label class="form-label">Work Order Reference</label>
            <input type="text" name="work_order" class="form-control">
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description / Notes</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <!-- Next Maintenance Date -->
        <div class="mb-3">
            <label class="form-label">Next Maintenance Date</label>
            <input type="date" name="next_maintenance_date" class="form-control">
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label required">Status</label>
            <select name="status" class="form-select" required>
                <option value="scheduled">Scheduled</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="delayed">Delayed</option>
            </select>
        </div>

        <!-- Submit -->
        <div class="d-grid">
            <button type="submit" class="btn btn-success">Save Maintenance Record</button>
        </div>
    </form>

    <div class="mt-4">
        <a href="/mes/mms_admin" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

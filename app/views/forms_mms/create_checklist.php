<?php
// create_checklist.php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ------------------------------
   Authentication
--------------------------------*/
$tenant = $_SESSION['tenant'] ?? null;
if (!$tenant) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

/* ------------------------------
   CSRF Token
--------------------------------*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ------------------------------
   Fetch Tenant Assets
--------------------------------*/
require_once __DIR__ . '/../../../models/AssetModel.php';

$assetModel = new AssetModel();
$assets = $assetModel->getAssetsByTenant($tenant);

/* ------------------------------
   Helper for escaping
--------------------------------*/
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/* ------------------------------
   Sticky values
--------------------------------*/
$old = $_SESSION['old_form'] ?? [];
unset($_SESSION['old_form']);
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
            box-shadow: 0 4px 10px rgba(0,0,0,.1);
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
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="/mes/mms_admin">Home</a></li>
            <li class="breadcrumb-item active">Create Checklist</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Create Maintenance Checklist</h3>
        <div>
            <a href="/mes/mms_admin" class="btn btn-outline-secondary btn-sm">Dashboard</a>
            <a href="/mes/signout" class="btn btn-outline-danger btn-sm">Sign Out</a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= e($_GET['success']) ?></div>
    <?php elseif (!empty($_GET['error'])): ?>
        <div class="alert alert-danger"><?= e($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="/mes/form_mms/addMaintenance" novalidate>

        <!-- Asset -->
        <div class="mb-3">
            <label for="asset_id" class="form-label required">Asset</label>
            <select name="asset_id" id="asset_id" class="form-select" required>
                <option value="">-- Select Asset --</option>
                <?php foreach ($assets as $asset): ?>
                    <option value="<?= e($asset['asset_id']) ?>"
                        <?= ($old['asset_id'] ?? '') === $asset['asset_id'] ? 'selected' : '' ?>>
                        <?= e($asset['asset_name']) ?> (<?= e($asset['asset_id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Maintenance Type -->
        <div class="mb-3">
            <label class="form-label required">Maintenance Type</label>
            <select name="maintenance_type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <?php foreach (['Preventive', 'Corrective', 'Inspection'] as $type): ?>
                    <option value="<?= $type ?>"
                        <?= ($old['maintenance_type'] ?? '') === $type ? 'selected' : '' ?>>
                        <?= $type ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Date -->
        <div class="mb-3">
            <label class="form-label required">Maintenance Date</label>
            <input type="date"
                   name="maintenance_date"
                   class="form-control"
                   value="<?= e($old['maintenance_date'] ?? '') ?>"
                   required>
        </div>

        <!-- Technician -->
        <div class="mb-3">
            <label class="form-label">Technician / Team</label>
            <input type="text"
                   name="technician"
                   class="form-control"
                   value="<?= e($old['technician'] ?? '') ?>"
                   placeholder="Technician or team name">
        </div>

        <!-- Work Order -->
        <div class="mb-3">
            <label class="form-label">Work Order No.</label>
            <input type="text"
                   name="work_order"
                   class="form-control"
                   value="<?= e($old['work_order'] ?? '') ?>">
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="description"
                      class="form-control"
                      rows="3"><?= e($old['description'] ?? '') ?></textarea>
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['scheduled', 'pending', 'completed'] as $status): ?>
                    <option value="<?= $status ?>"
                        <?= ($old['status'] ?? 'scheduled') === $status ? 'selected' : '' ?>>
                        <?= ucfirst($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- CSRF -->
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg">
                Generate Checklist
            </button>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

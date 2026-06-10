<?php 
// checklist_lists.php

if (session_status() === PHP_SESSION_NONE) session_start(); 
$tenant = $_SESSION['tenant'] ?? ['org_name' => 'Your Organization'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checklists | Maintenance Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        .transition-all { transition: all 0.2s ease-in-out; }
        .table-middle td { vertical-align: middle; }
    </style>
</head>
<body class="bg-body-tertiary">

<div class="container py-5" style="max-width: 1200px;">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/mes/mms_admin" class="link-underline-opacity-0">Home</a></li>
            <li class="breadcrumb-item active">Checklists</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0">Checklist Templates</h3>
            <p class="text-muted small mb-0">Configure, search, and manage inspection guidelines for <?= htmlspecialchars($tenant['org_name'] ?? 'Your Organization') ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/mes/form_mms/checklist_template" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Create Template
            </a>
            <a href="/mes/mms_admin" class="btn btn-white border shadow-sm btn-sm px-3">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filters & Live Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="" class="row g-3 align-items-center">
                <!-- Unified Search (either Checklist ID or Maintenance Type) -->
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">Search Term</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" id="serverSearchInput" class="form-control border-start-0 ps-1" 
                               placeholder="Search Checklist ID, type, or description..."
                               value="<?= htmlspecialchars($_GET['search'] ?? $_GET['checklist_id'] ?? '') ?>">
                    </div>
                </div>

                <!-- Dynamic Maintenance Type Dropdown -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Maintenance Type</label>
                    <select class="form-select" name="maintenance_type" id="maintTypeSelect">
                        <option value="">-- All Types --</option>
                        <?php if (!empty($maintenanceTypes)): ?>
                            <?php foreach ($maintenanceTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= (($_GET['maintenance_type'] ?? '') === $type) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallbacks if no template exists yet -->
                            <option value="PM" <?= (($_GET['maintenance_type'] ?? '') === 'PM') ? 'selected' : '' ?>>PM</option>
                            <option value="CM" <?= (($_GET['maintenance_type'] ?? '') === 'CM') ? 'selected' : '' ?>>CM</option>
                            <option value="Inspection" <?= (($_GET['maintenance_type'] ?? '') === 'Inspection') ? 'selected' : '' ?>>Inspection</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-dark w-100 shadow-sm">
                        <i class="bi bi-funnel me-1"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Client-side Interactive live filter indicator -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            <span class="fw-bold" id="resultCount">Showing all</span> checklist templates.
        </div>
        <div>
            <input type="text" id="clientLiveSearch" class="form-control form-control-sm px-3 shadow-none border rounded-pill" 
                   placeholder="Live table filter..." style="max-width: 250px;">
        </div>
    </div>

    <!-- Table Container -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-middle mb-0 align-middle" id="checklistTable">
                    <thead class="table-light border-0">
                        <tr>
                            <th class="ps-4 py-3 text-muted text-uppercase small" style="width: 150px;">Checklist ID</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 150px;">Maintenance Type</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 150px;">Work Order Code</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 120px;">Interval</th>
                            <th class="py-3 text-muted text-uppercase small">Description</th>
                            <th class="py-3 text-muted text-uppercase small">Task Details / Steps</th>
                            <th class="pe-4 py-3 text-muted text-uppercase text-center small" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php 
                        if (!empty($checklists)):
                            // Group rows by checklist ID to list steps cleanly
                            $grouped = [];
                            foreach ($checklists as $row) {
                                $grouped[$row['checklist_id']][] = $row;
                            }

                            foreach ($grouped as $checklistId => $rows): 
                                $first = $rows[0];
                                $maintType = strtolower($first['maintenance_type'] ?? 'inspection');
                                
                                // Color badge logic
                                $badgeClass = 'bg-secondary';
                                if ($maintType === 'pm') {
                                    $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                } elseif ($maintType === 'cm') {
                                    $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif ($maintType === 'inspection') {
                                    $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                }
                        ?>
                            <tr class="checklist-row">
                                <td class="ps-4 fw-semibold text-primary">
                                    <?= htmlspecialchars($checklistId) ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2.5 py-1.5 <?= $badgeClass ?> text-uppercase fw-semibold" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($first['maintenance_type'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="font-mono small text-dark">
                                    <?= htmlspecialchars($first['work_order'] ?? '-') ?>
                                </td>
                                <td class="small">
                                    <?= htmlspecialchars($first['interval_days'] ?? '30') ?> days
                                </td>
                                <td class="small text-muted">
                                    <?= htmlspecialchars($first['description'] ?: '-') ?>
                                </td>
                                <td>
                                    <ol class="mb-0 ps-3 small text-muted">
                                        <?php foreach ($rows as $task): ?>
                                            <?php if (!empty($task['task_text'])): ?>
                                                <li><?= htmlspecialchars($task['task_text']) ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ol>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="/mes/form_mms/checklist_edit?checklist_id=<?= urlencode($checklistId) ?>" 
                                       class="btn btn-outline-warning btn-sm px-3 rounded-pill shadow-sm transition-all">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="mb-3 text-secondary opacity-25">
                                        <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="fw-bold">No Checklists Found</h5>
                                    <p class="small text-muted mb-3">Adjust your search parameters or register a new checklist template.</p>
                                    <a href="/mes/form_mms/checklist_template" class="btn btn-primary btn-sm px-4">Create Checklist</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const liveSearchInput = document.getElementById('clientLiveSearch');
    const tableRows = document.querySelectorAll('#checklistTable tbody tr.checklist-row');
    const resultCount = document.getElementById('resultCount');

    function updateCounter(visibleCount) {
        if (resultCount) {
            resultCount.textContent = visibleCount === tableRows.length 
                ? "Showing all " + visibleCount
                : "Showing " + visibleCount + " of " + tableRows.length;
        }
    }

    updateCounter(tableRows.length);

    liveSearchInput?.addEventListener('keyup', function (e) {
        const query = e.target.value.toLowerCase().trim();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        updateCounter(visibleCount);
    });
});
</script>
</body>
</html>
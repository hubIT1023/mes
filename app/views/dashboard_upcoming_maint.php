<?php
/**
 * dashboard_upcoming_maint.php
 * 
 * Maintenance dashboard showing scheduled & in-progress work orders.
 * - Shows "Associate Checklist" button for unassociated records
 * - Shows "Checklist #000123" link for active checklists
 * - Automatically hides completed checklists (archived + deleted)
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

require_once __DIR__ . '/../models/AssociateChecklistModel.php';
require_once __DIR__ . '/../models/RoutineWorkOrderModel.php';

$tenantId = $_SESSION['tenant']['org_id'];
$checklistModel = new AssociateChecklistModel();
$routineModel = new RoutineWorkOrderModel();

// Handle POST: Associate Checklist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['associate_checklist'])) {
    $tenant_id       = $_POST['tenant_id'] ?? null;
    $asset_id        = $_POST['asset_id'] ?? null;
    $checklist_id    = $_POST['checklist_id'] ?? null;
    $work_order_ref  = $_POST['work_order_ref'] ?? null;
    $technician_name = $_POST['technician'] ?? null;

    if ($tenant_id && $asset_id && $checklist_id && $work_order_ref) {
        try {
            if ($checklistModel->isChecklistAssociated($tenant_id, $asset_id, $checklist_id, $work_order_ref)) {
                $_SESSION['flash_message'] = "Checklist already associated.";
            } else {
                $maintenanceId = $checklistModel->associateChecklist(
                    $tenant_id,
                    $asset_id,
                    $checklist_id,
                    $work_order_ref,
                    $technician_name
                );
                
                $checklistModel->updateRoutineWorkOrderStatus(
                    $tenant_id,
                    $asset_id,
                    $checklist_id,
                    $work_order_ref
                );
                
                $_SESSION['flash_message'] = "Checklist successfully associated!";
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Association failed: " . $e->getMessage();
        }
    } else {
        $_SESSION['flash_message'] = "Association failed: Missing required fields.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch upcoming maintenance + filters
$filters = [
    'asset_id' => $_GET['asset_id'] ?? '',
    'asset_name' => $_GET['asset_name'] ?? '',
    'work_order_ref' => $_GET['work_order_ref'] ?? '',
    'maintenance_type' => $_GET['maintenance_type'] ?? '',
    'technician' => $_GET['technician'] ?? ''
];

$assets = $routineModel->getUpcomingMaintenance($tenantId, $filters);
$filterData = $routineModel->getFilterOptions($tenantId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maintenance Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .container-xxl { max-width: 75%; padding: 1rem; }
    .badge { padding: 0.35em 0.65em; border-radius: 0.25rem; font-size: 0.75em; }
    .overdue-row { background-color: #f8d7da !important; }
</style>
</head>
<body>
<div class="container-xxl mt-4">

<?php if (!empty($_SESSION['flash_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['flash_message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_message']); endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h3 class="h3 mb-0 text-gray-800">Maintenance – Scheduled & In-Progress</h3>
    <a href="/mes/mms_admin" class="btn btn-sm btn-outline-primary">Back to MMS Dashboard</a>
</div>

<p class="text-muted">Tools with maintenance due within the next 4 weeks (including overdue).</p>

<!-- Filters Form -->
<form method="GET" action="" class="row g-3 mb-4">
<?php
$filterFields = [
    'asset_id' => 'Asset ID',
    'asset_name' => 'Asset Name',
    'work_order_ref' => 'Work Order',
    'maintenance_type' => 'Maintenance Type',
    'technician' => 'Technician'
];
foreach ($filterFields as $field => $label):
?>
<div class="col-md-2">
    <select name="<?= $field ?>" class="form-select">
        <option value=""><?= $label ?></option>
        <?php if (!empty($filterData[$field])): foreach ($filterData[$field] as $v): ?>
            <option value="<?= htmlspecialchars($v) ?>" <?= (isset($_GET[$field]) && $_GET[$field] === $v) ? 'selected' : '' ?>>
                <?= htmlspecialchars($v) ?>
            </option>
        <?php endforeach; endif; ?>
    </select>
</div>
<?php endforeach; ?>
<div class="col-md-2">
    <button class="btn btn-primary w-100" type="submit">Filter</button>
</div>
</form>

<!-- Maintenance Table -->
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
<thead class="table-light">
<tr>
    <th>REF ID</th>
    <th>Maint Chk_Id</th>
    <th>Asset ID</th>
    <th>Asset Name</th>
    <th>Location</th>
    <th>Due Date</th>
    <th>Type</th>
    <th>Status</th>
    <th>Work Order</th>
    <th>Checklist</th>
    <th>Technician</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php if (empty($assets)): ?>
<tr><td colspan="12" class="text-center text-muted py-4">No maintenance due in the next 30 days.</td></tr>
<?php else: foreach ($assets as $a): 
    $isOverdue = isset($a['next_maintenance_date']) && strtotime($a['next_maintenance_date']) < time();
    
    $isAssociated = $checklistModel->isChecklistAssociated(
        $a['tenant_id'],
        $a['asset_id'],
        $a['checklist_id'],
        $a['work_order_ref']
    );

    $maintenanceId = null;
    if ($isAssociated) {
        // ✅ Use model method instead of helper function
        $maintenanceId = $checklistModel->getMaintenanceChecklistId(
            $a['tenant_id'],
            $a['asset_id'],
            $a['checklist_id'],
            $a['work_order_ref']
        );
    }

    $modalId = "modalChecklist_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $a['asset_id']) . "_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $a['checklist_id']);
?>
<tr class="<?= $isOverdue ? 'overdue-row' : '' ?>">
    <td><?= htmlspecialchars($a['id'] ?? '') ?></td>
    <td><?= htmlspecialchars($maintenanceId ?? '') ?></td>
    <td><?= htmlspecialchars($a['asset_id']) ?></td>
    <td><?= htmlspecialchars($a['asset_name']) ?></td>
    <td><?= htmlspecialchars(trim(($a['location_id_1'] ?? '') . ' ' . ($a['location_id_2'] ?? '') . ' ' . ($a['location_id_3'] ?? ''))) ?></td>
    <td><?= htmlspecialchars($a['next_maintenance_date'] ?? '') ?></td>
    <td><?= htmlspecialchars($a['maintenance_type'] ?? '') ?></td>
    <td><span class="badge <?= $isOverdue ? 'bg-danger' : 'bg-success' ?>">
        <?= htmlspecialchars($a['status'] ?? ($isOverdue ? 'Overdue' : 'Upcoming')) ?>
    </span></td>
    <td><?= htmlspecialchars($a['work_order_ref']) ?></td>
    <td><?= htmlspecialchars($a['checklist_id']) ?></td>
    <td><?= htmlspecialchars($a['technician'] ?? '') ?></td>
    <td>
        <?php if (!$isAssociated): ?>
            <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                Associate Checklist
            </a>
        <?php else: ?>
            <a href="/mes/maintenance_checklist/view?id=<?= htmlspecialchars($maintenanceId) ?>" class="btn btn-success btn-sm">
                Checklist #<?= str_pad(htmlspecialchars($maintenanceId), 6, '0', STR_PAD_LEFT) ?>
            </a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<!-- Modals -->
<?php if (!empty($assets)): foreach ($assets as $a): 
    if ($checklistModel->isChecklistAssociated($a['tenant_id'],$a['asset_id'],$a['checklist_id'],$a['work_order_ref'])) continue;
    
    $tasks = $checklistModel->getChecklistAssociation($a['tenant_id'],$a['asset_id'],$a['checklist_id'],$a['work_order_ref']);
    $modalId = "modalChecklist_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $a['asset_id']) . "_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $a['checklist_id']);
?>
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="<?= $modalId ?>Label">Checklist Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<table class="table table-sm mb-3">
<tr><th>Asset ID</th><td><?= htmlspecialchars($a['asset_id']) ?></td></tr>
<tr><th>Asset Name</th><td><?= htmlspecialchars($a['asset_name']) ?></td></tr>
<tr><th>Location</th><td><?= htmlspecialchars(trim(($a['location_id_1'] ?? '') . ' ' . ($a['location_id_2'] ?? '') . ' ' . ($a['location_id_3'] ?? ''))) ?></td></tr>
<tr><th>Due Date</th><td><?= htmlspecialchars($a['next_maintenance_date'] ?? '') ?></td></tr>
<tr><th>Maintenance Type</th><td><?= htmlspecialchars($a['maintenance_type'] ?? '') ?></td></tr>
<tr><th>Status</th><td><?= htmlspecialchars($a['status'] ?? '') ?></td></tr>
<tr><th>Technician</th><td><?= htmlspecialchars($a['technician'] ?? '') ?></td></tr>
<tr><th>Work Order</th><td><?= htmlspecialchars($a['work_order_ref']) ?></td></tr>
<tr><th>Checklist</th><td><?= htmlspecialchars($a['checklist_id']) ?></td></tr>
</table>

<table class="table table-bordered">
<thead><tr><th>Task Order</th><th>Task Description</th></tr></thead>
<tbody>
<?php if (!empty($tasks)): foreach ($tasks as $t): ?>
<tr><td><?= htmlspecialchars($t['task_order']) ?></td><td><?= htmlspecialchars($t['task_text']) ?></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="2" class="text-center">No tasks found</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<div class="modal-footer">
 <form method="POST" action="/mes/maintenance_checklist/associate">
<input type="hidden" name="associate_checklist" value="1">
<input type="hidden" name="tenant_id" value="<?= htmlspecialchars($a['tenant_id']) ?>">
<input type="hidden" name="asset_id" value="<?= htmlspecialchars($a['asset_id']) ?>">
<input type="hidden" name="checklist_id" value="<?= htmlspecialchars($a['checklist_id']) ?>">
<input type="hidden" name="work_order_ref" value="<?= htmlspecialchars($a['work_order_ref']) ?>">
<input type="hidden" name="technician" value="<?= htmlspecialchars($a['technician'] ?? '') ?>">
<button type="submit" class="btn btn-primary">ASSOCIATE</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</form>
</div>
</div>
</div>
</div>
<?php endforeach; endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
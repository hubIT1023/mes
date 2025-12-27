<?php
// /public/forms/register_asset_modal.php
/*
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    die("Unauthorized: No organization ID");
}

global $pdo;
if (!$pdo) {
    require_once __DIR__ . '/../../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
    if (!$pdo) {
        die("Database connection failed");
    }
}

// Fetch assets
$assets = [];
try {
    $stmt = $pdo->prepare("SELECT asset_id, asset_name, location_id FROM assets_list WHERE org_id = ?");
    $stmt->execute([$orgId]);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Failed to load assets: " . $e->getMessage());
}

// Fetch maintenance options and associated tasks
$options = [];
$tasksByChecklist = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.checklist_id, c.maintenance_type, c.interval_days, c.work_order,
               t.task_order, t.task_text
        FROM custom_checklist c
        LEFT JOIN checklist_tasks t
          ON c.org_id = t.org_id AND c.checklist_id = t.checklist_id
        WHERE c.org_id = ?
        ORDER BY c.maintenance_type, t.task_order
    ");
    $stmt->execute([$orgId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cid = $row['checklist_id'];

        if (!isset($options[$cid])) {
            $options[$cid] = [
                "checklist_id"     => $cid,
                "maintenance_type" => $row['maintenance_type'],
                "interval_days"    => $row['interval_days'],
                "work_order"       => $row['work_order']
            ];
        }

        if (!empty($row['task_text'])) {
            $tasksByChecklist[$cid][] = [
                "task_order" => $row['task_order'],
                "task_text"  => $row['task_text']
            ];
        }
    }
    $options = array_values($options); // reset to numeric keys for foreach
} catch (Exception $e) {
    error_log("Failed to load maintenance options: " . $e->getMessage());
}
*/
?>

<div class="modal fade" id="registerAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/handler/registerAsset_handler.php" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Maintenance*</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <!-- Asset Selection -->
                    <div class="mb-3">
                        <label for="asset_id" class="form-label">Select Asset *</label>
                        <select id="asset_id" name="asset_id" class="form-select" required>
                            <option value="">-- Choose Asset --</option>
                            <?php foreach ($assets as $a): ?>
                                <option value="<?= htmlspecialchars($a['asset_id']) ?>"
                                        data-asset-name="<?= htmlspecialchars($a['asset_name']) ?>"
                                        data-location="<?= htmlspecialchars($a['location_id']) ?>">
                                    <?= htmlspecialchars($a['asset_name']) ?> (<?= htmlspecialchars($a['asset_id']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select an asset.</div>
                    </div>

                    <!-- Auto-filled Hidden Fields -->
                    <input type="hidden" name="asset_name" id="asset_name">
                    <input type="hidden" name="location_id" id="location_id">

                    <!-- Maintenance Option -->
                    <div class="mb-3">
                        <label for="option" class="form-label">Maintenance Type *</label>
                        <select id="option" name="option" class="form-select" required>
                            <option value="">-- Choose Type --</option>
                            <?php foreach ($options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt['checklist_id']) ?>"
                                        data-type="<?= htmlspecialchars($opt['maintenance_type']) ?>"
                                        data-days="<?= (int)$opt['interval_days'] ?>"
                                        data-cid="<?= htmlspecialchars($opt['checklist_id']) ?>"
                                        data-wo="<?= htmlspecialchars($opt['work_order']) ?>">
                                    <?= htmlspecialchars($opt['maintenance_type']) ?> (<?= (int)$opt['interval_days'] ?> days)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a maintenance type.</div>
                    </div>

                    <!-- Dynamic Task List -->
                    <div class="mb-3">
                        <label class="form-label">Checklist Tasks</label>
                        <div id="task-list" class="border rounded p-3 bg-light" style="min-height:60px; font-size:0.95rem;">
                            <em>Select a template to view tasks...</em>
                        </div>
                    </div>

                    <!-- Auto-filled from selection -->
                    <input type="hidden" name="maintenance_type" id="maintenance_type">
                    <input type="hidden" name="interval_days" id="interval_days">
                    <input type="hidden" name="checklist_id" id="checklist_id">
                    <input type="hidden" name="work_order" id="work_order">
                    <input type="hidden" name="next_maintenance_date" id="next_maintenance_date">
                    <input type="hidden" name="status" id="status" value="scheduled">

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Embed checklist tasks as JSON -->
<script>
    const checklistTasks = <?= json_encode($tasksByChecklist) ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const assetSelect = document.getElementById('asset_id');
    const optionSelect = document.getElementById('option');
    const taskList     = document.getElementById('task-list');

    // Fill asset fields
    assetSelect?.addEventListener('change', function () {
        const selected = assetSelect.options[assetSelect.selectedIndex];
        if (selected.dataset.assetName) {
            document.getElementById('asset_name').value = selected.dataset.assetName;
            document.getElementById('location_id').value = selected.dataset.location;
        }
    });

    // Fill maintenance fields + show tasks
    optionSelect?.addEventListener('change', function () {
        const selected = optionSelect.options[optionSelect.selectedIndex];

        if (!selected.value) {
            taskList.innerHTML = '<em>Select a template to view tasks...</em>';
            return;
        }

        // Hidden fields
        document.getElementById('maintenance_type').value = selected.dataset.type;
        document.getElementById('interval_days').value    = selected.dataset.days;
        document.getElementById('checklist_id').value     = selected.dataset.cid;
        document.getElementById('work_order').value       = selected.dataset.wo;

        // Auto-calc next date
        const days = parseInt(selected.dataset.days, 10);
        if (!isNaN(days) && days > 0) {
            const today = new Date();
            const next = new Date(today);
            next.setDate(today.getDate() + days);
            document.getElementById('next_maintenance_date').value = next.toISOString().split('T')[0];
        }

        // Show tasks
        const cid = selected.dataset.cid;
        if (checklistTasks[cid] && checklistTasks[cid].length > 0) {
            taskList.innerHTML = checklistTasks[cid]
                .map(t => `<div class="mb-1"><strong>${t.task_order}.</strong> ${t.task_text}</div>`)
                .join('');
        } else {
            taskList.innerHTML = '<em>No tasks defined for this checklist.</em>';
        }
    });
});
</script>

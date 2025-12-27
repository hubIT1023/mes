<?php
/*
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    header("Location: /signin");
    exit;
}

// Database connection
global $pdo;
if (!$pdo) {
    require_once __DIR__ . '/../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
    if (!$pdo) {
        die("Database connection failed");
    }
}

// Fetch assets due within 14 days
try {
    $stmt = $pdo->prepare("
        SELECT id, asset_id, asset_name, location_id AS location,
               next_maintenance_date AS due_date, interval_days,
               checklist_id, maintenance_type, status, work_order
        FROM registered_assets
        WHERE org_id = :org_id
          AND next_maintenance_date <= CURRENT_DATE + INTERVAL '14 days'
        ORDER BY next_maintenance_date ASC
    ");
    $stmt->execute(['org_id' => $orgId]);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Fetch failed: " . $e->getMessage());
    $assets = [];
}

// Fetch latest checklist status for each asset
$checklistStatus = [];
foreach ($assets as $asset) {
    if (empty($asset['checklist_id'])) {
        $checklistStatus[$asset['asset_id']] = 'none';
        continue;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT status FROM maintenance_checklist
            WHERE org_id = :org_id
              AND asset_id = :asset_id
              AND checklist_id = :checklist_id
            ORDER BY date_started DESC
            LIMIT 1
        ");
        $stmt->execute([
            'org_id' => $orgId,
            'asset_id' => $asset['asset_id'],
            'checklist_id' => $asset['checklist_id']
        ]);
        $row = $stmt->fetch();
        $checklistStatus[$asset['asset_id']] = $row ? $row['status'] : 'none';
    } catch (Exception $e) {
        error_log("Check failed for {$asset['asset_id']}: " . $e->getMessage());
        $checklistStatus[$asset['asset_id']] = 'error';
    }
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upcoming Maintenance Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container-xxl { max-width: 75%; padding: 1rem; }
        .badge { padding: 0.35em 0.65em; border-radius: 0.25rem; font-size: 0.75em; }
        .overdue-row { background-color: #f8d7da !important; }
    </style>
</head>
<body>
<div class="container-xxl mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800">Upcoming Maintenance Dashboard</h3>
        <a href="/dashboard" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" id="success-alert">
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (alert) bootstrap.Alert.getOrCreateInstance(alert).close();
            }, 5000);
        </script>
    <?php endif; ?>

    <p class="text-muted">Tools with maintenance due within the next 2 weeks, including overdue items.</p>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Asset ID</th>
                    <th>Asset Name</th>
                    <th>Location</th>
                    <th>Due Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Work Order</th>
                    <th>Template</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($assets)): ?>
    <tr>
        <td colspan="9" class="text-center text-muted py-4">
            No maintenance due in the next 14 days.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($assets as $asset):
        try {
            $dueDate = new DateTime($asset['due_date']);
            $today = new DateTime('today'); // Start of today (00:00:00)
            $interval = $today->diff($dueDate);
            $days = (int)$interval->format('%r%a'); // signed: +5 or -3

            $isOverdue = $days < 0;
            $isToday = $days === 0;
            $hasTemplate = !empty($asset['checklist_id']);
            $status = $checklistStatus[$asset['asset_id']] ?? 'none';
            $assetName = htmlspecialchars($asset['asset_name'] ?? $asset['asset_id']);
        } catch (Exception $e) {
            // Fallback if date is invalid
            $dueDate = null;
            $days = 0;
            $isOverdue = false;
            $isToday = false;
            $hasTemplate = false;
            $status = 'error';
            $assetName = htmlspecialchars($asset['asset_id']);
        }
    ?>
    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
        <td><strong><?= htmlspecialchars($asset['asset_id']) ?></strong></td>
        <td><?= $assetName ?></td>
        <td><?= htmlspecialchars($asset['location'] ?? '—') ?></td>
        <td>
            <?= $dueDate ? htmlspecialchars($dueDate->format('M j, Y')) : 'Invalid date' ?>
        </td>
        <td><?= htmlspecialchars($asset['maintenance_type'] ?? 'Routine') ?></td>
        <td>
            <?php if ($status === 'in-progress'): ?>
                <span class="badge bg-warning text-dark">Ongoing</span>
            <?php elseif ($status === 'completed'): ?>
                <span class="badge bg-secondary">Completed</span>
            <?php elseif ($isToday): ?>
                <span class="badge bg-info text-dark">Today</span>
            <?php elseif ($isOverdue): ?>
                <span class="badge bg-danger"><?= abs($days) ?>d overdue</span>
            <?php elseif ($days > 0): ?>
                <span class="badge bg-success"><?= $days ?>d left</span>
            <?php else: ?>
                <span class="badge bg-light text-dark">—</span>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($asset['work_order'] ?? '—') ?></td>
        <td>
            <?php if ($hasTemplate): ?>
                <span class="text-primary fw-bold"><?= htmlspecialchars($asset['checklist_id']) ?></span>
            <?php else: ?>
                <span class="text-muted">None</span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($hasTemplate): ?>
                <?php if ($status === 'pending' || $status === 'in-progress'): ?>
                    <a href="/view_checklist.php?asset_id=<?= urlencode($asset['asset_id']) ?>&checklist_id=<?= urlencode($asset['checklist_id']) ?>"
                       class="btn btn-sm btn-primary">CHECKLIST</a>
                <?php elseif ($status === 'completed'): ?>
                    <a href="/view_checklist.php?asset_id=<?= urlencode($asset['asset_id']) ?>&checklist_id=<?= urlencode($asset['checklist_id']) ?>"
                       class="btn btn-sm btn-outline-secondary">VIEW</a>
                <?php else: ?>
                    <button type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#generateModal_<?= (int)$asset['id'] ?>">
                        GENERATE
                    </button>
                    <div class="modal fade" id="generateModal_<?= (int)$asset['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Generate Maintenance Checklist</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to generate a checklist for:</p>
                                    <ul class="list-group mb-3">
                                        <li class="list-group-item"><strong>Asset:</strong> <?= $assetName ?></li>
                                        <li class="list-group-item"><strong>ID:</strong> <?= htmlspecialchars($asset['asset_id']) ?></li>
                                        <li class="list-group-item"><strong>Checklist:</strong> <?= htmlspecialchars($asset['checklist_id']) ?></li>
                                    </ul>
                                    <div class="alert alert-info">
                                        This will create a new checklist instance and cannot be undone.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST"
                                          action="/handler/generateChecklistFromTemplate_handler.php"
                                          style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int)$asset['id'] ?>">
                                        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($asset['asset_id']) ?>">
                                        <input type="hidden" name="checklist_id" value="<?= htmlspecialchars($asset['checklist_id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="redirect" value="yes">
                                        <button type="submit" class="btn btn-primary">Yes, Generate</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-muted">—</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

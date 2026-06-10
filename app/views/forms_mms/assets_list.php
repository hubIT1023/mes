<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset List | Asset Management</title>
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
    
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/mes/mms_admin" class="link-underline-opacity-0">Home</a></li>
            <li class="breadcrumb-item active">Asset List</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0">Registered Assets</h3>
            <p class="text-muted small mb-0">View and manage registered physical assets for <?= htmlspecialchars($tenant['org_name'] ?? 'Your Organization') ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/mes/form_mms/addAsset" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Register Asset
            </a>
            <a href="/mes/mms_admin" class="btn btn-white border shadow-sm btn-sm px-3">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Search box -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-0 shadow-none ps-1" placeholder="Search by Asset ID, Name, Department, Serial or Status...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-middle mb-0 align-middle" id="assetTable">
                    <thead class="table-light border-0">
                        <tr>
                            <th class="ps-4 py-3 text-muted text-uppercase small" style="width: 130px;">Asset ID</th>
                            <th class="py-3 text-muted text-uppercase small">Asset Name</th>
                            <th class="py-3 text-muted text-uppercase small">Serial No</th>
                            <th class="py-3 text-muted text-uppercase small">Department</th>
                            <th class="py-3 text-muted text-uppercase small">Cost Center</th>
                            <th class="py-3 text-muted text-uppercase small">Location (Bldg/Floor/Line)</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 120px;">Status</th>
                            <th class="pe-4 py-3 text-muted text-uppercase small text-end" style="width: 150px;">Created</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php if (!empty($assets)): ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary">
                                        <?= htmlspecialchars($asset['asset_id']) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($asset['asset_name']) ?></div>
                                        <?php if (!empty($asset['equipment_description'])): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($asset['equipment_description']) ?>">
                                                <?= htmlspecialchars($asset['equipment_description']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-mono small">
                                        <?= htmlspecialchars($asset['serial_no'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($asset['department'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($asset['cost_center'] ?: '-') ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?php 
                                            $locs = array_filter([$asset['location_id_1'], $asset['location_id_2'], $asset['location_id_3']]);
                                            echo !empty($locs) ? htmlspecialchars(implode(' / ', $locs)) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = strtolower($asset['status'] ?? 'active');
                                            $badgeClass = 'bg-secondary';
                                            if ($status === 'active') {
                                                $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                            } elseif ($status === 'inactive') {
                                                $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                                            } elseif ($status === 'decommissioned') {
                                                $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            }
                                        ?>
                                        <span class="badge rounded-pill px-2.5 py-1.5 <?= $badgeClass ?> text-uppercase fw-semibold" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end text-muted small">
                                        <?= !empty($asset['created_at']) ? htmlspecialchars(date('M d, Y', strtotime($asset['created_at']))) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="mb-3 text-secondary opacity-25">
                                        <i class="bi bi-box-seam fa-5x" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="fw-bold">No Assets Registered</h5>
                                    <p class="small text-muted mb-3">Add machinery to the system to get started.</p>
                                    <a href="/mes/form_mms/addAsset" class="btn btn-primary btn-sm px-4">Register First Asset</a>
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
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#assetTable tbody tr');

    searchInput?.addEventListener('keyup', function (e) {
        const query = e.target.value.toLowerCase().trim();

        tableRows.forEach(row => {
            // Check if there is an empty state row
            if (row.cells.length === 1 && row.cells[0].colSpan === 8) return;

            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
});
</script>
</body>
</html>

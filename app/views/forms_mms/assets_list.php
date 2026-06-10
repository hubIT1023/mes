<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset List | Asset Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        .transition-all { transition: all 0.2s ease-in-out; }
        .table-middle td { vertical-align: middle; }
        .font-mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .btn-edit-group { white-space: nowrap; }
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

    <!-- Notification placeholder -->
    <div id="notificationContainer"></div>

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
                            <th class="py-3 text-muted text-uppercase small">Asset Name & Description</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 150px;">Serial No</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 130px;">Department</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 130px;">Cost Center</th>
                            <th class="py-3 text-muted text-uppercase small">Location (Bldg/Floor/Line)</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 150px;">Status</th>
                            <th class="py-3 text-muted text-uppercase small" style="width: 120px;">Created</th>
                            <th class="pe-4 py-3 text-muted text-uppercase small text-end" style="width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php if (!empty($assets)): ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr data-asset-id="<?= htmlspecialchars($asset['asset_id']) ?>"
                                    data-asset-name="<?= htmlspecialchars($asset['asset_name']) ?>"
                                    data-desc="<?= htmlspecialchars($asset['equipment_description'] ?? '') ?>"
                                    data-serial-no="<?= htmlspecialchars($asset['serial_no'] ?? '') ?>"
                                    data-department="<?= htmlspecialchars($asset['department'] ?? '') ?>"
                                    data-cost-center="<?= htmlspecialchars($asset['cost_center'] ?? '') ?>"
                                    data-location-1="<?= htmlspecialchars($asset['location_id_1'] ?? '') ?>"
                                    data-location-2="<?= htmlspecialchars($asset['location_id_2'] ?? '') ?>"
                                    data-location-3="<?= htmlspecialchars($asset['location_id_3'] ?? '') ?>"
                                    data-status="<?= htmlspecialchars($asset['status'] ?? 'active') ?>">
                                    
                                    <td class="ps-4 fw-semibold text-primary">
                                        <?= htmlspecialchars($asset['asset_id']) ?>
                                    </td>
                                    
                                    <td class="col-name">
                                        <div class="fw-bold text-dark name-text"><?= htmlspecialchars($asset['asset_name']) ?></div>
                                        <?php if (!empty($asset['equipment_description'])): ?>
                                            <small class="text-muted d-block text-truncate desc-text" style="max-width: 250px;" title="<?= htmlspecialchars($asset['equipment_description']) ?>">
                                                <?= htmlspecialchars($asset['equipment_description']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="col-serial font-mono small">
                                        <?= htmlspecialchars($asset['serial_no'] ?: '-') ?>
                                    </td>
                                    
                                    <td class="col-dept">
                                        <?= htmlspecialchars($asset['department'] ?: '-') ?>
                                    </td>
                                    
                                    <td class="col-cost">
                                        <?= htmlspecialchars($asset['cost_center'] ?: '-') ?>
                                    </td>
                                    
                                    <td class="col-loc small text-muted">
                                        <?php 
                                            $locs = array_filter([$asset['location_id_1'], $asset['location_id_2'], $asset['location_id_3']]);
                                            echo !empty($locs) ? htmlspecialchars(implode(' / ', $locs)) : '-';
                                        ?>
                                    </td>
                                    
                                    <td class="col-status">
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
                                    
                                    <td class="text-muted small col-created">
                                        <?= !empty($asset['created_at']) ? htmlspecialchars(date('M d, Y', strtotime($asset['created_at']))) : '-' ?>
                                    </td>

                                    <td class="pe-4 text-end col-actions">
                                        <!-- Edit button -->
                                        <button class="btn btn-sm btn-outline-secondary border-0 btn-edit" onclick="startEdit(this)" title="Edit Asset">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <!-- Save / Cancel button group -->
                                        <div class="d-none btn-edit-group">
                                            <button class="btn btn-sm btn-success border-0 btn-save me-1" onclick="saveEdit(this)" title="Save Changes">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger border-0 btn-cancel" onclick="cancelEdit(this)" title="Cancel">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="mb-3 text-secondary opacity-25">
                                        <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
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
            if (row.cells.length === 1 && row.cells[0].colSpan === 9) return;

            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
});

// Helper to escape HTML to prevent XSS in JS modifications
function escapeHtml(text) {
    if (!text) return '';
    return text
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Global alert triggers
function showNotification(type, message) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Start inline editing
function startEdit(btn) {
    const row = btn.closest('tr');
    
    // Disable all other edit buttons while this row is being edited
    document.querySelectorAll('.btn-edit').forEach(b => b.classList.add('disabled'));
    
    // Toggle action controls
    row.querySelector('.btn-edit').classList.add('d-none');
    row.querySelector('.btn-edit-group').classList.remove('d-none');
    
    // Replace text values with input components
    const name = row.dataset.assetName;
    const desc = row.dataset.desc;
    const serial = row.dataset.serialNo;
    const dept = row.dataset.department;
    const cost = row.dataset.costCenter;
    const loc1 = row.dataset.location1;
    const loc2 = row.dataset.location2;
    const loc3 = row.dataset.location3;
    const status = row.dataset.status;

    row.querySelector('.col-name').innerHTML = `
        <input type="text" class="form-control form-control-sm fw-bold edit-name" value="${escapeHtml(name)}" required placeholder="Asset Name">
        <textarea class="form-control form-control-sm edit-desc mt-1 small text-muted" rows="1" placeholder="Description">${escapeHtml(desc)}</textarea>
    `;
    
    row.querySelector('.col-serial').innerHTML = `
        <input type="text" class="form-control form-control-sm font-mono edit-serial" value="${escapeHtml(serial)}" placeholder="Serial No">
    `;
    
    row.querySelector('.col-dept').innerHTML = `
        <input type="text" class="form-control form-control-sm edit-dept" value="${escapeHtml(dept)}" placeholder="Department">
    `;
    
    row.querySelector('.col-cost').innerHTML = `
        <input type="text" class="form-control form-control-sm edit-cost" value="${escapeHtml(cost)}" placeholder="Cost Center">
    `;
    
    row.querySelector('.col-loc').innerHTML = `
        <input type="text" class="form-control form-control-sm edit-loc1" placeholder="Building" value="${escapeHtml(loc1)}">
        <input type="text" class="form-control form-control-sm edit-loc2 mt-1" placeholder="Floor" value="${escapeHtml(loc2)}">
        <input type="text" class="form-control form-control-sm edit-loc3 mt-1" placeholder="Line" value="${escapeHtml(loc3)}">
    `;
    
    row.querySelector('.col-status').innerHTML = `
        <select class="form-select form-select-sm edit-status">
            <option value="active" ${status.toLowerCase() === 'active' ? 'selected' : ''}>ACTIVE</option>
            <option value="inactive" ${status.toLowerCase() === 'inactive' ? 'selected' : ''}>INACTIVE</option>
            <option value="decommissioned" ${status.toLowerCase() === 'decommissioned' ? 'selected' : ''}>DECOMMISSIONED</option>
        </select>
    `;
}

// Cancel inline editing
function cancelEdit(btn) {
    const row = btn.closest('tr');
    
    // Restore all edit buttons
    document.querySelectorAll('.btn-edit').forEach(b => b.classList.remove('disabled'));
    
    // Toggle action controls
    row.querySelector('.btn-edit').classList.remove('d-none');
    row.querySelector('.btn-edit-group').classList.add('d-none');
    
    // Restore raw visual cells
    row.querySelector('.col-name').innerHTML = `
        <div class="fw-bold text-dark name-text">${escapeHtml(row.dataset.assetName)}</div>
        ${row.dataset.desc ? `<small class="text-muted d-block text-truncate desc-text" style="max-width: 250px;" title="${escapeHtml(row.dataset.desc)}">${escapeHtml(row.dataset.desc)}</small>` : ''}
    `;
    
    row.querySelector('.col-serial').innerText = row.dataset.serialNo || '-';
    row.querySelector('.col-dept').innerText = row.dataset.department || '-';
    row.querySelector('.col-cost').innerText = row.dataset.costCenter || '-';
    
    const locs = [row.dataset.location1, row.dataset.location2, row.dataset.location3].filter(Boolean);
    row.querySelector('.col-loc').innerText = locs.length > 0 ? locs.join(' / ') : '-';
    
    const status = row.dataset.status.toLowerCase();
    let badgeClass = 'bg-secondary';
    if (status === 'active') {
        badgeClass = 'bg-success-subtle text-success border border-success-subtle';
    } else if (status === 'inactive') {
        badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
    } else if (status === 'decommissioned') {
        badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
    }
    
    row.querySelector('.col-status').innerHTML = `
        <span class="badge rounded-pill px-2.5 py-1.5 ${badgeClass} text-uppercase fw-semibold" style="font-size: 0.7rem;">
            ${escapeHtml(status)}
        </span>
    `;
}

// Save inline edits via AJAX
function saveEdit(btn) {
    const row = btn.closest('tr');
    
    const assetId = row.dataset.assetId;
    const name = row.querySelector('.edit-name').value.trim();
    const desc = row.querySelector('.edit-desc').value.trim();
    const serial = row.querySelector('.edit-serial').value.trim();
    const dept = row.querySelector('.edit-dept').value.trim();
    const cost = row.querySelector('.edit-cost').value.trim();
    const loc1 = row.querySelector('.edit-loc1').value.trim();
    const loc2 = row.querySelector('.edit-loc2').value.trim();
    const loc3 = row.querySelector('.edit-loc3').value.trim();
    const status = row.querySelector('.edit-status').value;

    if (!name) {
        alert("Asset Name is required.");
        return;
    }

    // Disable all inputs on the current row to prevent double-submit
    const inputs = row.querySelectorAll('input, select, textarea, button');
    inputs.forEach(i => i.disabled = true);

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    fetch('/mes/assets/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            asset_id: assetId,
            asset_name: name,
            equipment_description: desc,
            serial_no: serial,
            department: dept,
            cost_center: cost,
            location_id_1: loc1,
            location_id_2: loc2,
            location_id_3: loc3,
            status: status,
            csrf_token: csrfToken
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || 'Server error') });
        }
        return response.json();
    })
    .then(data => {
        // Update dataset values
        row.dataset.assetName = name;
        row.dataset.desc = desc;
        row.dataset.serialNo = serial;
        row.dataset.department = dept;
        row.dataset.costCenter = cost;
        row.dataset.location1 = loc1;
        row.dataset.location2 = loc2;
        row.dataset.location3 = loc3;
        row.dataset.status = status;

        showNotification('success', data.message || 'Asset updated successfully!');
        
        // Re-enable row and exit edit mode
        inputs.forEach(i => i.disabled = false);
        cancelEdit(btn);
    })
    .catch(error => {
        showNotification('danger', error.message || 'An unexpected error occurred.');
        // Re-enable row for correcting details
        inputs.forEach(i => i.disabled = false);
    });
}
</script>
</body>
</html>

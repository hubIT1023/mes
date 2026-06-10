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

    <!-- Notification placeholder -->
    <div id="notificationContainer"></div>

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
                            <th class="pe-4 py-3 text-muted text-uppercase small text-end" style="width: 110px;">Actions</th>
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
                            <?php 
                            // Convert tasks to a JSON string for easy access in JS
                            $tasksJson = json_encode(array_map(function($task) {
                                return [
                                    'task_id' => $task['task_id'],
                                    'task_order' => $task['task_order'],
                                    'task_text' => $task['task_text']
                                ];
                            }, array_filter($rows, function($r) { return !empty($r['task_text']); })));
                            ?>
                            <tr class="checklist-row"
                                data-checklist-id="<?= htmlspecialchars($checklistId) ?>"
                                data-maintenance-type="<?= htmlspecialchars($first['maintenance_type'] ?? '') ?>"
                                data-work-order="<?= htmlspecialchars($first['work_order'] ?? '') ?>"
                                data-interval-days="<?= htmlspecialchars($first['interval_days'] ?? '30') ?>"
                                data-description="<?= htmlspecialchars($first['description'] ?? '') ?>"
                                data-tasks="<?= htmlspecialchars($tasksJson, ENT_QUOTES, 'UTF-8') ?>">
                                
                                <td class="ps-4 fw-semibold text-primary col-checklist-id">
                                    <?= htmlspecialchars($checklistId) ?>
                                </td>
                                <td class="col-maint-type">
                                    <span class="badge rounded-pill px-2.5 py-1.5 <?= $badgeClass ?> text-uppercase fw-semibold" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($first['maintenance_type'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="font-mono small text-dark col-work-order">
                                    <?= htmlspecialchars($first['work_order'] ?? '-') ?>
                                </td>
                                <td class="small col-interval">
                                    <?= htmlspecialchars($first['interval_days'] ?? '30') ?> days
                                </td>
                                <td class="small text-muted col-description">
                                    <?= htmlspecialchars($first['description'] ?: '-') ?>
                                </td>
                                <td class="col-tasks">
                                    <ol class="mb-0 ps-3 small text-muted">
                                        <?php foreach ($rows as $task): ?>
                                            <?php if (!empty($task['task_text'])): ?>
                                                <li><?= htmlspecialchars($task['task_text']) ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ol>
                                </td>
                                <td class="pe-4 text-end col-actions">
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-outline-secondary border-0 btn-edit" onclick="startEdit(this)" title="Edit Template">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    <!-- Save / Cancel Buttons -->
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
const availableMaintenanceTypes = <?= json_encode($maintenanceTypes ?: ['PM', 'CM', 'Inspection']) ?>;

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
            // Skip check if row is currently in edit mode to avoid searching raw input markup
            const editBtn = row.querySelector('.btn-edit');
            if (editBtn && editBtn.classList.contains('d-none')) {
                // Keep it visible if it's currently editing
                row.style.display = '';
                visibleCount++;
                return;
            }
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

// Badge color logic based on type
function getBadgeClass(type) {
    const lower = (type || '').toLowerCase();
    if (lower === 'pm') {
        return 'bg-success-subtle text-success border border-success-subtle';
    } else if (lower === 'cm') {
        return 'bg-danger-subtle text-danger border border-danger-subtle';
    } else if (lower === 'inspection') {
        return 'bg-info-subtle text-info border border-info-subtle';
    }
    return 'bg-secondary text-white';
}

// Start inline editing
function startEdit(btn) {
    const row = btn.closest('tr');
    
    // Disable all other edit buttons while this row is being edited
    document.querySelectorAll('.btn-edit').forEach(b => b.classList.add('disabled'));
    
    // Toggle action controls
    row.querySelector('.btn-edit').classList.add('d-none');
    row.querySelector('.btn-edit-group').classList.remove('d-none');
    
    // Retrieve dataset
    const maintType = row.dataset.maintenanceType;
    const workOrder = row.dataset.workOrder;
    const intervalDays = row.dataset.intervalDays;
    const description = row.dataset.description;
    const tasks = JSON.parse(row.dataset.tasks || '[]');

    // 1. Maintenance Type Column
    let optionsHtml = '';
    availableMaintenanceTypes.forEach(type => {
        optionsHtml += `<option value="${escapeHtml(type)}" ${type.toLowerCase() === maintType.toLowerCase() ? 'selected' : ''}>${escapeHtml(type)}</option>`;
    });
    // If current type is not in list, add it
    if (maintType && !availableMaintenanceTypes.some(t => t.toLowerCase() === maintType.toLowerCase())) {
        optionsHtml += `<option value="${escapeHtml(maintType)}" selected>${escapeHtml(maintType)}</option>`;
    }
    row.querySelector('.col-maint-type').innerHTML = `
        <select class="form-select form-select-sm edit-maint-type" style="min-width: 120px;">
            ${optionsHtml}
        </select>
    `;

    // 2. Work Order Code Column
    row.querySelector('.col-work-order').innerHTML = `
        <input type="text" class="form-control form-control-sm edit-work-order" value="${escapeHtml(workOrder)}" placeholder="Work Order" style="min-width: 100px;">
    `;

    // 3. Interval Column
    row.querySelector('.col-interval').innerHTML = `
        <div class="input-group input-group-sm" style="min-width: 90px;">
            <input type="number" class="form-control edit-interval" value="${escapeHtml(intervalDays)}" min="1" required>
        </div>
    `;

    // 4. Description Column
    row.querySelector('.col-description').innerHTML = `
        <textarea class="form-control form-control-sm edit-description" rows="2" placeholder="Description" style="min-width: 150px;">${escapeHtml(description)}</textarea>
    `;

    // 5. Tasks Column
    let tasksHtml = '<div class="edit-tasks-list d-flex flex-column gap-2" style="min-width: 250px;">';
    tasks.forEach((task, index) => {
        tasksHtml += `
            <div class="task-input-row d-flex align-items-center gap-1">
                <input type="hidden" class="task-id" value="${task.task_id}">
                <input type="hidden" class="task-order" value="${task.task_order}">
                <span class="text-muted small task-order-num" style="min-width: 18px;">${index + 1}.</span>
                <input type="text" class="form-control form-control-sm task-text" value="${escapeHtml(task.task_text)}" placeholder="Step description" required>
                <button type="button" class="btn btn-sm text-danger p-0 border-0 btn-delete-step" onclick="removeTaskRow(this)" title="Remove Step">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    });
    tasksHtml += `</div>
    <button type="button" class="btn btn-link btn-sm p-0 text-success mt-2 text-decoration-none" onclick="addTaskRow(this)">
        <i class="bi bi-plus-circle me-1"></i> Add Step
    </button>`;
    
    row.querySelector('.col-tasks').innerHTML = tasksHtml;
}

// Cancel inline editing
function cancelEdit(btn) {
    const row = btn.closest('tr');
    
    // Restore all edit buttons
    document.querySelectorAll('.btn-edit').forEach(b => b.classList.remove('disabled'));
    
    // Toggle action controls
    row.querySelector('.btn-edit').classList.remove('d-none');
    row.querySelector('.btn-edit-group').classList.add('d-none');
    
    // Restore raw visual cells from row datasets
    const maintType = row.dataset.maintenanceType;
    const workOrder = row.dataset.workOrder;
    const intervalDays = row.dataset.intervalDays;
    const description = row.dataset.description;
    const tasks = JSON.parse(row.dataset.tasks || '[]');

    // Badge color logic
    const badgeClass = getBadgeClass(maintType);

    row.querySelector('.col-maint-type').innerHTML = `
        <span class="badge rounded-pill px-2.5 py-1.5 ${badgeClass} text-uppercase fw-semibold" style="font-size: 0.7rem;">
            ${escapeHtml(maintType)}
        </span>
    `;

    row.querySelector('.col-work-order').innerHTML = `
        <span class="font-mono small text-dark">${escapeHtml(workOrder || '-')}</span>
    `;

    row.querySelector('.col-interval').innerHTML = `
        <span class="small">${escapeHtml(intervalDays || '30')} days</span>
    `;

    row.querySelector('.col-description').innerHTML = `
        <span class="small text-muted">${escapeHtml(description || '-')}</span>
    `;

    let tasksListHtml = '<ol class="mb-0 ps-3 small text-muted">';
    tasks.forEach(task => {
        if (task.task_text) {
            tasksListHtml += `<li>${escapeHtml(task.task_text)}</li>`;
        }
    });
    tasksListHtml += '</ol>';
    row.querySelector('.col-tasks').innerHTML = tasksListHtml;
}

function addTaskRow(btn) {
    const colTasks = btn.closest('.col-tasks');
    const container = colTasks.querySelector('.edit-tasks-list');
    
    const newRow = document.createElement('div');
    newRow.className = 'task-input-row d-flex align-items-center gap-1';
    newRow.innerHTML = `
        <input type="hidden" class="task-id" value="">
        <input type="hidden" class="task-order" value="">
        <span class="text-muted small task-order-num" style="min-width: 18px;"></span>
        <input type="text" class="form-control form-control-sm task-text" value="" placeholder="Step description" required>
        <button type="button" class="btn btn-sm text-danger p-0 border-0 btn-delete-step" onclick="removeTaskRow(this)" title="Remove Step">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(newRow);
    reindexTaskOrders(container);
}

function removeTaskRow(btn) {
    const row = btn.closest('.task-input-row');
    const container = row.closest('.edit-tasks-list');
    const taskId = row.querySelector('.task-id').value;
    
    if (taskId) {
        // Existing task, clear text and hide (so backend deletes it)
        row.querySelector('.task-text').value = '';
        row.style.display = 'none';
    } else {
        // New task, safe to remove from DOM
        row.remove();
    }
    reindexTaskOrders(container);
}

function reindexTaskOrders(container) {
    let index = 1;
    container.querySelectorAll('.task-input-row').forEach(row => {
        if (row.style.display === 'none') return;
        row.querySelector('.task-order-num').textContent = index + '.';
        row.querySelector('.task-order').value = index;
        index++;
    });
}

// Save inline edits via AJAX
function saveEdit(btn) {
    const row = btn.closest('tr');
    
    const checklistId = row.dataset.checklistId;
    const maintType = row.querySelector('.edit-maint-type').value;
    const workOrder = row.querySelector('.edit-work-order').value.trim();
    const interval = row.querySelector('.edit-interval').value;
    const description = row.querySelector('.edit-description').value.trim();

    // Collect tasks
    const taskIds = [];
    const taskTexts = [];
    const taskOrders = [];

    const taskRows = row.querySelectorAll('.task-input-row');
    let hasAtLeastOneTask = false;

    taskRows.forEach(tRow => {
        const id = tRow.querySelector('.task-id').value;
        const text = tRow.querySelector('.task-text').value.trim();
        const order = tRow.querySelector('.task-order').value;

        taskIds.push(id);
        taskTexts.push(text);
        taskOrders.push(order);

        if (text !== '') {
            hasAtLeastOneTask = true;
        }
    });

    if (!hasAtLeastOneTask) {
        alert("At least one active task is required.");
        return;
    }

    if (!interval || parseInt(interval) < 1) {
        alert("Interval (days) must be a positive number.");
        return;
    }

    // Disable all inputs on the current row to prevent double-submit
    const inputs = row.querySelectorAll('input, select, textarea, button');
    inputs.forEach(i => i.disabled = true);

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    fetch('/mes/form_mms/checklist_update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            checklist_id: checklistId,
            maintenance_type: maintType,
            work_order: workOrder,
            interval_days: interval,
            description: description,
            task_id: taskIds,
            task_text: taskTexts,
            task_order: taskOrders,
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
        row.dataset.maintenanceType = maintType;
        row.dataset.workOrder = workOrder;
        row.dataset.intervalDays = interval;
        row.dataset.description = description;
        if (data.tasks) {
            row.dataset.tasks = JSON.stringify(data.tasks);
        }

        showNotification('success', data.message || 'Checklist updated successfully!');
        
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
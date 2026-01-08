<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
  .empty-state-link {
    text-decoration: none !important;
    transition: all 0.3s ease;
    display: inline-block;
    
    /* The dashed border styling */
    border: 2px dashed #dee2e6; /* Light gray border */
    padding: 40px 60px;         /* Space between text and border */
    border-radius: 12px;        /* Optional: slightly rounded corners */
    background-color: #f8f9fa;  /* Light background to make it pop */
}

.empty-state-link:hover {
    transform: translateY(-5px);
    border-color: #0d6efd;      /* Changes to blue on hover */
    background-color: #ffffff;  /* Brightens on hover */
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.empty-state-link:hover i {
    color: #0d6efd !important;
}

    .empty-state-link {
        text-decoration: none !important;
        transition: all 0.3s ease-in-out;
        display: inline-block;
        
        /* Dashed Border Styling */
        border: 2px dashed #ced4da; /* Subtle gray dash */
        padding: 3rem;              /* Large internal spacing */
        border-radius: 15px;        /* Smooth corners */
        background-color: #f8f9fa;  /* Very light gray background */
        max-width: 450px;           /* Prevents it from getting too wide */
    }

    .empty-state-link:hover {
        transform: translateY(-5px);
        border-color: #0d6efd;      /* Primary blue on hover */
        background-color: #ffffff;  /* Cleans up to white on hover */
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .empty-state-link:hover i {
        color: #0d6efd !important;  /* Icon turns blue */
    }

    .empty-state-link:hover h4 {
        color: #0d6efd !important;  /* Text turns blue */
    }

    /* Consistent Header Styling */
    .page-header {
        background-color: #6c757d; /* bg-secondary */
        padding: 1.5rem;
        border-radius: 0.75rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }

    /* Dashed Registration Box */
    .empty-state-link {
        text-decoration: none !important;
        transition: all 0.3s ease;
        display: inline-block;
        border: 3px dashed #dee2e6;
        padding: 4rem 2rem;
        border-radius: 1.5rem;
        background-color: #f8f9fa;
        max-width: 500px;
        width: 100%;
    }

    .empty-state-link:hover {
        border-color: #0d6efd;
        background-color: #fff;
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.1);
    }

    .empty-state-link:hover i {
        color: #0d6efd !important;
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }
</style>

<div class="container-lg mt-4">
  
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <nav class="page-header shadow-sm">
        <div>
            <h2 class="fw-bold mb-1">Registered Devices</h2>
            <p class="text-white-50 mb-0">Manage your connected devices</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/hub_portal" class="btn btn-light border">
                <i class="fas fa-desktop me-1"></i> Hub Portal
            </a>
            <?php if (!empty($devices)): ?>
                <a href="/device/register" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Register New Device
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <?php if (empty($devices)): ?>
        <div class="d-flex align-items-center justify-content-center mt-5" style="min-height: 40vh;">
            <a href="/device/register" class="empty-state-link text-center">
                <div class="mb-4">
                    <i class="fas fa-microchip fa-4x text-muted"></i>
                </div>
                <h4 class="text-dark fw-bold">No devices registered yet</h4>
                <p class="text-muted mb-4">Set up your first device to start monitoring parameters.</p>
                <span class="btn btn-outline-primary px-4">
                    <i class="fas fa-plus me-1"></i> Register Now
                </span>
            </a>
        </div>
    
	<!-- existing code -->
	
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Device Name</th>
                                <th>Description</th>
                                <th>Parameters</th>
                                <th class="text-end">Hi Limit</th>
                                <th class="text-end">Lo Limit</th>
                                <th>Trigger</th>
                                <th>Action</th>
                                <th class="text-center">Key</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($device['device_name']) ?></strong>
                                        <div class="small text-muted mt-1">
                                            <?php if (!empty($device['location_level_1'])): ?>
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?= htmlspecialchars($device['location_level_1']) ?>
                                                <?php if (!empty($device['location_level_2'])): ?> / <?= htmlspecialchars($device['location_level_2']) ?><?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="editable-cell" data-device-id="<?= (int)$device['id'] ?>" data-field="description" data-value="<?= htmlspecialchars($device['description'] ?? '') ?>">
                                        <span class="cell-text"><?= !empty($device['description']) ? htmlspecialchars($device['description']) : '<span class="text-muted">—</span>' ?></span>
                                        <input type="text" class="form-control d-none cell-input" value="<?= htmlspecialchars($device['description'] ?? '') ?>">
                                    </td>

                                    <td>
                                        <?php if (!empty($device['parameter_name'])): ?>
                                            <code><?= htmlspecialchars($device['parameter_name']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end editable-cell" data-device-id="<?= (int)$device['id'] ?>" data-field="hi_limit" data-value="<?= $device['hi_limit'] ?>">
                                        <span class="cell-text"><?= $device['hi_limit'] !== null ? number_format((float)$device['hi_limit'], 2) : '<span class="text-muted">—</span>' ?></span>
                                        <input type="number" step="any" class="form-control d-none cell-input text-end" value="<?= $device['hi_limit'] ?? '' ?>">
                                    </td>

                                    <td class="text-end editable-cell" data-device-id="<?= (int)$device['id'] ?>" data-field="lo_limit" data-value="<?= $device['lo_limit'] ?>">
                                        <span class="cell-text"><?= $device['lo_limit'] !== null ? number_format((float)$device['lo_limit'], 2) : '<span class="text-muted">—</span>' ?></span>
                                        <input type="number" step="any" class="form-control d-none cell-input text-end" value="<?= $device['lo_limit'] ?? '' ?>">
                                    </td>

                                    <td class="editable-cell" data-device-id="<?= (int)$device['id'] ?>" data-field="trigger_condition" data-value="<?= htmlspecialchars($device['trigger_condition'] ?? '') ?>">
                                        <span class="cell-text"><?= !empty($device['trigger_condition']) ? htmlspecialchars($device['trigger_condition']) : '<span class="text-muted">—</span>' ?></span>
                                        <input type="text" class="form-control d-none cell-input" value="<?= htmlspecialchars($device['trigger_condition'] ?? '') ?>">
                                    </td>

                                    <td class="editable-cell" data-device-id="<?= (int)$device['id'] ?>" data-field="action" data-value="<?= htmlspecialchars($device['action'] ?? '') ?>">
                                        <span class="cell-text"><?= !empty($device['action']) ? htmlspecialchars($device['action']) : '<span class="text-muted">—</span>' ?></span>
                                        <input type="text" class="form-control d-none cell-input" value="<?= htmlspecialchars($device['action'] ?? '') ?>">
                                    </td>

                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-secondary copy-key" data-key="<?= htmlspecialchars($device['device_key']) ?>" title="Copy Key">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.copy-key').forEach(btn => {
    btn.addEventListener('click', function() {
        const key = this.dataset.key;
        navigator.clipboard.writeText(key).then(() => {
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                this.innerHTML = original;
            }, 2000);
        }).catch(() => {
            alert('Failed to copy device key');
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Make cells editable on click
    document.querySelectorAll('.editable-cell').forEach(cell => {
        cell.addEventListener('click', function(e) {
            if (e.target.closest('input, .btn')) return;
            startEdit(cell);
        });
    });

    // Handle Enter = save, Esc = cancel
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.editing').forEach(cell => cancelEdit(cell));
        } else if (e.key === 'Enter') {
            const editingCell = document.querySelector('.editing');
            if (editingCell) {
                e.preventDefault();
                saveEdit(editingCell);
            }
        }
    });

    function startEdit(cell) {
        cell.classList.add('editing', 'position-relative');
        cell.querySelector('.cell-text').classList.add('d-none');
        const input = cell.querySelector('.cell-input');
        input.classList.remove('d-none');
        input.focus();
        input.select();
    }

    function cancelEdit(cell) {
        const input = cell.querySelector('.cell-input');
        const originalValue = cell.dataset.value;
        
        // Restore original display
        if (cell.dataset.field === 'hi_limit' || cell.dataset.field === 'lo_limit') {
            cell.querySelector('.cell-text').innerHTML = 
                originalValue !== '' && originalValue !== null ? 
                parseFloat(originalValue).toFixed(2) : '<span class="text-muted">—</span>';
        } else {
            cell.querySelector('.cell-text').textContent = originalValue || '';
            if (!originalValue) cell.querySelector('.cell-text').innerHTML = '<span class="text-muted">—</span>';
        }
        
        input.value = originalValue;
        cell.classList.remove('editing');
        cell.querySelector('.cell-text').classList.remove('d-none');
        input.classList.add('d-none');
    }

    async function saveEdit(cell) {
        const deviceId = cell.dataset.deviceId;
        const field = cell.dataset.field;
        const input = cell.querySelector('.cell-input');
        let newValue = input.value.trim();

        // Handle numeric fields
        if (field === 'hi_limit' || field === 'lo_limit') {
            newValue = newValue === '' ? null : parseFloat(newValue);
            if (isNaN(newValue) && newValue !== null) {
                alert('Please enter a valid number');
                return;
            }
        }

        // No change? Cancel
        const oldValue = cell.dataset.value;
        if ((newValue === null && oldValue === '') || newValue == oldValue) {
            cancelEdit(cell);
            return;
        }

        try {
            const res = await fetch('/device/update-field', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: deviceId,
                    field: field,
                    value: newValue,
                    csrf_token: '<?= $_SESSION['csrf_token'] ?? "" ?>'
                })
            });

            if (res.ok) {
                // Update display
                cell.dataset.value = newValue ?? '';
                if (field === 'hi_limit' || field === 'lo_limit') {
                    cell.querySelector('.cell-text').innerHTML = 
                        newValue !== null ? parseFloat(newValue).toFixed(2) : '<span class="text-muted">—</span>';
                } else {
                    cell.querySelector('.cell-text').textContent = newValue || '';
                    if (!newValue) cell.querySelector('.cell-text').innerHTML = '<span class="text-muted">—</span>';
                }
                cell.classList.remove('editing');
                cell.querySelector('.cell-text').classList.remove('d-none');
                input.classList.add('d-none');
            } else {
                const err = await res.json().catch(() => ({}));
                alert('Save failed: ' + (err.message || 'Unknown error'));
                cancelEdit(cell);
            }
        } catch (e) {
            alert('Network error');
            cancelEdit(cell);
        }
    }

    // Save on blur (optional)
    document.querySelectorAll('.cell-input').forEach(input => {
        input.addEventListener('blur', function() {
            const cell = this.closest('.editable-cell');
            if (cell.classList.contains('editing')) {
                saveEdit(cell);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
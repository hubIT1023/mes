<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container-lg mt-4 mb-5">
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <nav class="d-flex justify-content-between align-items-center bg-secondary text-white p-4 rounded-3 shadow-sm mb-4">
        <div>
            <h2 class="fw-bold mb-1">Registered Devices</h2>
            <p class="text-white-50 mb-0 small">Manage your connected hardware assets</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/hub_portal" class="btn btn-light btn-sm px-3 fw-medium">
                <i class="fas fa-desktop me-1"></i> Hub Portal
            </a>
            <?php if (!empty($devices)): ?>
                <a href="/device/register" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i> Register New
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <?php if (empty($devices)): ?>
        <div class="d-flex align-items-center justify-content-center mt-5" style="min-height: 50vh;">
            <a href="/device/register" 
               class="text-center text-decoration-none border border-3 border-dashed bg-body-tertiary p-5 rounded-5" 
               style="max-width: 500px; transition: all 0.2s ease-in-out;">
                
                <div class="mb-4 text-secondary opacity-50">
                    <i class="fas fa-microchip fa-4x"></i>
                </div>
                
                <h4 class="text-dark fw-bold">No devices registered yet</h4>
                <p class="text-muted mb-4">Set up your first device to start monitoring parameters in real-time.</p>
                
                <span class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i> Register First Device
                </span>
            </a>
        </div>

    <?php else: ?>
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-uppercase text-muted">
                                <th class="ps-4">Device Name</th>
                                <th>Description</th>
                                <th>Parameters</th>
                                <th class="text-end">Hi Limit</th>
                                <th class="text-end">Lo Limit</th>
                                <th>Trigger</th>
                                <th>Action</th>
                                <th class="text-center pe-4">Key</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($device['device_name']) ?></div>
                                        <?php if (!empty($device['location_level_1'])): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-map-marker-alt me-1 text-primary shadow-sm"></i>
                                                <?= htmlspecialchars($device['location_level_1']) ?>
                                                <?= !empty($device['location_level_2']) ? ' / ' . htmlspecialchars($device['location_level_2']) : '' ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-muted small">
                                        <?= !empty($device['description']) ? htmlspecialchars($device['description']) : '—' ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($device['parameter_name'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium">
                                                <?= htmlspecialchars($device['parameter_name']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end font-monospace"><?= $device['hi_limit'] ?? '—' ?></td>
                                    <td class="text-end font-monospace"><?= $device['lo_limit'] ?? '—' ?></td>
                                    
                                    <td><span class="small"><?= htmlspecialchars($device['trigger_condition'] ?? '—') ?></span></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($device['action'] ?? '—') ?></span></td>

                                    <td class="text-center pe-4">
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm copy-key" 
                                                data-key="<?= htmlspecialchars($device['device_key']) ?>" 
                                                title="Copy Key">
                                            <i class="fas fa-key small"></i>
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
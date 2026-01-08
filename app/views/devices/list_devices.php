<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container-lg mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Registered Devices</h2>
            <p class="text-muted mb-0">Manage your connected devices</p>
        </div>
        <a href="/device/register" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Register New Device
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($devices)): ?>
        <div class="text-center py-5">
            <i class="fas fa-microchip fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No devices registered yet</h5>
            <p class="text-muted">Click "Register New Device" to get started.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($devices as $device): ?>
                <div class="col-12">
                    <div class="card shadow-sm border-left-primary">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($device['device_name']) ?></h5>
                                    <p class="text-muted small mb-2">
                                        <span class="badge bg-light text-dark border me-2">Key: <?= substr($device['device_key'], 0, 8) ?>…</span>
                                        <?php if (!empty($device['parameter_name'])): ?>
                                            <span>Parameter: <?= htmlspecialchars($device['parameter_name']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <div class="small text-muted">
                                        <?php if ($device['location_level_1']): ?>
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($device['location_level_1']) ?>
                                            <?php if ($device['location_level_2']): ?> / <?= htmlspecialchars($device['location_level_2']) ?><?php endif; ?>
                                            <?php if ($device['location_level_3']): ?> / <?= htmlspecialchars($device['location_level_3']) ?><?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted mb-1">Registered</div>
                                    <div class="small"><?= date('M j, Y', strtotime($device['created_at'])) ?></div>
                                    <button 
                                        class="btn btn-sm btn-outline-secondary mt-2 copy-key" 
                                        data-key="<?= htmlspecialchars($device['device_key']) ?>"
                                        title="Copy full device key"
                                    >
                                        <i class="fas fa-key me-1"></i> Show Full Key
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



<div class="container-lg mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Registered Devices</h2>
            <p class="text-muted mb-0">Manage your connected devices</p>
        </div>
        <a href="/device/register" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Register New Device
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($devices)): ?>
        <div class="text-center py-5">
            <i class="fas fa-microchip fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No devices registered yet</h5>
            <p class="text-muted">Click "Register New Device" to get started.</p>
        </div>
    <?php else: ?>
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
                                <th>Trigger Condition</th>
                                <th>Action</th>
                                <th class="text-center">Key</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                                <tr>
                                    <!-- Device Name -->
                                    <td>
                                        <strong><?= htmlspecialchars($device['device_name']) ?></strong>
                                        <div class="small text-muted mt-1">
                                            <?php if (!empty($device['location_level_1'])): ?>
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?= htmlspecialchars($device['location_level_1']) ?>
                                                <?php if (!empty($device['location_level_2'])): ?> / <?= htmlspecialchars($device['location_level_2']) ?><?php endif; ?>
                                                <?php if (!empty($device['location_level_3'])): ?> / <?= htmlspecialchars($device['location_level_3']) ?><?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td>
                                        <?= !empty($device['description']) ? htmlspecialchars($device['description']) : '<span class="text-muted">—</span>' ?>
                                    </td>

                                    <!-- Parameters -->
                                    <td>
                                        <?php if (!empty($device['parameter_name'])): ?>
                                            <code><?= htmlspecialchars($device['parameter_name']) ?></code>
                                            <?php if (!empty($device['parameter_value'])): ?>
                                                = <?= htmlspecialchars($device['parameter_value']) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Hi Limit -->
                                    <td class="text-end">
                                        <?= $device['hi_limit'] !== null ? number_format((float)$device['hi_limit'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>

                                    <!-- Lo Limit -->
                                    <td class="text-end">
                                        <?= $device['lo_limit'] !== null ? number_format((float)$device['lo_limit'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>

                                    <!-- Trigger Condition -->
                                    <td>
                                        <?= !empty($device['trigger_condition']) ? htmlspecialchars($device['trigger_condition']) : '<span class="text-muted">—</span>' ?>
                                    </td>

                                    <!-- Action -->
                                    <td>
                                        <?= !empty($device['action']) ? htmlspecialchars($device['action']) : '<span class="text-muted">—</span>' ?>
                                    </td>

                                    <!-- Device Key (with copy) -->
                                    <td class="text-center">
                                        <button 
                                            class="btn btn-sm btn-outline-secondary copy-key"
                                            data-key="<?= htmlspecialchars($device['device_key']) ?>"
                                            title="Copy device key"
                                        >
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
            const originalIcon = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                this.innerHTML = originalIcon;
            }, 2000);
        }).catch(err => {
            alert('Failed to copy key');
        });
    });
});

document.querySelectorAll('.copy-key').forEach(btn => {
    btn.addEventListener('click', function() {
        const key = this.dataset.key;
        navigator.clipboard.writeText(key).then(() => {
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
            setTimeout(() => this.innerHTML = original, 2000);
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
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

<script>
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
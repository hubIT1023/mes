<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container-sm mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Register New Device</h2>
                    <p class="text-muted">Connect a new hardware asset to your hub.</p>
                </div>
                <a href="/device" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Devices
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="/device/register">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-4">
                            <h6 class="text-primary fw-bold text-uppercase small mb-3 border-bottom pb-2">
                                <i class="fas fa-info-circle me-1"></i> Basic Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Device Name <span class="text-danger">*</span></label>
                                    <input type="text" name="device_name" class="form-control form-control-lg" required maxlength="255" placeholder="e.g., Main Water Pump">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2" maxlength="500" placeholder="Describe the purpose of this device..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-primary fw-bold text-uppercase small mb-3 border-bottom pb-2">
                                <i class="fas fa-sliders-h me-1"></i> Parameter & Thresholds
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Parameter Name</label>
                                    <input type="text" name="parameter_name" class="form-control" maxlength="100" placeholder="e.g., Temperature (°C)">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hi Limit</label>
                                    <input type="number" step="any" name="hi_limit" class="form-control" placeholder="Max">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Lo Limit</label>
                                    <input type="number" step="any" name="lo_limit" class="form-control" placeholder="Min">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Trigger Condition</label>
                                    <input type="text" name="trigger_condition" class="form-control" placeholder="e.g., value > hi_limit">
                                    <div class="form-text">Logic used to fire an action.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Action</label>
                                    <input type="text" name="action" class="form-control" placeholder="e.g., Send Email Alert">
                                    <div class="form-text">What happens when trigger is met?</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-primary fw-bold text-uppercase small mb-3 border-bottom pb-2">
                                <i class="fas fa-map-marker-alt me-1"></i> Physical Location
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Building / Plant</label>
                                    <input type="text" name="location_level_1" class="form-control" placeholder="Level 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Floor / Area</label>
                                    <input type="text" name="location_level_2" class="form-control" placeholder="Level 2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Specific Room / Spot</label>
                                    <input type="text" name="location_level_3" class="form-control" placeholder="Level 3">
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 d-flex justify-content-end gap-2">
                            <a href="/device" class="btn btn-light px-4 border">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Register Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
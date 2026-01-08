<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container-sm mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7 col-xl-6">
            
            <nav class="d-flex justify-content-between align-items-center bg-body-secondary border p-3 rounded-3 shadow-sm mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Register Device</h4>
                    <p class="text-muted small mb-0">New hardware setup</p>
                </div>
                <a href="/device" class="btn btn-outline-secondary btn-sm px-3 border-0">
                    <i class="fas fa-times"></i>
                </a>
            </nav>
            
            <div class="card border-0 shadow-sm overflow-hidden rounded-4">
                <div class="card-body p-0">
                    <form method="POST" action="/device/register" class="bg-body-white p-4 p-md-5">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <hr class="flex-grow-1 border-secondary-subtle">
                                <span class="mx-2 text-uppercase fw-bold text-secondary small" style="font-size: 0.65rem; letter-spacing: 1px;">Identity</span>
                                <hr class="flex-grow-1 border-secondary-subtle">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Device Name *</label>
                                <input type="text" name="device_name" class="form-control bg-white border-light-subtle shadow-sm" required placeholder="e.g. Pump 01">
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold small">Description</label>
                                <textarea name="description" class="form-control bg-white border-light-subtle shadow-sm" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <hr class="flex-grow-1 border-secondary-subtle">
                                <span class="mx-2 text-uppercase fw-bold text-secondary small" style="font-size: 0.65rem; letter-spacing: 1px;">Thresholds</span>
                                <hr class="flex-grow-1 border-secondary-subtle">
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Parameter Name</label>
                                    <input type="text" name="parameter_name" class="form-control bg-white border-light-subtle shadow-sm" placeholder="Temperature">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Hi Limit</label>
                                    <input type="number" name="hi_limit" class="form-control bg-white border-light-subtle shadow-sm" placeholder="Max">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Lo Limit</label>
                                    <input type="number" name="lo_limit" class="form-control bg-white border-light-subtle shadow-sm" placeholder="Min">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <hr class="flex-grow-1 border-secondary-subtle">
                                <span class="mx-2 text-uppercase fw-bold text-secondary small" style="font-size: 0.65rem; letter-spacing: 1px;">Location</span>
                                <hr class="flex-grow-1 border-secondary-subtle">
                            </div>
                            <div class="vstack gap-2">
                                <input type="text" name="location_level_1" class="form-control bg-white border-light-subtle shadow-sm" placeholder="Building/Plant">
                                <input type="text" name="location_level_2" class="form-control bg-white border-light-subtle shadow-sm" placeholder="Floor/Area">
                                <input type="text" name="location_level_3" class="form-control bg-white border-light-subtle shadow-sm" placeholder="Specific Room">
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4 pt-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Register Device
                            </button>
                            <a href="/device" class="btn btn-link text-decoration-none text-muted small">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
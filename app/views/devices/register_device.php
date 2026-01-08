<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
    /* Professional Dashboard Background */
    body {
        background-color: #f8f9fa;
    }

    .page-header {
        background-color: #ffffff;
        border: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
        border-radius: 0.75rem;
        margin-bottom: 2rem;
    }

    .btn-back {
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.4rem 0.8rem;
    }
	
    /* Best Practice: Layered Gray Container */
    .form-wrapper-gray {
        background-color: #f1f3f5; /* Subtle deep gray for the 'well' */
        border: 1px solid #e9ecef;
        padding: 2.5rem;
        border-radius: 1rem;
    }

    /* Make white inputs 'pop' against the gray wrapper */
    .form-wrapper-gray .form-control,
    .form-wrapper-gray .form-select {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
    }

    .form-wrapper-gray .form-control:focus {
        background-color: #ffffff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .section-divider {
        color: #495057;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
</style>

<div class="container-sm mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
        
            <nav class="page-header shadow-sm d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;">Register New Device</h2>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-plus-circle me-1 text-primary"></i> 
                        Connect a new hardware asset to your hub.
                    </p>
                </div>
                <div>
                    <a href="/device" class="btn btn-outline-secondary btn-back shadow-sm">
                        <i class="fas fa-chevron-left me-1"></i> Back
                    </a>
                </div>
            </nav>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <form method="POST" action="/device/register" class="form-wrapper-gray">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-5">
                            <h6 class="section-divider fw-bold">
                                <i class="fas fa-info-circle me-1 text-primary"></i> Basic Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Device Name <span class="text-danger">*</span></label>
                                    <input type="text" name="device_name" class="form-control form-control-lg" required maxlength="255" placeholder="e.g., Main Water Pump">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Description</label>
                                    <textarea name="description" class="form-control" rows="2" maxlength="500" placeholder="Describe the purpose of this device..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h6 class="section-divider fw-bold">
                                <i class="fas fa-sliders-h me-1 text-primary"></i> Parameter & Thresholds
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Parameter Name</label>
                                    <input type="text" name="parameter_name" class="form-control" maxlength="100" placeholder="e.g., Temperature (°C)">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Hi Limit</label>
                                    <input type="number" step="any" name="hi_limit" class="form-control" placeholder="Max">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Lo Limit</label>
                                    <input type="number" step="any" name="lo_limit" class="form-control" placeholder="Min">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Trigger Condition</label>
                                    <input type="text" name="trigger_condition" class="form-control" placeholder="e.g., value > hi_limit">
                                    <div class="form-text">Logic used to fire an action.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Action</label>
                                    <input type="text" name="action" class="form-control" placeholder="e.g., Send Email Alert">
                                    <div class="form-text">What happens when trigger is met?</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="section-divider fw-bold">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i> Physical Location
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-muted">Building / Plant</label>
                                    <input type="text" name="location_level_1" class="form-control" placeholder="Level 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-muted">Floor / Area</label>
                                    <input type="text" name="location_level_2" class="form-control" placeholder="Level 2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-muted">Specific Room / Spot</label>
                                    <input type="text" name="location_level_3" class="form-control" placeholder="Level 3">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 d-flex justify-content-end gap-3 border-top mt-2">
                            <a href="/device" class="btn btn-link text-decoration-none text-muted fw-semibold">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Register Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
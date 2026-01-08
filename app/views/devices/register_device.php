<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container-sm mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Register New Device</h2>
        <a href="/device" class="btn btn-outline-secondary btn-sm">← Back to Devices</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="/device/register">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row-sm g-3">
                    <!-- Device Name (Required) -->
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Device Name <span class="text-danger">*</span></label>
                        <input type="text" name="device_name" class="form-control" required maxlength="255" placeholder="e.g., Pump Sensor #1">
                    </div>

                    <!-- Parameter & Limits -->
                    <div class="col-md-6">
                        <label class="form-label">Parameter Name</label>
                        <input type="text" name="parameter_name" class="form-control" maxlength="100" placeholder="e.g., temperature, pressure">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hi Limit</label>
                        <input type="number" step="any" name="hi_limit" class="form-control" placeholder="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lo Limit</label>
                        <input type="number" step="any" name="lo_limit" class="form-control" placeholder="20">
                    </div>

                    <!-- Trigger & Action -->
                    <div class="col-md-6">
                        <label class="form-label">Trigger Condition</label>
                        <input type="text" name="trigger_condition" class="form-control" maxlength="100" placeholder="e.g., >, <, between">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Action</label>
                        <input type="text" name="action" class="form-control" maxlength="100" placeholder="e.g., alert, shutdown, log">
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500" placeholder="Optional notes about this device"></textarea>
                    </div>

                    <!-- Location Hierarchy -->
                    <div class="col-md-4">
                        <label class="form-label">Location Level 1</label>
                        <input type="text" name="location_level_1" class="form-control" maxlength="100" placeholder="e.g., Plant A">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location Level 2</label>
                        <input type="text" name="location_level_2" class="form-control" maxlength="100" placeholder="e.g., Floor 2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location Level 3</label>
                        <input type="text" name="location_level_3" class="form-control" maxlength="100" placeholder="e.g., Room 101">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="/device" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Register Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
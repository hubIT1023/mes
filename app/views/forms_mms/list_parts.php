<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
.rs-card {
    transition: all 0.2s ease;
    border: 1px solid #e0e0e0;
}
.rs-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #c0c0c0;
}
.rs-card .rs-image-placeholder {
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}
</style>

<div class="container mt-4">
    <h2 class="mb-4">Machine Parts Inventory</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" action="/mes/parts-list" class="row g-3 mb-5">
        <div class="col-md-3">
            <label class="form-label fw-bold">Entity</label>
            <select name="entity" class="form-select">
                <option value="">All Entities</option>
                <?php foreach ($entities as $ent): ?>
                    <option value="<?= htmlspecialchars($ent) ?>" 
                            <?= (($_GET['entity'] ?? '') === $ent) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ent) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">Part Name</label>
            <input type="text" name="part_name" class="form-control" 
                   value="<?= htmlspecialchars($_GET['part_name'] ?? '') ?>" 
                   placeholder="e.g., Solenoid Valve">
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold">Part ID</label>
            <input type="text" name="part_id" class="form-control" 
                   value="<?= htmlspecialchars($_GET['part_id'] ?? '') ?>" 
                   placeholder="e.g., RS-873-2506">
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold">Vendor</label>
            <input type="text" name="vendor_id" class="form-control" 
                   value="<?= htmlspecialchars($_GET['vendor_id'] ?? '') ?>" 
                   placeholder="e.g., SMC">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
            <a href="/mes/parts-list" class="btn btn-outline-secondary">Clear</a>
        </div>
    </form>

    <!-- Results -->
    <?php if (empty($parts)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No parts found</h5>
            <p class="text-muted">
                <?= !empty(array_filter($_GET)) ? 'Try adjusting your filters.' : 'Add parts from the dashboard.' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Results (<?= count($parts) ?> parts)</h5>
            <a href="/mes/parts-list" class="btn btn-sm btn-outline-secondary">Refresh</a>
        </div>

        <div class="row g-4">
            <?php foreach ($parts as $part): ?>
                <div class="col-12">
                    <div class="rs-card rounded-3 bg-white">
                        <div class="d-flex align-items-start p-3">
                            <!-- Image (Left) -->
                            <?php
                            $imagePath = $part['image_path'] ?? '';
                            if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                     alt="Part Image"
                                     class="flex-shrink-0 rounded me-3"
                                     style="width: 100px; height: 100px; object-fit: contain;">
                            <?php else: ?>
                                <div class="rs-image-placeholder flex-shrink-0 rounded me-3"
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-cube fa-2x"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Content (Right) -->
                            <div class="flex-grow-1">
                                <!-- Part ID & Category -->
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($part['part_id']) ?></h5>
                                    <span class="badge <?= 
                                        $part['category'] === 'HIGH' ? 'bg-danger' : 
                                        ($part['category'] === 'MEDIUM' ? 'bg-warning text-dark' : 'bg-success') 
                                    ?> ms-2"><?= htmlspecialchars($part['category'] ?? 'LOW') ?></span>
                                </div>

                                <!-- Part Name -->
                                <p class="text-muted mb-2"><?= htmlspecialchars($part['part_name']) ?></p>

                                <!-- Metadata -->
                                <div class="d-flex flex-wrap gap-3 mb-3 small text-muted">
                                    <span><strong>Entity:</strong> <?= htmlspecialchars($part['entity']) ?></span>
                                    <span><strong>Serial:</strong> <?= htmlspecialchars($part['serial_no'] ?? '—') ?></span>
                                    <span><strong>Vendor:</strong> <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></span>
                                </div>

                                <!-- Actions -->
                                <form method="POST" action="/mes/machine-parts/delete" style="display:inline;" 
                                      onsubmit="return confirm('Delete this part?')">
                                    <input type="hidden" name="id" value="<?= (int)$part['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
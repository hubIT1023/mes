<?php include __DIR__ . '/../layouts/html/header.php'; ?>

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
                <?= isset($_GET) && array_filter($_GET) ? 'Try adjusting your filters.' : 'Add parts from the dashboard.' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Results (<?= count($parts) ?> parts)</h5>
            <a href="/mes/parts-list" class="btn btn-sm btn-outline-secondary">Refresh</a>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($parts as $part): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-3 hover-shadow">
                        <!-- Image -->
                        <?php
                        $imagePath = $part['image_path'] ?? '';
                        if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 class="card-img-top bg-light" 
                                 alt="Part Image"
                                 style="height: 150px; object-fit: contain;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Body -->
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge bg-secondary"><?= htmlspecialchars($part['entity']) ?></span>
                            </div>
                            <h6 class="card-title fw-bold mb-1"><?= htmlspecialchars($part['part_id']) ?></h6>
                            <p class="card-text small text-muted mb-2"><?= htmlspecialchars($part['part_name']) ?></p>

                            <div class="mt-auto small text-muted">
                                <div>Serial: <?= htmlspecialchars($part['serial_no'] ?? '—') ?></div>
                                <div>Vendor: <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center pt-2">
                            <span class="badge <?= 
                                $part['category'] === 'HIGH' ? 'bg-danger' : 
                                ($part['category'] === 'MEDIUM' ? 'bg-warning text-dark' : 'bg-success') 
                            ?>"><?= htmlspecialchars($part['category'] ?? 'LOW') ?></span>
                            
                            <form method="POST" action="/mes/machine-parts/delete" style="display:inline;" 
                                  onsubmit="return confirm('Delete this part?')">
                                <input type="hidden" name="id" value="<?= (int)$part['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger p-1">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Optional: Add subtle hover effect -->
<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
</style>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
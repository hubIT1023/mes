<?php include __DIR__ . '/../layouts/html/header.php'; ?>
<div class="container mt-4">
    <h2>Machine Parts Inventory</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" action="/mes/parts-list" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Entity</label>
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
            <label class="form-label">Part Name</label>
            <input type="text" name="part_name" class="form-control" 
                   value="<?= htmlspecialchars($_GET['part_name'] ?? '') ?>" 
                   placeholder="e.g., Solenoid Valve">
        </div>

        <div class="col-md-2">
            <label class="form-label">Part ID</label>
            <input type="text" name="part_id" class="form-control" 
                   value="<?= htmlspecialchars($_GET['part_id'] ?? '') ?>" 
                   placeholder="e.g., RS-873-2506">
        </div>

        <div class="col-md-2">
            <label class="form-label">Vendor</label>
            <input type="text" name="vendor_id" class="form-control" 
                   value="<?= htmlspecialchars($_GET['vendor_id'] ?? '') ?>" 
                   placeholder="e.g., SMC">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
            <a href="/mes/parts-list" class="btn btn-outline-secondary">Clear</a>
        </div>
    </form>

    <!-- Results -->
    <?php if (empty($parts)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No parts found. 
            <?= isset($_GET['filter']) ? 'Try adjusting your filters.' : 'Add parts from the dashboard.' ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Results (<?= count($parts) ?>)</h5>
            <a href="/mes/parts-list" class="btn btn-sm btn-outline-primary">Refresh</a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($parts as $part): ?>
           <!-- Inside foreach loop -->
		<div class="col">
			<div class="card h-100 shadow-sm">

			 <!-- Image -->
				<?php
				$imagePath = $part['image_path'] ?? '';

				if ($imagePath) {
					// ✅ CORRECT: $imagePath is already a web-accessible path like "/app/parts_img/xyz.jpg"
					$imageUrl = $imagePath;

					// Full server path (for existence check)
					$fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;

					if (file_exists($fullServerPath)) {
						echo '<img src="' . htmlspecialchars($imageUrl) . '" 
							  class="card-img-top bg-light" 
							  alt="Part Image"
							  style="object-fit: contain; height: 160px;">';
					} else {
						echo '<div class="card-header bg-light text-center text-muted py-5">
								Image Missing (file not found on server)
							  </div>';
					}
				} else {
					echo '<div class="card-header bg-light text-center text-muted py-5">
							No Image Available
						  </div>';
				}
				?>


				<!-- Body -->
				<div class="card-body">
					<h6 class="card-title"><?= htmlspecialchars($part['part_id']) ?></h6>
					<p class="card-text small">
						<strong><?= htmlspecialchars($part['part_name']) ?></strong><br>
						<small class="text-muted">
							Entity: <span class="badge bg-secondary"><?= htmlspecialchars($part['entity']) ?></span><br>
							Serial: <?= htmlspecialchars($part['serial_no'] ?? '<em>N/A</em>') ?><br>
							Vendor: <?= htmlspecialchars($part['vendor_id'] ?? '<em>N/A</em>') ?>
						</small>
					</p>
				</div>

				<!-- Footer -->
				<div class="card-footer d-flex justify-content-between align-items-center">
					<span class="badge <?= 
						$part['category'] === 'HIGH' ? 'bg-danger' : 
						($part['category'] === 'MEDIUM' ? 'bg-warning text-dark' : 'bg-success') 
					?>"><?= htmlspecialchars($part['category'] ?? 'LOW') ?></span>
					
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
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
.rs-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    background-color: #fff;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    transition: box-shadow 0.2s ease;
}
.rs-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.rs-image {
    width: 60px;
    height: 60px;
    object-fit: contain;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #6c757d;
}

.rs-info {
    flex-grow: 1;
}

.rs-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.rs-title {
    margin: 0;
    font-size: 1rem;
    font-weight: bold;
    line-height: 1.3;
}

.rs-subtitle {
    margin: 0;
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 4px;
}

.rs-meta {
    font-size: 0.75rem;
    color: #495057;
    margin: 8px 0;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.rs-description {
    width: 100%;
    padding: 12px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #212529;
    min-height: 60px;
    cursor: pointer;
    position: relative;
}

.rs-description:hover::after {
    content: "✏️";
    position: absolute;
    top: 4px;
    right: 4px;
    font-size: 0.9em;
    opacity: 0.6;
}

.rs-description.editing {
    cursor: default;
}
.rs-description.editing::after {
    display: none;
}

.rs-description-text {
    white-space: pre-line;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rs-description-input {
    width: 100%;
    box-sizing: border-box;
}

.rs-edit-controls {
    margin-top: 8px;
}

.rs-badge {
    font-size: 0.75rem;
    font-weight: bold;
    padding: 4px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}
.badge-high { background: #dc3545; color: white; }
.badge-medium { background: #ffc107; color: #212529; }
.badge-low { background: #28a745; color: white; }

.rs-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
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
                    <div class="rs-card">
                        <!-- Image -->
                        <?php
                        $imagePath = $part['image_path'] ?? '';
                        if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="Part Image" class="rs-image">
                        <?php else: ?>
                            <div class="rs-image"><i class="fas fa-cube"></i></div>
                        <?php endif; ?>

                        <!-- Info -->
                        <div class="rs-info">
                            <div class="rs-header">
                                <div>
                                    <h5 class="rs-title">RS No. <?= htmlspecialchars($part['part_id']) ?></h5>
                                    <p class="rs-subtitle"><?= htmlspecialchars($part['part_name']) ?></p>
                                </div>
                                <span class="rs-badge <?= 
                                    $part['category'] === 'HIGH' ? 'badge-high' : 
                                    ($part['category'] === 'MEDIUM' ? 'badge-medium' : 'badge-low') 
                                ?>">
                                    <?= htmlspecialchars($part['category'] ?? 'LOW') ?>
                                </span>
                            </div>

                            <div class="rs-meta">
                                <span><strong>Entity:</strong> <?= htmlspecialchars($part['entity']) ?></span>
                                <span><strong>Serial:</strong> <?= htmlspecialchars($part['serial_no'] ?? '—') ?></span>
                                <span><strong>Vendor:</strong> <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></span>
                            </div>

                            <!-- Inline-editable Description -->
                            <div class="rs-description" data-part-id="<?= (int)$part['id'] ?>">
                                <div class="rs-description-text">
                                    <?= nl2br(htmlspecialchars($part['description'] ?? 'Click to add description.')) ?>
                                </div>
                                <textarea class="rs-description-input d-none form-control" rows="3"
                                    data-original="<?= htmlspecialchars($part['description'] ?? '') ?>"
                                ><?= htmlspecialchars($part['description'] ?? '') ?></textarea>
                                <div class="rs-edit-controls d-none">
                                    <button type="button" class="btn btn-sm btn-primary btn-save-desc">Save</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-desc">Cancel</button>
                                </div>
                            </div>

                            <!-- Delete only -->
                            <div class="rs-actions">
                                <form method="POST" action="/mes/machine-parts/delete" onsubmit="return confirm('Delete this part?')">
                                    <input type="hidden" name="id" value="<?= (int)$part['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Inline Edit JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Click to edit
    document.querySelectorAll('.rs-description').forEach(desc => {
        desc.addEventListener('click', function(e) {
            if (e.target.tagName === 'TEXTAREA' || e.target.classList.contains('btn')) return;
            this.classList.add('editing');
            this.querySelector('.rs-description-text').classList.add('d-none');
            const input = this.querySelector('.rs-description-input');
            input.classList.remove('d-none');
            this.querySelector('.rs-edit-controls').classList.remove('d-none');
            input.focus();
        });
    });

    // Save
    document.querySelectorAll('.btn-save-desc').forEach(btn => {
        btn.addEventListener('click', async function() {
            const desc = this.closest('.rs-description');
            const id = desc.dataset.partId;
            const textarea = desc.querySelector('.rs-description-input');
            const newText = textarea.value.trim();
            const original = textarea.dataset.original;

            if (newText === original) {
                cancelEdit(desc);
                return;
            }

            try {
                const res = await fetch('/mes/machine-parts/update-desc', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        description: newText,
                        csrf_token: '<?= $_SESSION['csrf_token'] ?? "" ?>'
                    })
                });

                if (res.ok) {
                    desc.querySelector('.rs-description-text').innerHTML = 
                        newText ? newText.replace(/\n/g, '<br>') : 'Click to add description.';
                    textarea.dataset.original = newText;
                    cancelEdit(desc);
                } else {
                    const err = await res.json();
                    alert('Error: ' + (err.message || 'Failed to save'));
                    cancelEdit(desc);
                }
            } catch (e) {
                alert('Network error');
                cancelEdit(desc);
            }
        });
    });

    // Cancel
    document.querySelectorAll('.btn-cancel-desc').forEach(btn => {
        btn.addEventListener('click', function() {
            cancelEdit(this.closest('.rs-description'));
        });
    });

    function cancelEdit(desc) {
        desc.classList.remove('editing');
        desc.querySelector('.rs-description-text').classList.remove('d-none');
        desc.querySelector('.rs-description-input').classList.add('d-none');
        desc.querySelector('.rs-edit-controls').classList.add('d-none');
        const textarea = desc.querySelector('.rs-description-input');
        textarea.value = textarea.dataset.original;
    }
});
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
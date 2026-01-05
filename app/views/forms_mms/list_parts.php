<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
/* ======================= */
/* Desktop: Grid Layout */
/* ======================= */
.rs-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
    padding: 16px;
    display: grid;
    grid-template-columns: 60px 1fr 2fr auto; /* Image | Info | Description | Actions */
    gap: 16px;
    align-items: start;
    transition: box-shadow 0.2s;
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

.rs-info-title {
    margin: 0 0 4px 0;
    font-size: 1rem;
    font-weight: bold;
}
.rs-info-subtitle {
    margin: 0 0 8px 0;
    font-size: 0.875rem;
    color: #6c757d;
}
.rs-meta {
    font-size: 0.75rem;
    color: #495057;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.rs-description {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 12px;
    min-height: 80px;
    font-size: 0.875rem;
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
}
.rs-description-input {
    width: 100%;
    box-sizing: border-box;
    font-size: 0.875rem;
}
.rs-edit-controls {
    margin-top: 8px;
}

/* Actions Column */
.rs-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
}
.rs-badge {
    font-size: 0.75rem;
    font-weight: bold;
    padding: 4px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    text-align: center;
    min-width: 70px;
}
.badge-high { background: #dc3545; color: white; }
.badge-medium { background: #ffc107; color: #212529; }
.badge-low { background: #28a745; color: white; }
.btn-action {
    width: 80px;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 4px;
}

/* ======================= */
/* Mobile Layout (≤576px) */
/* ======================= */
@media (max-width: 576px) {
    .rs-card {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 12px;
    }

    .rs-image {
        width: 100%;
        max-width: 100px;
        margin: 0 auto;
    }

    .rs-description-section,
    .rs-description,
    .rs-description-input {
        width: 100%;
    }

    .rs-edit-controls {
        flex-direction: row;
        justify-content: flex-start;
        gap: 8px;
        width: 100%;
    }

    /* Actions: Badge + buttons in a row */
    .rs-actions {
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    .rs-badge {
        flex-shrink: 0;
    }

    .btn-action,
    .rs-actions form {
        flex: 1 1 auto;
        min-width: auto;
    }
}

/* ... your existing CSS ... */

/* Zoomable image container */
.rs-image-container {
    position: relative;
    display: inline-block; /* or block, depending on layout */
    cursor: zoom-in;
}

/* Zoom effect on hover/focus */
.rs-image-container:hover .rs-image,
.rs-image-container:focus .rs-image {
    transform: scale(2.5); /* Adjust zoom level (e.g., 2.5x) */
    z-index: 10;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
    border-radius: 8px;
}

/* Ensure the image can be transformed */
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
    /* Add transition for smooth zoom */
    transition: transform 0.3s ease, box-shadow 0.3s ease, z-index 0.3s ease;
    /* Ensure it can be layered above others */
    position: relative;
}

/* Optional: Improve mobile touch experience */
@media (hover: none) and (pointer: coarse) {
    .rs-image-container {
        cursor: pointer;
    }
    /* Use :active for immediate feedback on touch */
    .rs-image-container:active .rs-image {
        transform: scale(2.5);
        z-index: 10;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
    }
}
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-0">Machine Parts Inventory</h2>
            <p class="text-muted small">Manage and track critical spare parts</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light p-2 px-3 rounded shadow-sm">
                <li class="breadcrumb-item"><a href="/mes/dashboard_admin">Dashboard</a></li>
                <li class="breadcrumb-item active">Machine Parts</li>
            </ol>
        </nav>
    </div>

    <?php foreach (['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $class): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div class="alert <?= $class ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas <?= $key === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
                <?= htmlspecialchars($_SESSION[$key]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm mb-5 bg-light">
        <div class="card-body">
            <form method="GET" action="/mes/parts-list" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Entity</label>
                    <select name="entity" class="form-select border-0 shadow-sm">
                        <option value="">All Entities</option>
                        <?php foreach ($entities as $ent): ?>
                            <option value="<?= htmlspecialchars($ent) ?>" <?= (($_GET['entity'] ?? '') === $ent) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ent) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Part Name</label>
                    <input type="text" name="part_name" class="form-control border-0 shadow-sm" value="<?= htmlspecialchars($_GET['part_name'] ?? '') ?>" placeholder="Search name...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Part ID</label>
                    <input type="text" name="part_id" class="form-control border-0 shadow-sm" value="<?= htmlspecialchars($_GET['part_id'] ?? '') ?>" placeholder="ID...">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm w-100">Apply Filters</button>
                    <a href="/mes/parts-list" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($parts)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-4x text-light mb-3"></i>
            <h4 class="text-muted">No matching parts found</h4>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-muted">Showing <?= count($parts) ?> results</h5>
        </div>

        <div class="row">
            <?php foreach ($parts as $part): ?>
                <div class="col-12">
                    <div class="rs-card">
                        <div class="rs-image-container">
                            <?php 
                            $img = $part['image_path'] ?? '';
                            if ($img && file_exists($_SERVER['DOCUMENT_ROOT'] . $img)): ?>
                                <img src="<?= htmlspecialchars($img) ?>" alt="Part" class="rs-image shadow-sm">
                            <?php else: ?>
                                <div class="rs-image bg-light border"><i class="fas fa-microchip opacity-25"></i></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <span class="badge bg-light text-dark border mb-1">#<?= htmlspecialchars($part['part_id']) ?></span>
                            <h5 class="rs-info-title"><?= htmlspecialchars($part['part_name']) ?></h5>
                            <div class="rs-meta mt-2">
                                <span><strong>Entity:</strong> <?= htmlspecialchars($part['entity']) ?></span>
                                <span><strong>Vendor:</strong> <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></span>
                                <span class="text-primary fw-bold mt-1">Stock: <?= (int)$part['parts_available_on_hand'] ?> units</span>
                            </div>
                        </div>

                        <div class="rs-description-section">
                            <div class="rs-description shadow-sm" data-part-id="<?= (int)$part['id'] ?>">
                                <div class="rs-description-text text-muted">
                                    <?= nl2br(htmlspecialchars($part['description'] ?? 'No description provided.')) ?>
                                </div>
                                <textarea class="rs-description-input d-none form-control" rows="4" 
                                          data-original="<?= htmlspecialchars($part['description'] ?? '') ?>"><?= htmlspecialchars($part['description'] ?? '') ?></textarea>
                                <div class="rs-edit-controls d-none mt-2">
                                    <button class="btn btn-sm btn-primary btn-save-desc">Save</button>
                                    <button class="btn btn-sm btn-link text-muted btn-cancel-desc">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <div class="rs-actions">
                            <div class="rs-badge <?= $part['category'] === 'HIGH' ? 'badge-high' : ($part['category'] === 'MEDIUM' ? 'badge-medium' : 'badge-low') ?>">
                                <?= htmlspecialchars($part['category'] ?? 'LOW') ?> PRIORITY
                            </div>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPartModal"
                                    data-part-json='<?= json_encode($part, JSON_HEX_APOS) ?>'>
                                <i class="fas fa-edit me-1"></i> Full Edit
                            </button>
                            <form method="POST" action="/mes/machine-parts/delete" onsubmit="return confirm('Permanently delete this part?')">
                                <input type="hidden" name="id" value="<?= (int)$part['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="editPartModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Machine Part</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editPartForm">
                    <input type="hidden" id="edit-id" name="id">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Part ID / Model No</label>
                            <input type="text" class="form-control" name="part_id" id="edit-part_id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Part Name</label>
                            <input type="text" class="form-control" name="part_name" id="edit-part_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Entity</label>
                            <input type="text" class="form-control" name="entity" id="edit-entity">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category Priority</label>
                            <select class="form-select" name="category" id="edit-category">
                                <option value="LOW">Low</option>
                                <option value="MEDIUM">Medium</option>
                                <option value="HIGH">High</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Full Description</label>
                            <textarea class="form-control" name="description" id="edit-description" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePartBtn">Update Part</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. INLINE EDITING LOGIC
    document.querySelectorAll('.rs-description').forEach(desc => {
        desc.addEventListener('click', function(e) {
            if (e.target.closest('textarea, .btn')) return;
            this.classList.add('editing');
            this.querySelector('.rs-description-text').classList.add('d-none');
            const input = this.querySelector('.rs-description-input');
            input.classList.remove('d-none');
            this.querySelector('.rs-edit-controls').classList.remove('d-none');
            input.focus();
        });
    });

    document.querySelectorAll('.btn-save-desc').forEach(btn => {
        btn.addEventListener('click', async function() {
            const desc = this.closest('.rs-description');
            const textarea = desc.querySelector('.rs-description-input');
            const newText = textarea.value.trim();

            try {
                const res = await fetch('/mes/machine-parts/update-desc', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: desc.dataset.partId,
                        description: newText,
                        csrf_token: '<?= $_SESSION['csrf_token'] ?? "" ?>'
                    })
                });

                if (res.ok) {
                    desc.querySelector('.rs-description-text').innerHTML = newText.replace(/\n/g, '<br>') || 'No description.';
                    textarea.dataset.original = newText;
                    closeInline(desc);
                }
            } catch (e) { alert('Save failed'); }
        });
    });

    document.querySelectorAll('.btn-cancel-desc').forEach(btn => {
        btn.addEventListener('click', () => closeInline(btn.closest('.rs-description')));
    });

    function closeInline(container) {
        container.classList.remove('editing');
        container.querySelector('.rs-description-text').classList.remove('d-none');
        container.querySelector('.rs-description-input').classList.add('d-none');
        container.querySelector('.rs-edit-controls').classList.add('d-none');
    }

    // 2. MODAL POPULATION (Using JSON data attribute for cleanliness)
    const editModal = document.getElementById('editPartModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const data = JSON.parse(event.relatedTarget.getAttribute('data-part-json'));
        Object.keys(data).forEach(key => {
            const field = document.getElementById('edit-' + key);
            if (field) field.value = data[key];
        });
    });

    // 3. MODAL SAVE
    document.getElementById('savePartBtn').addEventListener('click', async function() {
        const formData = new FormData(document.getElementById('editPartForm'));
        const payload = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('/mes/machine-parts/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (res.ok) location.reload();
            else alert('Update failed');
        } catch (e) { alert('Network error'); }
    });
});
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
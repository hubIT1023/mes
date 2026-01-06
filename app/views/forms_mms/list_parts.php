<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
/* ======================= */
/* Modern Grid Design      */
/* ======================= */
:root {
    --primary-blue: #3498db;
    --border-color: #e0e6ed;
    --bg-light: #f8f9fa;
}

.rs-card {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background: #fff;
    padding: 20px;
    display: grid;
    /* Wider image: 120px instead of 80px */
    grid-template-columns: 120px 1fr 2fr 130px; /* Image | Info | Description | Actions */
    gap: 24px;
    align-items: start; /* Changed to 'start' for vertical alignment with taller content */
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    margin-bottom: 1rem;
    position: relative; /* Ensure zoom stays within card context */
}

.rs-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    border-color: var(--primary-blue);
    transform: translateY(-2px);
}

/* ======================= */
/* Enhanced Image Zoom     */
/* ======================= */
.rs-image-container {
    position: relative;
    width: 120px;
    height: 120px; /* Square, larger */
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rs-image {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Use 'contain' to preserve aspect ratio (better for part images) */
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem; /* Slightly larger icon */
    color: #6c757d;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: zoom-in;
    position: relative;
}

/* Zoom on hover */
.rs-image-container:hover .rs-image {
    transform: scale(2.2); /* Slightly reduced scale for better fit */
    z-index: 1000;
    box-shadow: 0 10px 35px rgba(0,0,0,0.35);
    border-radius: 10px;
}

/* Ensure zoomed image appears above everything */
.rs-image-container:hover {
    z-index: 1001;
}

/* ======================= */
/* Content & Typography    */
/* ======================= */
.rs-info-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--primary-blue);
}

.rs-info-subtitle {
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 8px;
}

.rs-meta span {
    display: block;
    font-size: 0.8rem;
    color: #7f8c8d;
    line-height: 1.5;
}

/* Inline Edit Area */
.rs-description {
    background: #fafafa;
    border: 1px dashed #cbd5e0;
    border-radius: 8px;
    padding: 12px;
    min-height: 90px;
    font-size: 0.875rem;
    cursor: text;
    position: relative;
    transition: background 0.2s;
}

.rs-description:hover {
    background: #fffdf5;
    border-style: solid;
}

.rs-description:hover::after {
    content: "Edit Details ✎";
    position: absolute;
    bottom: 5px;
    right: 10px;
    font-size: 0.75rem;
    color: var(--primary-blue);
}

/* Badges & Actions */
.rs-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rs-badge {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 5px;
    border-radius: 6px;
    text-align: center;
    letter-spacing: 0.5px;
}
.badge-high { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.badge-medium { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge-low { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }

/* Desktop+: Adjust gaps */
@media (min-width: 993px) {
    .rs-card {
        grid-column-gap: 28px;
    }
}

/* Tablet: Reduce image size slightly */
@media (max-width: 992px) {
    .rs-card {
        grid-template-columns: 100px 1fr 1fr;
        gap: 16px;
    }
    .rs-image-container {
        width: 100px;
        height: 100px;
    }
    .rs-actions {
        grid-column: span 3;
        flex-direction: row;
        flex-wrap: wrap;
    }
    .rs-actions > * {
        flex: 1 1 45%;
    }
}

/* Mobile: Full stack */
@media (max-width: 576px) {
    .rs-card {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
    }
    .rs-image-container {
        align-self: center;
        width: 140px;
        height: 140px;
        margin-bottom: 16px;
    }
    .rs-actions {
        width: 100%;
        flex-direction: column;
        gap: 8px;
    }
    .rs-actions .btn,
    .rs-actions form {
        width: 100%;
    }
	
	.h1-tall {
  line-height: 2.0;
}
}
</style>

<div class="container-lg mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="fw-bold mb-0 h1-tall">Machine Parts Inventory</h1>
            <p class="text-muted small">Manage and track critical spare parts</p>
        </div>
		
        <nav class="flex flex-wrap gap-2 sm:gap-4 space-y-1">
		  <a href="/mes/hub_portal" class="flex items-center px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition group">
			<i class="fas fa-th-large mr-3 text-gray-400 group-hover:text-blue-500"></i> 
			<span class="font-medium">Hub Portal</span>
		  </a>

		  <a href="/mes/signout" class="flex items-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/40 transition group">
			<i class="fas fa-power-off mr-3 opacity-80 group-hover:scale-110 transition-transform"></i> 
			<span class="font-medium">Logout</span>
		  </a>
		</nav>
    </div>

    <!-- ✅ FLASH MESSAGES (now will appear after update) -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

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
                                <img src="<?= htmlspecialchars($img) ?>" alt="Part image for <?= htmlspecialchars($part['part_name']) ?>" class="rs-image shadow-sm">
                            <?php else: ?>
                                <div class="rs-image bg-light border"><i class="fas fa-microchip opacity-25"></i></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <span class="badge bg-light text-dark border mb-1">model: <?= htmlspecialchars($part['part_id']) ?></span>
                            <h5 class="rs-info-title"><?= htmlspecialchars($part['part_name']) ?></h5>
                            <div class="rs-meta mt-2">
                                <span><strong>Entity:</strong> <?= htmlspecialchars($part['entity']) ?></span>
                                <span><strong>Vendor:</strong> <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></span>
                                <span><strong>SAP Code:</strong> <?= htmlspecialchars($part['sap_code']) ?></span>
                            </div>
                        </div>

                        <div class="rs-description-section">
                            <label class="form-label fw-bold mb-2">Details</label>
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
                            <!-- Trigger modal -->
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPartModal"
                                    data-part-json='<?= json_encode($part, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
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

<!-- ✅ EDIT MODAL: REAL FORM (no AJAX) -->
<div class="modal fade" id="editPartModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Machine Part</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- REAL POST FORM -->
                <form id="editPartForm" method="POST" action="/mes/machine-parts/update">
                    <input type="hidden" name="id" id="edit-id">
                    <input type="hidden" name="asset_id" id="edit-asset_id">
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
                            <input type="text" class="form-control" name="entity" id="edit-entity" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category Priority</label>
                            <select class="form-select" name="category" id="edit-category">
                                <option value="LOW">Low</option>
                                <option value="MEDIUM">Medium</option>
                                <option value="HIGH">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Serial No</label>
                            <input type="text" class="form-control" name="serial_no" id="edit-serial_no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Vendor ID</label>
                            <input type="text" class="form-control" name="vendor_id" id="edit-vendor_id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">MFG Code</label>
                            <input type="text" class="form-control" name="mfg_code" id="edit-mfg_code">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">SAP Code</label>
                            <input type="text" class="form-control" name="sap_code" id="edit-sap_code">
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
                <!-- Submit the real form -->
                <button type="submit" form="editPartForm" class="btn btn-primary">Update Part</button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Keep inline description editing (uses AJAX, but that's OK) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inline description editing (AJAX - fine for single field)
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
                } else {
                    alert('Failed to save description');
                }
            } catch (e) { 
                alert('Network error while saving description'); 
            }
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

    // ✅ Modal population (no AJAX save anymore)
    const editModal = document.getElementById('editPartModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const data = JSON.parse(event.relatedTarget.getAttribute('data-part-json'));
        // Populate all editable fields
        const fieldNames = ['id', 'asset_id', 'part_id', 'part_name', 'entity', 'category', 
                           'serial_no', 'vendor_id', 'mfg_code', 'sap_code', 'description'];
        fieldNames.forEach(key => {
            const field = document.getElementById('edit-' + key);
            if (field) {
                field.value = data[key] || '';
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
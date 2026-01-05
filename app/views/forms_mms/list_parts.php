<?php

// include __DIR__ . '/../layouts/html/header.php'; 

?>

<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?= htmlspecialchars($_SESSION['org_name'] ?? 'Maintenance Management') ?> — Machine Parts
  </title>
  <link rel="icon" type="image/png" href="/app/Assets/img/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }

    /* === Your existing CSS (rs-card, rs-image, zoom, etc.) === */
    /* (No changes needed — your CSS is correct) */
    .rs-card { /* ... */ }
    .rs-card:hover { /* ... */ }
    .rs-image { /* ... */ }
    .rs-info-title { /* ... */ }
    .rs-info-subtitle { /* ... */ }
    .rs-meta { /* ... */ }
    .rs-description { /* ... */ }
    .rs-description:hover::after { /* ... */ }
    .rs-description.editing { /* ... */ }
    .rs-description.editing::after { /* ... */ }
    .rs-description-text { /* ... */ }
    .rs-description-input { /* ... */ }
    .rs-edit-controls { /* ... */ }
    .rs-actions { /* ... */ }
    .rs-badge { /* ... */ }
    .badge-high { background: #dc3545; color: white; }
    .badge-medium { background: #ffc107; color: #212529; }
    .badge-low { background: #28a745; color: white; }
    .btn-action { /* ... */ }

    @media (max-width: 576px) {
      .rs-card { /* ... */ }
      .rs-image { /* ... */ }
      /* ... rest of mobile styles */
    }

    /* Zoomable image */
    .rs-image-container {
      position: relative;
      display: inline-block;
      cursor: zoom-in;
    }
    .rs-image-container:hover .rs-image,
    .rs-image-container:focus .rs-image {
      transform: scale(2.5);
      z-index: 10;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
      border-radius: 8px;
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
      transition: transform 0.3s ease, box-shadow 0.3s ease, z-index 0.3s ease;
      position: relative;
    }
    @media (hover: none) and (pointer: coarse) {
      .rs-image-container { cursor: pointer; }
      .rs-image-container:active .rs-image {
        transform: scale(2.5);
        z-index: 10;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
      }
    }
  </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col min-h-screen">

  <!-- Top Navigation Bar -->
  <header class="bg-white dark:bg-gray-800 shadow-md py-4 px-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-0">
    <div class="flex items-center gap-3">
      <h1 class="text-xl font-bold text-blue-600 dark:text-blue-400">
        <?= htmlspecialchars($_SESSION['org_alias'] ?? $_SESSION['org_name'] ?? 'MMS') ?>
      </h1>
    </div>

    <nav class="flex flex-wrap gap-2 sm:gap-4">
      <a href="/mes/hub_portal" class="flex items-center px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition group">
        <i class="fas fa-th-large mr-2 text-gray-400 group-hover:text-blue-500"></i> Hub Portal
      </a>
      <a href="/mes/signout" class="flex items-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/40 transition group">
        <i class="fas fa-power-off mr-2 opacity-80"></i> Logout
      </a>
    </nav>
  </header>

  <!-- Main Content -->
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <h2 class="mb-0">Machine Parts Inventory</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="/mes/dashboard_admin">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">Machine Parts</li>
        </ol>
      </nav>
    </div>

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
      <!-- ... your filter form (unchanged) ... -->
      <div class="col-md-3">
        <label class="form-label fw-bold">Entity</label>
        <select name="entity" class="form-select">
          <option value="">All Entities</option>
          <?php foreach ($entities as $ent): ?>
            <option value="<?= htmlspecialchars($ent) ?>" <?= (($_GET['entity'] ?? '') === $ent) ? 'selected' : '' ?>>
              <?= htmlspecialchars($ent) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold">Part Name</label>
        <input type="text" name="part_name" class="form-control" value="<?= htmlspecialchars($_GET['part_name'] ?? '') ?>" placeholder="e.g., Solenoid Valve">
      </div>
      <div class="col-md-2">
        <label class="form-label fw-bold">Part ID</label>
        <input type="text" name="part_id" class="form-control" value="<?= htmlspecialchars($_GET['part_id'] ?? '') ?>" placeholder="e.g., RS-873-2506">
      </div>
      <div class="col-md-2">
        <label class="form-label fw-bold">Vendor</label>
        <input type="text" name="vendor_id" class="form-control" value="<?= htmlspecialchars($_GET['vendor_id'] ?? '') ?>" placeholder="e.g., SMC">
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
        <p class="text-muted"><?= !empty(array_filter($_GET)) ? 'Try adjusting your filters.' : 'Add parts from the dashboard.' ?></p>
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
              <!-- Column 1: Image -->
              <div class="rs-image-container">
                <?php
                $imagePath = $part['image_path'] ?? '';
                if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                  <img src="<?= htmlspecialchars($imagePath) ?>" alt="Part Image" class="rs-image">
                <?php else: ?>
                  <div class="rs-image"><i class="fas fa-cube"></i></div>
                <?php endif; ?>
              </div>

              <!-- Column 2: Info -->
              <div>
                <h5 class="rs-info-title">Model No. <?= htmlspecialchars($part['part_id']) ?></h5>
                <p class="rs-info-subtitle"><?= htmlspecialchars($part['part_name']) ?></p>
                <div class="rs-meta">
                  <span><strong>Entity:</strong> <?= htmlspecialchars($part['entity']) ?></span>
                  <span><strong>Serial:</strong> <?= htmlspecialchars($part['serial_no'] ?? '—') ?></span>
                  <span><strong>Vendor:</strong> <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></span>
                </div>
              </div>

              <!-- Column 3: Description -->
              <div class="rs-description-section">
                <label class="form-label fw-bold mb-2">Details</label>
                <div class="rs-description" data-part-id="<?= (int)$part['id'] ?>">
                  <div class="rs-description-text"><?= nl2br(htmlspecialchars($part['description'] ?? 'Click to add description.')) ?></div>
                  <textarea class="rs-description-input d-none form-control" rows="4" data-original="<?= htmlspecialchars($part['description'] ?? '') ?>"><?= htmlspecialchars($part['description'] ?? '') ?></textarea>
                  <div class="rs-edit-controls d-none mt-2">
                    <button type="button" class="btn btn-sm btn-primary btn-save-desc">Save</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-desc">Cancel</button>
                  </div>
                </div>
              </div>

              <!-- Column 4: Actions -->
              <div class="rs-actions">
                <span class="rs-badge <?= 
                  $part['category'] === 'HIGH' ? 'badge-high' : 
                  ($part['category'] === 'MEDIUM' ? 'badge-medium' : 'badge-low') 
                ?>">
                  <?= htmlspecialchars($part['category'] ?? 'LOW') ?>
                </span>
                <button type="button" class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#editPartModal"
                  data-part-id="<?= (int)$part['id'] ?>"
                  data-asset-id="<?= htmlspecialchars($part['asset_id']) ?>"
                  data-entity="<?= htmlspecialchars($part['entity']) ?>"
                  data-part-id-field="<?= htmlspecialchars($part['part_id']) ?>"
                  data-part-name="<?= htmlspecialchars($part['part_name']) ?>"
                  data-serial-no="<?= htmlspecialchars($part['serial_no'] ?? '') ?>"
                  data-vendor-id="<?= htmlspecialchars($part['vendor_id'] ?? '') ?>"
                  data-mfg-code="<?= htmlspecialchars($part['mfg_code'] ?? '') ?>"
                  data-sap-code="<?= htmlspecialchars($part['sap_code'] ?? '') ?>"
                  data-category="<?= htmlspecialchars($part['category'] ?? 'LOW') ?>"
                  data-parts-available="<?= (int)($part['parts_available_on_hand'] ?? 0) ?>"
                  data-description="<?= htmlspecialchars($part['description'] ?? '') ?>">
                  Edit
                </button>
                <form method="POST" action="/mes/machine-parts/delete" onsubmit="return confirm('Delete this part?')">
                  <input type="hidden" name="id" value="<?= (int)$part['id'] ?>">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger btn-action">Delete</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Edit Part Modal -->
  <div class="modal fade" id="editPartModal" tabindex="-1" aria-labelledby="editPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editPartModalLabel">Edit Machine Part</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editPartForm">
            <input type="hidden" id="edit-id" name="id">
            <input type="hidden" id="edit-csrf" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row">
              <!-- ... your form fields (unchanged) ... -->
              <div class="col-md-6 mb-3"><label class="form-label">Asset ID</label><input type="text" class="form-control" id="edit-asset-id" name="asset_id" required></div>
              <div class="col-md-6 mb-3"><label class="form-label">Entity</label><input type="text" class="form-control" id="edit-entity" name="entity" required></div>
              <div class="col-md-6 mb-3"><label class="form-label">Part ID</label><input type="text" class="form-control" id="edit-part-id" name="part_id" required></div>
              <div class="col-md-6 mb-3"><label class="form-label">Part Name</label><input type="text" class="form-control" id="edit-part-name" name="part_name" required></div>
              <div class="col-md-6 mb-3"><label class="form-label">Serial No</label><input type="text" class="form-control" id="edit-serial-no" name="serial_no"></div>
              <div class="col-md-6 mb-3"><label class="form-label">Vendor ID</label><input type="text" class="form-control" id="edit-vendor-id" name="vendor_id"></div>
              <div class="col-md-6 mb-3"><label class="form-label">MFG Code</label><input type="text" class="form-control" id="edit-mfg-code" name="mfg_code"></div>
              <div class="col-md-6 mb-3"><label class="form-label">SAP Code</label><input type="text" class="form-control" id="edit-sap-code" name="sap_code"></div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" id="edit-category" name="category">
                  <option value="LOW">Low</option>
                  <option value="MEDIUM">Medium</option>
                  <option value="HIGH">High</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Parts Available</label>
                <input type="number" class="form-control" id="edit-parts-available" name="parts_available_on_hand" min="0">
              </div>
              <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="savePartBtn">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // === Your existing JS (inline edit + modal logic) ===
    // (No changes needed — your JS is correct)
    document.addEventListener('DOMContentLoaded', function () {
      // ... your inline-edit JS ...
    });

    const editModal = document.getElementById('editPartModal');
    editModal.addEventListener('show.bs.modal', function (event) {
      // ... populate modal ...
    });

    document.getElementById('savePartBtn').addEventListener('click', async function() {
      // ... save logic ...
    });
  </script>

</body>
</html>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
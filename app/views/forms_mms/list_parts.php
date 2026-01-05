<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Machine Parts Inventory Management">
  <meta name="author" content="HubIt.online">

  <title>Machine Parts Inventory — HubIt.online</title>

  <!-- Custom fonts -->
  <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- SB Admin 2 core CSS -->
  <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">

  <!-- Custom styles -->
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
      grid-template-columns: 60px 1fr 2fr auto;
      gap: 16px;
      align-items: start;
      transition: box-shadow 0.2s;
    }

    .rs-card:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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

    /* Mobile Layout (≤576px) */
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

    /* Zoomable Image */
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
      transition: transform 0.3s ease, box-shadow 0.3s ease, z-index 0.3s ease;
      position: relative;
    }

    @media (hover: none) and (pointer: coarse) {
      .rs-image-container {
        cursor: pointer;
      }
      .rs-image-container:active .rs-image {
        transform: scale(2.5);
        z-index: 10;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
      }
    }
  </style>
</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar (if used elsewhere, otherwise skip) -->
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">

        <div class="container-fluid mt-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h1 class="h3 mb-0 text-gray-800">Machine Parts Inventory</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/mes/dashboard_admin">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Machine Parts</li>
              </ol>
            </nav>
          </div>

          <!-- Alerts -->
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
              <p class="text-muted">
                <?= !empty(array_filter($_GET)) ? 'Try adjusting your filters.' : 'Add parts from the dashboard.' ?>
              </p>
            </div>
          <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="h5 mb-0">Results (<?= count($parts) ?> parts)</h2>
              <a href="/mes/parts-list" class="btn btn-sm btn-outline-secondary">Refresh</a>
            </div>

            <div class="row g-4">
              <?php foreach ($parts as $part): ?>
                <div class="col-12">
                  <div class="rs-card">
                    <div class="rs-image-container">
                      <?php
                      $imagePath = $part['image_path'] ?? '';
                      if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Part Image" class="rs-image">
                      <?php else: ?>
                        <div class="rs-image"><i class="fas fa-cube"></i></div>
                      <?php endif; ?>
                    </div>

                    <div>
                      <h3 class="rs-info-title">Model No. <?= htmlspecialchars($part['part_id']) ?></h3>
                      <p class="rs-info-subtitle"><?= htmlspecialchars($part['part_name']) ?></p>
                      <div class="rs-meta">
                        <span><strong>Entity:</strong> <?= htmlspecialchars($part['entity']) ?></span>
                        <span><strong>Serial:</strong> <?= htmlspecialchars($part['serial_no'] ?? '—') ?></span>
                        <span><strong>Vendor:</strong> <?= htmlspecialchars($part['vendor_id'] ?? '—') ?></span>
                      </div>
                    </div>

                    <div class="rs-description-section">
                      <label class="form-label fw-bold mb-2">Details</label>
                      <div class="rs-description" data-part-id="<?= (int)$part['id'] ?>">
                        <div class="rs-description-text">
                          <?= nl2br(htmlspecialchars($part['description'] ?? 'Click to add description.')) ?>
                        </div>
                        <textarea class="rs-description-input d-none form-control" rows="4"
                          data-original="<?= htmlspecialchars($part['description'] ?? '') ?>"><?= htmlspecialchars($part['description'] ?? '') ?></textarea>
                        <div class="rs-edit-controls d-none mt-2">
                          <button type="button" class="btn btn-sm btn-primary btn-save-desc">Save</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-desc">Cancel</button>
                        </div>
                      </div>
                    </div>

                    <div class="rs-actions">
                      <span class="rs-badge <?= 
                        $part['category'] === 'HIGH' ? 'badge-high' : 
                        ($part['category'] === 'MEDIUM' ? 'badge-medium' : 'badge-low') 
                      ?>">
                        <?= htmlspecialchars($part['category'] ?? 'LOW') ?>
                      </span>
                      <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                        data-bs-toggle="modal"
                        data-bs-target="#editPartModal"
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
      </div>

      <!-- Footer -->
      <?php include __DIR__ . '/../layouts/html/footer.php'; ?>
    </div>
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
              <div class="col-md-6 mb-3">
                <label class="form-label">Asset ID</label>
                <input type="text" class="form-control" id="edit-asset-id" name="asset_id" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Entity</label>
                <input type="text" class="form-control" id="edit-entity" name="entity" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Part ID</label>
                <input type="text" class="form-control" id="edit-part-id" name="part_id" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Part Name</label>
                <input type="text" class="form-control" id="edit-part-name" name="part_name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Serial No</label>
                <input type="text" class="form-control" id="edit-serial-no" name="serial_no">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Vendor ID</label>
                <input type="text" class="form-control" id="edit-vendor-id" name="vendor_id">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">MFG Code</label>
                <input type="text" class="form-control" id="edit-mfg-code" name="mfg_code">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">SAP Code</label>
                <input type="text" class="form-control" id="edit-sap-code" name="sap_code">
              </div>
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

  <!-- Bootstrap JS (assumed already loaded in footer or layout) -->
  <script>
    // === Your existing inline-edit and modal JS here ===
    document.addEventListener('DOMContentLoaded', function () {
      // ... (keep your existing JavaScript unchanged)
    });
  </script>

</body>
</html>
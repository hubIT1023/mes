<?php
// /public/forms/scheduled_maintenance_form.php
/*
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    header("Location: /signin");
    exit;
}

// Flash messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maintenance Type & Interval Configuration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <style>
    body {
      background-color: #f8fafc;
      font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    .form-container {
      max-width: 900px;
      margin: 3rem auto;
      background: #fff;
      border-radius: 0.75rem;
      box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
      padding: 2rem;
    }

    .breadcrumb {
      background-color: transparent;
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }

    .breadcrumb a {
      text-decoration: none;
      color: #0d6efd;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    table th,
    table td {
      vertical-align: middle !important;
    }

    table tbody tr:hover {
      background-color: #f1f3f5;
    }

    .btn-success {
      font-weight: 500;
      letter-spacing: 0.3px;
    }

    .info-box {
      background-color: #f8f9fa;
      border-left: 4px solid #0d6efd;
    }

    h3 {
      font-weight: 600;
    }
  </style>
</head>
<body>
<div class="container form-container">

  <!-- Breadcrumb -->
  <nav class="breadcrumb">
    <a href="/dashboard">Dashboard</a>
    <span class="text-muted mx-1">›</span>
    <span>Maintenance Type & Configuration</span>
  </nav>

  <!-- Page Title -->
  <h3 class="mb-3">Maintenance Type & Interval Configuration</h3>

  <p class="text-muted mb-4">
    Configure maintenance intervals and checklist references for each maintenance type.
    These configurations will be used to auto-schedule asset maintenance activities.
  </p>

  <!-- Notifications (Uncomment when integrating PHP logic) -->
  <?php /* if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>? Success:</strong> <?= htmlspecialchars($success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>? Error:</strong> <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; */?>

  <!-- Form -->
  <form method="POST" action="/handler/sched_maint_handler.php">
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))) ?>">

    <div class="table-responsive mb-4">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>Maintenance Type</th>
          <th>Interval (Days)</th>
          <th>Checklist ID</th>
          <th>Work Order Prefix</th>
        </tr>
        </thead>
        <tbody>
        <?php for ($i = 1; $i <= 1; $i++): ?>
          <tr>
            <td><?= $i ?></td>
            <td>
              <input type="text" name="maintenance_type[<?= $i ?>]" class="form-control"
                     placeholder="e.g., Monthly PM"
                     value="<?= htmlspecialchars($_POST['maintenance_type'][$i] ?? '') ?>"
                     maxlength="255" required>
            </td>
            <td>
              <input type="number" name="interval_days[<?= $i ?>]" class="form-control"
                     placeholder="30"
                     min="1" max="3650"
                     value="<?= (int)($_POST['interval_days'][$i] ?? '') ?>" required>
            </td>
            <td>
              <input type="text" name="checklist_id[<?= $i ?>]" class="form-control"
                     placeholder="e.g., MTH-01"
                     value="<?= htmlspecialchars($_POST['checklist_id'][$i] ?? '') ?>"
                     maxlength="50" required>
            </td>
            <td>
              <input type="text" name="work_order[<?= $i ?>]" class="form-control"
                     placeholder="e.g., WO-MTH"
                     value="<?= htmlspecialchars($_POST['work_order'][$i] ?? 'WO-' . $i) ?>"
                     maxlength="50" required>
            </td>
          </tr>
        <?php endfor; ?>
        </tbody>
      </table>
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-success btn-lg">
        Save Maintenance Configuration
      </button>
    </div>
  </form>

  <!-- Info Box -->
  <div class="info-box mt-4 p-3 rounded">
    <h6 class="fw-semibold mb-2">Notes:</h6>
    <ul class="mb-0 small text-muted">
      <li>Only rows with <strong>all fields completed</strong> will be saved.</li>
      <li><code>Work Order Prefix</code> is used to generate work order numbers (e.g., WO-MTH-001).</li>
      <li>Each <code>Checklist ID</code> must be unique within your organization.</li>
      <li>Updates affect only future scheduled maintenance tasks.</li>
    </ul>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// /public/forms/state_config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    die("Unauthorized");
}

global $pdo;
if (!$pdo) {
    require_once __DIR__ . '/../../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
    if (!$pdo) {
        die("Database connection failed");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_states'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $_SESSION['error'] = "CSRF validation failed";
        header("Location: /forms/state_config.php");
        exit;
    }

    try {
        // Delete current org config
        $stmt = $pdo->prepare("DELETE FROM state_config WHERE org_id = ?");
        $stmt->execute([$orgId]);

        // Insert updated config
        $insert = $pdo->prepare("
            INSERT INTO state_config (org_id, state_key, label, css_class)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($_POST['states'] as $id => $fields) {
            $state_key = trim($fields['state_key']);
            $label = trim($fields['label']);
            $css_class = trim($fields['css_class']);

            if (empty($state_key)) continue;

            $insert->execute([$orgId, $state_key, $label, $css_class]);
        }

        $_SESSION['success'] = "State configuration saved!";
        header("Location: /forms/state_config.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Save failed: " . $e->getMessage();
    }
}

// Regenerate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch config: org overrides + default fallback
try {
    $stmt = $pdo->prepare("
        SELECT state_key, label, css_class, org_id
        FROM state_config
        WHERE org_id = ? OR org_id = 'default'
        ORDER BY 
            CASE WHEN org_id = ? THEN 0 ELSE 1 END,
            state_key
    ");
    $stmt->execute([$orgId, $orgId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $config = [];
    foreach ($rows as $row) {
        $key = $row['state_key'];
        if (!isset($config[$key])) {
            $config[$key] = [
                'label' => $row['label'],
                'css_class' => $row['css_class'],
                'source' => $row['org_id'] === $orgId ? 'Custom' : 'Default'
            ];
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to load config";
    $config = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>State Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/../Assets/css/custom-colors.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">

    <h4>State Configuration</h4>
    <p class="text-muted">Edit how stop causes appear in the dashboard.</p>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Configure Tool State Columns</h1>
        <a href="/dashboard" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <table class="table table-bordered" id="stateConfigTable">
            <thead class="table-light">
                <tr>
                    <th>State Key</th>
                    <th>Label</th>
                    <th>CSS Class</th>
                    <th>Preview</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($config)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No state configurations found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($config as $state_key => $data): ?>
                    <tr>
                        <td>
                            <input type="text"
                                   name="states[<?= htmlspecialchars($state_key) ?>][state_key]"
                                   value="<?= htmlspecialchars($state_key) ?>"
                                   class="form-control form-control-sm"
                                   <?= $data['source'] === 'Custom' ? '' : 'disabled' ?>>
                        </td>
                        <td>
                            <input type="text"
                                   name="states[<?= htmlspecialchars($state_key) ?>][label]"
                                   value="<?= htmlspecialchars($data['label']) ?>"
                                   class="form-control form-control-sm">
                        </td>
                        <td>
                            <input type="text"
                                   name="states[<?= htmlspecialchars($state_key) ?>][css_class]"
                                   value="<?= htmlspecialchars($data['css_class']) ?>"
                                   class="form-control form-control-sm">
                        </td>
                        <td>
                            <span class="badge <?= htmlspecialchars($data['css_class']) ?>">
                                <?= htmlspecialchars($data['label']) ?>
                            </span>
                        </td>
                        <td>
                            <?= $data['source'] ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Add New State Button -->
        <div class="mb-3">
            <button type="button" class="btn btn-outline-primary btn-sm" id="addStateRow">
                + Add State Property
            </button>
        </div>

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <button type="submit" name="save_states" class="btn btn-success">Save All Changes</button>
        </div>
    </form>

</div>

<!-- JavaScript to Add New Row -->
<script>
function attachCssClassListeners() {
    document.querySelectorAll('input[name*="[css_class]"]').forEach(input => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            const preview = row.querySelector('.badge');
            if (preview) {
                // Reset existing bg-* classes
                preview.className = 'badge ' + input.value.trim();
            }
        });
    });
}
document.getElementById('addStateRow').addEventListener('click', () => {
    const table = document.getElementById('stateConfigTable');
    const tbody = table.querySelector('tbody');

    // If table is empty, remove placeholder row
    const firstRow = tbody.querySelector('tr');
    if (firstRow && firstRow.childElementCount === 1) {
        tbody.innerHTML = '';
    }

    const index = Date.now(); // Unique ID
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>
            <input type="text" name="states[new_${index}][state_key]" class="form-control form-control-sm" placeholder="e.g. CUSTOM" required>
        </td>
        <td>
            <input type="text" name="states[new_${index}][label]" class="form-control form-control-sm" placeholder="e.g. Custom State" required>
        </td>
        <td>
            <input type="text" name="states[new_${index}][css_class]" class="form-control form-control-sm" placeholder="e.g. bg-dark" value="bg-secondary">
        </td>
        <td>
            <span class="badge bg-secondary">Preview</span>
        </td>
        <td>Custom</td>
    `;

    tbody.appendChild(row);
});
</script>
</body>
</html>

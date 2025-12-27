<?php
// /public/forms/meta-data-settings.php
// Passive view — no logic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Column Settings - EMS</title>
    <link href="/Assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/Assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <!-- Sidebar -->
    <?php //include __DIR__ . '/../html/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <!-- Topbar -->
            <?php //include __DIR__ . '/../html/topbar.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-xl mt-4">

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Configure Tool State Columns</h1>
                    <a href="/dashboard" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Customize the labels and descriptions for each column to match your organization's terminology.
                        </p>

                        <form method="POST" action="/meta-data-settings">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Column</th>
                                            <th>Label (Display Name)</th>
                                            <th>Description (Optional)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 1; $i <= 16; $i++): ?>
                                            <?php 
                                            $col = "col_$i";
                                            $data = $labels[$col][0] ?? ['label' => $col, 'description' => ''];
                                            ?>
                                            <tr>
                                                <td><code><?= $col ?></code></td>
                                                <td>
                                                    <input type="text" name="label_<?= $i ?>" class="form-control"
                                                           value="<?= htmlspecialchars($data['label']) ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="desc_<?= $i ?>" class="form-control"
                                                           value="<?= htmlspecialchars($data['description']) ?>">
                                                </td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
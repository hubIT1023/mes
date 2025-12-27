
<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Please log in first");
    exit;
}

// Extract tenant ID from session
$tenant_id = $_SESSION['tenant_id'] ?? null;
$tenant_name = $_SESSION['tenant_name'] ?? 'Unknown';

// Load header
include __DIR__ . '/../layouts/html/header.php';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metadata Customization - HubIT</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-sticky {
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-white text-slate-900 leading-normal">
<header class="sticky top-0 z-50 bg-white bg-opacity-90 backdrop-blur-sm border-b border-slate-200">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <a href="#" class="text-2xl font-bold text-brand-600">HubIT.online</a>
        <div class="hidden md:flex space-x-6">
            <a href="/mes/dashboard_admin" class="text-slate-600 hover:text-brand-600 transition-colors">Admin</a>
            <a href="/mes/signin" class="text-slate-600 hover:text-brand-600 transition-colors">Log-out</a>
        </div>
    </nav>
</header>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <!-- Sidebar Column -->
        <div class="col-md-3 col-lg-2 bg-light sidebar-sticky">
            <?php include __DIR__ . '/../layouts/html/sidebar_2.php'; ?>
        </div>

        <!-- Main Content Column -->
        <div class="col-md-9 col-lg-10">
            <h2 class="text-3xl font-bold text-center my-4">Metadata Customization</h2>

            <div id="wrapper">
                <div id="content-wrapper" class="d-flex flex-column bg-white">
                    <div class="container-fluid">
                        <!-- Alerts -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <div class="d-sm-flex align-items-center justify-content-between mb-3">
                            <div class="alert alert-info btn-sm mb-0">
                                Tenant ID: <?= htmlspecialchars($tenant_id, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>

                        <hr class="divider my-3">

                        <!-- Metadata Form -->
                        <div class="card shadow">
                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    Customize the labels and descriptions for each column to match your organization's terminology.
                                </p>

                                <form method="POST" action="/mes/meta-data-settings">
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
                                                <?php for ($i = 1; $i <= 16; $i++): 
                                                    $col = "col_$i";
                                                    $label = $labels[$col]['label'] ?? $col;
                                                    $desc = $labels[$col]['description'] ?? '';
                                                ?>
                                                    <tr>
                                                        <td><code><?= $col ?></code></td>
                                                        <td>
                                                            <input type="text" name="label_<?= $i ?>" class="form-control"
                                                                   value="<?= htmlspecialchars($label) ?>" required>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="desc_<?= $i ?>" class="form-control"
                                                                   value="<?= htmlspecialchars($desc) ?>">
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
    </div>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
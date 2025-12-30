<?php
// /app/views/dashboard_admin.php

// 1. Session & Auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['tenant_id'])) {
    header("Location: " . base_url('/signin') . "?error=Please log in first");
    exit;
}

// 2. CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Tenant Context
$tenant_id = $_SESSION['tenant_id'] ?? null;
$tenant_name = $_SESSION['tenant_name'] ?? 'Unknown';

// 4. Database Setup
require_once __DIR__ . '/../config/Database.php';
$conn = Database::getInstance()->getConnection();

// 5. Fetch Data from DB
$groups = fetchGroups($conn, $tenant_id);
$allPages = fetchAllPages($conn, $tenant_id);
$tenantAssets = fetchTenantAssets($conn, $tenant_id);

// 6. Organize Pages
$pages = [];
foreach ($allPages as $pageId => $pageName) {
    $pages[$pageId] = ['page_id' => (int)$pageId, 'page_name' => $pageName];
}

// 7. Determine Selected Page
$selectedPageId = determineSelectedPage($pages);
if ($selectedPageId !== null) {
    $_SESSION['last_page_id'] = $selectedPageId;
}

// 8. Filter Groups & Determine UI State
$selectedPageGroups = $selectedPageId !== null 
    ? array_filter($groups, fn($g) => (int)$g['page_id'] === $selectedPageId)
    : [];
$currentPageHasRealGroups = !empty($selectedPageGroups);
$showBlankCanvas = empty($pages) || !$currentPageHasRealGroups;
$selectedPageName = ($selectedPageId !== null && isset($pages[$selectedPageId]))
    ? $pages[$selectedPageId]['page_name']
    : 'Dashboard';

// 9. Initialize Mode Model
require_once __DIR__ . '/../models/ToolStateModel.php';
$modeModel = new ToolStateModel();
$modeChoices = $modeModel->getModeColorChoices($tenant_id);

// --- HELPER FUNCTIONS ---
function base_url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . $path;
}

// (Other helper functions: fetchGroups, fetchAllPages, fetchTenantAssets, determineSelectedPage)
// ... same as your original code
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HubIT Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar-sticky { position: sticky; top: 56px; height: calc(100vh - 56px); overflow-y: auto; }
        .blank-canvas-card {
            border: 4px dashed #cbd5e1;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .blank-canvas-card:hover { border-color: #3b82f6; background-color: #f8fafc; }
    </style>
</head>

<body class="bg-white text-slate-900">
    <header class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="<?= base_url('/') ?>" class="text-2xl font-bold text-blue-600">HubIT.online</a>
            <div class="flex space-x-4">
                <span class="text-slate-500">Tenant: <?= htmlspecialchars($tenant_id) ?></span>
                <a href="<?= base_url('/signin') ?>" class="text-slate-600">Log out</a>
            </div>
        </nav>
    </header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 bg-light sidebar-sticky p-0">
                <?php include __DIR__ . '/layouts/html/sidebar_2.php'; ?>
            </div>

            <main class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-2xl font-bold">Machine Status Board - <?= htmlspecialchars($selectedPageName) ?></h2>
                    
                    <?php if (!empty($pages)): ?>
                        <div class="d-flex gap-2">
                            <select class="form-select w-auto" onchange="location.href='?page_id='+this.value">
                                <?php foreach ($pages as $p): ?>
                                    <option value="<?= (int)$p['page_id'] ?>" <?= (int)$p['page_id'] == $selectedPageId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['page_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary" onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)">
                                <i class="fas fa-plus"></i> New Group
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['error']) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <hr class="mb-4">

                <?php if ($showBlankCanvas): ?>
                    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 50vh;">
                        <?php if (empty($pages)): ?>
                            <div class="blank-canvas-card p-5 text-center rounded-lg" data-bs-toggle="modal" data-bs-target="#createGroupPageModal" style="width: 300px;">
                                <i class="fas fa-file-circle-plus text-slate-300 fa-4x mb-3"></i>
                                <h5 class="text-slate-600">Create First Page</h5>
                            </div>
                        <?php else: ?>
                            <div class="blank-canvas-card p-5 text-center rounded-lg" onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)" style="width: 300px;">
                                <i class="fas fa-layer-group text-slate-300 fa-4x mb-3"></i>
                                <h5 class="text-slate-600">Add Group to <?= htmlspecialchars($selectedPageName) ?></h5>
                                <p class="small text-slate-400">Click to configure your first group for this page.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($selectedPageGroups as $g): ?>
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                                        <h5 class="mb-0"><?= htmlspecialchars($g['group_name']) ?> <small class="opacity-75 ms-2">| <?= htmlspecialchars($g['location_name']) ?></small></h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addEntityModal_<?= (int)$g['group_code'] ?>">
                                                <i class="fas fa-plus me-1"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="openUpdateGroupModal(
                                                <?= (int)$g['id'] ?>,
                                                <?= (int)$g['page_id'] ?>,
                                                '<?= addslashes($g['group_name']) ?>',
                                                '<?= addslashes($g['location_name']) ?>',
                                                <?= (int)($g['seq_id'] ?? 1) ?>
                                            )">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="openDeleteGroupModal(
                                                <?= (int)$g['id'] ?>,
                                                <?= (int)$g['page_id'] ?>,
                                                '<?= addslashes($g['group_name']) ?>'
                                            )">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success">
                                                <small class="ms-2"><?= (int)($g['seq_id'] ?? 1) ?></small>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body bg-slate-50">
                                        <?php 
                                            $group = $g;
                                            $org_id = $tenant_id;
                                            include __DIR__ . '/utilities/entity_toolState_card.php'; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- ✅ All modal form actions updated to use base_url() -->
    <form action="<?= base_url('/create-group') ?>" method="POST">...</form>
    <form action="<?= base_url('/update-group') ?>" method="POST">...</form>
    <form action="<?= base_url('/delete-group') ?>" method="POST">...</form>
    <form action="<?= base_url('/add-entity') ?>" method="POST">...</form>
    <form action="<?= base_url('/create-page') ?>" method="POST">...</form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openCreateGroupModal(pageId) {
            const pageInput = document.getElementById('modal_page_id');
            if (pageInput) pageInput.value = pageId;
            const myModal = new bootstrap.Modal(document.getElementById('createGroupModal'));
            myModal.show();
        }
        // ... other JS functions unchanged
    </script>
</body>
</html>

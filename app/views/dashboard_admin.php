<?php
// /app/views/dashboard_admin.php

// 1. Session & Auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['tenant_id'])) {
    header("Location: /mes/signin?error=Please log in first");
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
require_once __DIR__ . '/../models/ToolStateModel.php'; // Ensure model is included
$modeModel = new ToolStateModel();
$modeChoices = $modeModel->getModeColorChoices($tenant_id);

// --- HELPER FUNCTIONS ---
function fetchGroups($conn, $tenant_id): array {
    try {
        $stmt = $conn->prepare("
            SELECT id, group_code, location_code, group_name, location_name,
                   org_id, created_at, page_id, page_name, seq_id
            FROM group_location_map 
            WHERE org_id = ? AND group_name != '---'
            ORDER BY page_id, COALESCE(seq_id, 9999), created_at
        ");
        $stmt->execute([$tenant_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("DB error fetching groups: " . $e->getMessage());
        return [];
    }
}

function fetchAllPages($conn, $tenant_id): array {
    try {
        $stmt = $conn->prepare("
            SELECT DISTINCT page_id, page_name
            FROM group_location_map 
            WHERE org_id = ?
            ORDER BY page_id
        ");
        $stmt->execute([$tenant_id]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        error_log("DB error fetching pages: " . $e->getMessage());
        return [];
    }
}

function fetchTenantAssets($conn, $tenant_id): array {
    try {
        $stmt = $conn->prepare("SELECT asset_id, asset_name FROM assets WHERE tenant_id = ?");
        $stmt->execute([$tenant_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("DB error fetching tenant assets: " . $e->getMessage());
        return [];
    }
}

function determineSelectedPage(array $pages): ?int {
    if (isset($_GET['page_id']) && $_GET['page_id'] !== '') {
        return (int)$_GET['page_id'];
    }
    if (isset($_SESSION['last_page_id'])) {
        return (int)$_SESSION['last_page_id'];
    }
    return !empty($pages) ? (int)array_key_first($pages) : null;
}
// --- HELPER FUNCTIONS ---
function base_url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . $path;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HubIT Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <!-- Custom Styles -->
    <style>
        .blank-canvas-card {
            border: 4px dashed #cbd5e1;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .blank-canvas-card:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }

        /* Top Product Bar */
        .top-product-bar {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.5rem 0;
        }
        .product-item {
            text-align: center;
            text-decoration: none;
            color: #495057;
            font-size: 0.75rem;
        }
        .product-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #adb5bd;
            border-radius: 0.5rem;
            margin: 0 auto 0.3rem;
            color: #495057;
        }
        .product-item:hover .product-icon,
        .product-item:hover {
            color: #0d6efd;
        }

        /* Main Header */
        .main-header {
            background-color: white;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 0;
        }
        .main-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .btn-new-group {
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: 500;
        }
        .btn-new-group:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
    </style>
</head>

<body class="bg-white text-slate-900">

    <!-- Top Product & Info Bar -->
    <div class="top-product-bar">
        <div class="container-fluid d-flex justify-content-between align-items-center px-3">
            <!-- Product Icons -->
            <div class="d-flex gap-3">
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-server"></i></div> Gateways</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-database"></i></div> Data Loggers</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-wifi"></i></div> Sensors</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-router"></i></div> Routers</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-comment"></i></div> Displays</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-microchip"></i></div> Computing</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-chart-bar"></i></div> Data Visualisation</a>
                <a href="#" class="product-item"><div class="product-icon"><i class="fas fa-lightbulb"></i></div> Accessories</a>
            </div>

            <!-- Right Side: Links & Tenant Info -->
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="nav-link">About us</a>
                <a href="#" class="nav-link">Contact Us</a>
                <span class="text-muted small">Tenant: <?= htmlspecialchars($tenant_id) ?></span>
                <a href="/mes/signin" class="nav-link">Log out</a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="main-header">
        <div class="container-fluid d-flex justify-content-between align-items-center px-3">
            <h2>Machine Status Board - <?= htmlspecialchars($selectedPageName) ?></h2>
            <?php if (!empty($pages)): ?>
                <div class="d-flex gap-2">
                    <select class="form-select w-auto" onchange="location.href='?page_id='+this.value">
                        <?php foreach ($pages as $p): ?>
                            <option value="<?= (int)$p['page_id'] ?>" <?= (int)$p['page_id'] == $selectedPageId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['page_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-new-group" onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)">
                        <i class="fas fa-plus"></i> New Group
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 👇 YOUR EXISTING CONTENT BLOCK STARTS HERE (UNCHANGED) -->
    <div class="container-fluid">
        <div class="row">
            <!--div class="col-md-3 col-lg-2 bg-light sidebar-sticky p-0">
                <?php //include __DIR__ . '/layouts/html/sidebar_2.php'; ?>
            </div-->

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
                                            <!-- Add Entity -->
                                            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addEntityModal_<?= (int)$g['group_code'] ?>">
                                                <i class="fas fa-plus me-1"></i>
                                            </button>
                                            <!-- Update Group -->
                                            <button class="btn btn-sm btn-warning" onclick="openUpdateGroupModal(
                                                <?= (int)$g['id'] ?>,
                                                <?= (int)$g['page_id'] ?>,
                                                '<?= addslashes($g['group_name']) ?>',
                                                '<?= addslashes($g['location_name']) ?>',
                                                <?= (int)($g['seq_id'] ?? 1) ?>
                                            )">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <!-- Delete Group -->
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

    <!-- CREATE GROUP MODAL -->
    <div class="modal fade" id="createGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/mes/create-group" method="POST">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="modal_page_id" name="page_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Group Name</label>
                            <input type="text" class="form-control" name="group_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location Name</label>
                            <input type="text" class="form-control" name="location_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CREATE PAGE MODAL -->
    <div class="modal fade" id="createGroupPageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/mes/create-page" method="POST">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Page</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Page Name</label>
                            <input type="text" class="form-control" name="page_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Page</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- UPDATE GROUP MODAL -->
    <div class="modal fade" id="updateGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/mes/update-group" method="POST">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="update_group_id" name="group_id" value="">
                    <input type="hidden" id="update_page_id" name="page_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Group Name</label>
                            <input type="text" class="form-control" id="update_group_name" name="group_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location Name</label>
                            <input type="text" class="form-control" id="update_location_name" name="location_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sequence Order</label>
                            <input type="number" class="form-control" id="update_seq_id" name="seq_id" min="1" required>
                            <small class="form-text text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DELETE GROUP MODAL -->
    <div class="modal fade" id="deleteGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/mes/delete-group" method="POST">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="delete_group_id" name="group_id" value="">
                    <input type="hidden" id="delete_page_id" name="page_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete group "<span id="delete_group_name"></span>"? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Entity Modals -->
    <?php foreach ($groups as $g): ?>
        <div class="modal fade" id="addEntityModal_<?= (int)$g['group_code'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <form action="/mes/add-entity" method="POST">
                    <input type="hidden" name="group_code" value="<?= (int)$g['group_code'] ?>">
                    <input type="hidden" name="location_code" value="<?= (int)$g['location_code'] ?>">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Entity to <?= htmlspecialchars($g['group_name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Select Asset</label>
                                <select class="form-select" name="asset_id" required onchange="updateEntityName(this)">
                                    <option value="">-- Choose an asset --</option>
                                    <?php foreach ($tenantAssets as $asset): ?>
                                        <option value="<?= htmlspecialchars($asset['asset_id']) ?>"
                                                data-name="<?= htmlspecialchars($asset['asset_name']) ?>">
                                            <?= htmlspecialchars($asset['asset_id']) ?> - <?= htmlspecialchars($asset['asset_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Entity Name</label>
                                <input type="text" class="form-control" name="entity" readonly required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Entity</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script>
        function openCreateGroupModal(pageId) {
            const pageInput = document.getElementById('modal_page_id');
            if (pageInput) pageInput.value = pageId;
            const myModal = new bootstrap.Modal(document.getElementById('createGroupModal'));
            myModal.show();
        }

        function updateEntityName(select) {
            const name = select.options[select.selectedIndex]?.getAttribute('data-name') || '';
            const form = select.closest('form');
            form.querySelector('input[name="entity"]').value = name;
        }

        function openUpdateGroupModal(groupId, pageId, groupName, locationName, seqId) {
            document.getElementById('update_group_id').value = groupId;
            document.getElementById('update_page_id').value = pageId;
            document.getElementById('update_group_name').value = groupName;
            document.getElementById('update_location_name').value = locationName;
            document.getElementById('update_seq_id').value = seqId || 1;
            const modal = new bootstrap.Modal(document.getElementById('updateGroupModal'));
            modal.show();
        }

        function openDeleteGroupModal(groupId, pageId, groupName) {
            document.getElementById('delete_group_id').value = groupId;
            document.getElementById('delete_page_id').value = pageId;
            document.getElementById('delete_group_name').textContent = groupName;
            const modal = new bootstrap.Modal(document.getElementById('deleteGroupModal'));
            modal.show();
        }
    </script>
</body>
</html>
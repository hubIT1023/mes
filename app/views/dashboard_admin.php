<?php
// /app/views/dashboard_admin.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['tenant_id'])) {
    header("Location: /mes/signin?error=Please log in first");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tenant_id = $_SESSION['tenant_id'] ?? null;
$tenant_name = $_SESSION['tenant_name'] ?? 'Unknown';

require_once __DIR__ . '/../config/Database.php';
$conn = Database::getInstance()->getConnection();

$groups = fetchGroups($conn, $tenant_id);
$allPages = fetchAllPages($conn, $tenant_id);
$tenantAssets = fetchTenantAssets($conn, $tenant_id);

$pages = [];
foreach ($allPages as $pageId => $pageName) {
    $pages[(int)$pageId] = ['page_id' => (int)$pageId, 'page_name' => $pageName];
}

$selectedPageId = determineSelectedPage($pages);
if ($selectedPageId !== null) {
    $_SESSION['last_page_id'] = $selectedPageId;
}

$hasAnyPage = !empty($pages);

$selectedPageGroups = $selectedPageId !== null 
    ? array_filter($groups, fn($g) => (int)$g['page_id'] === $selectedPageId)
    : [];
$currentPageHasRealGroups = !empty($selectedPageGroups);
$showBlankCanvas = empty($pages) || !$currentPageHasRealGroups;
$selectedPageName = ($selectedPageId !== null && isset($pages[$selectedPageId]))
    ? $pages[$selectedPageId]['page_name']
    : 'Dashboard';

require_once __DIR__ . '/../models/ToolStateModel.php';
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
        $id = (int)$_GET['page_id'];
        return $id > 0 ? $id : null;
    }
    if (isset($_SESSION['last_page_id'])) {
        return (int)$_SESSION['last_page_id'];
    }
    return !empty($pages) ? (int)array_key_first($pages) : null;
}

$current_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function is_active($path, $current_page) {
    return $path === $current_page ? 'active' : '';
}

 //include __DIR__ . '/../layouts/html/header.php'; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HubIT Dashboard</title>
<link rel="icon" type="image/png" href="/app/Assets/img/favicon.png">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<body class="bg-light text-dark">

<header class="navbar navbar-expand-sm bg-white border-bottom shadow-sm py-3 px-4">
    <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
        <h1 class="navbar-brand fw-bold text-primary mb-0 fs-4">
            <?= htmlspecialchars($_SESSION['org_alias'] ?? $_SESSION['org_name'] ?? $tenant_name) ?>
        </h1>

        <nav class="d-flex gap-3">
            <a href="/mes/hub_portal" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-secondary bg-hover-light transition">
                <i class="fas fa-th-large me-2"></i>
                <span class="fw-medium">Hub Portal</span>
            </a>

            <a href="/mes/signout" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-danger bg-hover-danger-subtle transition">
                <i class="fas fa-power-off me-2"></i>
                <span class="fw-medium">Logout</span>
            </a>
        </nav>
    </div>
</header>

<div class="bg-body-tertiary border-bottom py-2 sticky-top">
    <div class="container-fluid">
        <div class="d-flex overflow-x-auto gap-4 pb-1 no-scrollbar">
            
            <a href="/mes/mode-color" class="text-center text-decoration-none group <?= is_active('/mes/mode-color', $current_page) ? 'text-primary' : 'text-secondary' ?>" style="min-width: 80px;">
                <div class="mx-auto mb-1 border rounded-3 bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; transition: 0.2s;">
                    <i class="fas fa-palette"></i>
                </div>
                <div style="font-size: 0.75rem;" class="fw-medium">Mode Colors</div>
            </a>

            <a href="/mes/parts-list" class="text-center text-decoration-none text-secondary group" style="min-width: 80px;">
                <div class="mx-auto mb-1 border rounded-3 bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                    <i class="fas fa-gears"></i>
                </div>
                <div style="font-size: 0.75rem;" class="fw-medium">Machine Parts</div>
            </a>

            <a href="#" class="text-center text-decoration-none text-primary group" style="min-width: 80px;" onclick="openDashboardPageModal(<?= json_encode($selectedPageId) ?>)">
                <div class="mx-auto mb-1 border border-primary-subtle rounded-3 bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div style="font-size: 0.75rem;" class="fw-medium">Dashboard Pages</div>
            </a>

        </div>
    </div>
</div>

<main class="container-fluid p-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h2 class="h4 fw-bold mb-0">
            Machine Status Board <span class="text-secondary fw-normal">| <?= htmlspecialchars($selectedPageName) ?></span>
        </h2>

        <?php if (!empty($pages)): ?>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm w-auto shadow-sm" onchange="location.href='?page_id='+this.value">
                    <?php foreach ($pages as $p): ?>
                        <option value="<?= (int)$p['page_id'] ?>" <?= (int)$p['page_id'] == $selectedPageId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['page_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)">
                    <i class="fas fa-plus me-1"></i> New Group
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if ($showBlankCanvas): ?>
	
        <div class="d-flex align-items-center justify-content-center border border-2 border-dashed rounded-4 bg-body-tertiary" style="min-height: 400px;">
            <div class="text-center p-5" style="max-width: 400px; cursor: pointer;" 
                 onclick="<?= empty($pages) ? 'openDashboardPageModal(null)' : 'openCreateGroupModal(' . (int)$selectedPageId . ')' ?>">
                
                <div class="mb-3 text-secondary opacity-25">
                    <i class="fas <?= empty($pages) ? 'fa-file-circle-plus' : 'fa-layer-group' ?> fa-5x"></i>
                </div>
                <h5 class="fw-bold"><?= empty($pages) ? 'Create First Page' : 'Add Group to ' . htmlspecialchars($selectedPageName) ?></h5>
                <p class="text-muted small">Organize your factory floor by grouping machines into logical areas or lines.</p>
                <button class="btn btn-outline-primary btn-sm mt-2 px-4">Get Started</button>
            </div>
        </div>
    <?php else: ?>
        
        <div class="row g-4">
            <?php foreach ($selectedPageGroups as $g): ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3 border-0">
                            <h5 class="mb-0 fw-bold">
                                <?= htmlspecialchars($g['group_name']) ?> 
                                <span class="badge bg-white bg-opacity-25 ms-2 fw-normal fs-6"><?= htmlspecialchars($g['location_name']) ?></span>
                            </h5>
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addEntityModal_<?= (int)$g['group_code'] ?>">
                                    <i class="fas fa-plus text-primary"></i>
                                </button>
                                <button class="btn btn-light btn-sm" onclick="openUpdateGroupModal(<?= (int)$g['id'] ?>, <?= (int)$g['page_id'] ?>, '<?= addslashes($g['group_name']) ?>', '<?= addslashes($g['location_name']) ?>', <?= (int)($g['seq_id'] ?? 1) ?>)">
                                    <i class="fas fa-edit text-warning"></i>
                                </button>
                                <button class="btn btn-light btn-sm" onclick="openDeleteGroupModal(<?= (int)$g['id'] ?>, <?= (int)$g['page_id'] ?>, '<?= addslashes($g['group_name']) ?>')">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                                <span class="btn btn-light btn-sm disabled fw-bold border-start border-secondary-subtle">
                                    #<?= (int)($g['seq_id'] ?? 1) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body bg-white p-4">
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

<!-- MODALS -->
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

<!-- DASHBOARD PAGE MANAGER MODAL -->
<div class="modal fade" id="dashboardPageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="dashboardPageForm" method="POST">
                <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select class="form-select" id="pageAction" required>
                            <option value="create">Create New Page</option>
                            <option value="rename" <?= $hasAnyPage ? '' : 'disabled' ?>>Rename Page</option>
                            <option value="delete" <?= $hasAnyPage ? '' : 'disabled' ?>>Delete Page</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="pageSelectorField">
                        <label class="form-label">Select Page</label>
                        <select class="form-select" id="pageSelector" name="page_id">
                            <?php foreach ($pages as $p): ?>
                                <option value="<?= (int)$p['page_id'] ?>" <?= (int)$p['page_id'] == ($selectedPageId ?? 0) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['page_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="pageNameField">
                        <label class="form-label">Page Name</label>
                        <input type="text" class="form-control" id="pageNameInput" name="page_name" maxlength="100">
                    </div>

                    <div class="alert alert-warning d-none" id="deleteWarning">
                        <strong>Warning:</strong> This will permanently delete the page and all its groups and entities. Cannot be undone.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Create Page</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Other modals (Update/Delete Group, Add Entity) remain unchanged -->

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
                    <p>Are you sure you want to delete group "<span id="delete_group_name"></span>" and all its entities? This action cannot be undone.</p>
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

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openDashboardPageModal(currentPageId) {
    const modalEl = document.getElementById('dashboardPageModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('dashboardPageForm');
    const actionSelect = document.getElementById('pageAction');
    const pageSelector = document.getElementById('pageSelector');
    const pageNameInput = document.getElementById('pageNameInput');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const deleteWarning = document.getElementById('deleteWarning');
    const pageNameField = document.getElementById('pageNameField');
    const pageSelectorField = document.getElementById('pageSelectorField');

    form.reset();
    if (pageSelector && currentPageId && pageSelector.querySelector(`option[value="${currentPageId}"]`)) {
        pageSelector.value = currentPageId;
    }

    const updatePageName = () => {
        if (actionSelect.value === 'rename' && pageSelector) {
            pageNameInput.value = pageSelector.options[pageSelector.selectedIndex]?.text || '';
        }
    };

    const updateModalUI = () => {
        const action = actionSelect.value;
        let title, btnText, actionUrl;
        let isDelete = false, showPageSelector = false;

        switch(action) {
            case 'create':
                title = "Create New Page";
                btnText = "Create Page";
                actionUrl = "/mes/create-page";
                pageNameInput.required = true;
                break;
            case 'rename':
                title = "Rename Page";
                btnText = "Rename Page";
                actionUrl = "/mes/rename-page";
                pageNameInput.required = true;
                showPageSelector = true;
                updatePageName();
                break;
            case 'delete':
                title = "Delete Page";
                btnText = "Delete Page";
                actionUrl = "/mes/delete-page";
                pageNameInput.required = false;
                isDelete = true;
                showPageSelector = true;
                break;
        }

        modalTitle.textContent = title;
        submitBtn.textContent = btnText;
        submitBtn.className = `btn ${isDelete ? 'btn-danger' : 'btn-primary'}`;
        form.action = actionUrl;

        pageNameField.classList.toggle('d-none', isDelete);
        deleteWarning.classList.toggle('d-none', !isDelete);
        pageSelectorField.classList.toggle('d-none', !showPageSelector);
    };

    actionSelect.onchange = updateModalUI;
    if (pageSelector) pageSelector.onchange = updatePageName;
    updateModalUI();
    modal.show();
}

function openCreateGroupModal(pageId) {
    document.getElementById('modal_page_id').value = pageId;
    new bootstrap.Modal(document.getElementById('createGroupModal')).show();
}

function updateEntityName(select) {
    const name = select.options[select.selectedIndex]?.getAttribute('data-name') || '';
    select.closest('form').querySelector('input[name="entity"]').value = name;
}

function openUpdateGroupModal(groupId, pageId, groupName, locationName, seqId) {
    document.getElementById('update_group_id').value = groupId;
    document.getElementById('update_page_id').value = pageId;
    document.getElementById('update_group_name').value = groupName;
    document.getElementById('update_location_name').value = locationName;
    document.getElementById('update_seq_id').value = seqId || 1;
    new bootstrap.Modal(document.getElementById('updateGroupModal')).show();
}

function openDeleteGroupModal(groupId, pageId, groupName) {
    document.getElementById('delete_group_id').value = groupId;
    document.getElementById('delete_page_id').value = pageId;
    document.getElementById('delete_group_name').textContent = groupName;
    new bootstrap.Modal(document.getElementById('deleteGroupModal')).show();
}
</script>
</body>
</html>
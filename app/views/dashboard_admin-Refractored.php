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

<style>
.top-product-bar {
    background:#f8f9fa;
    border-bottom:1px solid #dee2e6;
    padding:.4rem 0;
    position:sticky;
    top:0;
    z-index:1050;
}
.product-list {
    display:flex;
    gap:1rem;
    overflow-x:auto;
}
.product-item {
    min-width:64px;
    font-size:.7rem;
    text-decoration:none;
    color:#495057;
    display:flex;
    flex-direction:column;
    align-items:center;
}
.product-icon {
    width:38px;height:38px;
    border:1px solid #adb5bd;
    border-radius:.5rem;
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
}
.product-item:hover,
.product-item:hover .product-icon {
    color:#0d6efd;
    border-color:#0d6efd;
}
@media(max-width:576px){ .product-item span{display:none;} }
</style>
</head>

<body class="bg-white text-slate-900">

<!-- HEADER -->
<header class="sticky top-[3px] z-12 bg-white border-b border-slate-200 shadow-sm">
    <nav class="navbar navbar-expand navbar-light bg-white border-bottom py-2">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4 text-primary" href="#">HubIT.online</a>
            <div class="d-flex align-items-center gap-3">
                <a href="/mes/hub_portal" class="text-decoration-none text-primary small">Hub Portal</a>
                <a href="/mes/signout" class="text-decoration-none text-primary small">Log out</a>
            </div>
        </div>
    </nav>
</header>

<!-- DEBUG: Force visible output -->
<div class="container">
    <div class="alert alert-info">
        DEBUG: 
        Pages count: <?= count($pages) ?>, 
        Selected Page ID: <?= $selectedPageId ?>, 
        Groups count: <?= count($selectedPageGroups) ?>, 
        Show Blank Canvas: <?= $showBlankCanvas ? 'YES' : 'NO' ?>
    </div>
</div>

<!-- TOP PRODUCT BAR -->
<div class="top-product-bar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="product-list">
            <a href="/mes/mode-color" class="product-item d-flex flex-column align-items-center text-decoration-none <?= is_active('/mes/mode-color') ? 'text-primary' : 'text-secondary' ?>">
                <div class="product-icon d-flex align-items-center justify-content-center mb-1 border rounded p-2">
                    <i class="fas fa-palette fa-lg"></i>
                </div>
                <span class="small">Mode Colors</span>
            </a>
            <a href="/mes/parts-list" class="product-item d-flex flex-column align-items-center text-decoration-none <?= is_active('/mes/parts-list') ? 'text-primary' : 'text-secondary' ?>">
                <div class="product-icon d-flex align-items-center justify-content-center mb-1 border rounded p-2">
                    <i class="fas fa-fw fa-gears"></i>
                </div>
                <span class="small">Machine Parts</span>
            </a>
            <a href="#" class="product-item d-flex flex-column align-items-center text-decoration-none text-primary" 
               onclick="openDashboardPageModal(<?= json_encode($selectedPageId) ?>)">
                <div class="product-icon d-flex align-items-center justify-content-center mb-1 border rounded p-2">
                    <i class="fas fa-fw fa-plus-circle"></i>
                </div>
                <span class="small">Dashboard Pages</span>
            </a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <main class="col-12 p-4"> 
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h2 class="text-2xl font-bold">
                    Machine Status Board - <?= htmlspecialchars($selectedPageName) ?>
                </h2>

                <?php if (!empty($pages)): ?>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <select 
                            class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto"
                            onchange="location.href='?page_id='+this.value"
                        >
                            <?php foreach ($pages as $p): ?>
                                <option value="<?= (int)$p['page_id'] ?>" <?= (int)$p['page_id'] == $selectedPageId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['page_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium text-sm w-full sm:w-auto flex items-center justify-center gap-1"
                            onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)"
                        >
                            <i class="fas fa-plus text-xs"></i> New Group
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
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 50vh; background-color: #f8f9fa;">
                    <?php if (empty($pages)): ?>
                        <div 
                            onclick="openDashboardPageModal(null)" 
                            style="width: 300px; padding: 30px; text-align: center; background: white; border: 2px dashed #ccc; border-radius: 10px; cursor: pointer;"
                        >
                            <div style="font-size: 48px; margin-bottom: 16px;">📁</div>
                            <h5 style="color: #495057; margin: 0;">Create Your First Dashboard Page</h5>
                            <p style="color: #6c757d; font-size: 14px; margin-top: 8px;">Click here to get started</p>
                        </div>
                    <?php else: ?>
                        <div 
                            onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)" 
                            style="width: 300px; padding: 30px; text-align: center; background: white; border: 2px dashed #ccc; border-radius: 10px; cursor: pointer;"
                        >
                            <div style="font-size: 48px; margin-bottom: 16px;">🧩</div>
                            <h5 style="color: #495057; margin: 0;">Add Group to <?= htmlspecialchars($selectedPageName) ?></h5>
                            <p style="color: #6c757d; font-size: 14px; margin-top: 8px;">Click to configure your first group</p>
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

<!-- MODALS -->
<?php include __DIR__ . '/modals/create_group_modal.php'; ?>
<?php include __DIR__ . '/modals/update_group_modal.php'; ?>
<?php include __DIR__ . '/modals/delete_group_modal.php'; ?>
<?php include __DIR__ . '/modals/dashboard_page_modal.php'; ?>

<?php foreach ($groups as $group): ?>
    <?php include __DIR__ . '/modals/add_entity_modal.php'; ?>
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
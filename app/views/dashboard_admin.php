<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HubIT Dashboard</title>

    <!-- Bootstrap CSS (clean CDN, no trailing spaces) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />

    <!-- Font Awesome (updated version, clean URL) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <!-- Custom Styles -->
    <style>
        .blank-canvas-card {
            border: 4px dashed #ced4da;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .blank-canvas-card:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="bg-white text-dark">
    <!-- Topbar -->
    <header class="sticky-top bg-white border-bottom shadow-sm">
        <nav class="navbar navbar-expand-lg navbar-light container-fluid px-3 py-2">
            <a class="navbar-brand me-3" href="/mes/dashboard_admin">
                <img src="/Assets/img/hubIT_logo-v2.png" alt="HubIT Logo" style="max-height: 40px;" />
            </a>

            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#topbarNav"
                aria-controls="topbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="topbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= is_active('/mes/dashboard_admin', $current_page) ?>" href="/mes/dashboard_admin">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="assetsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-boxes me-1"></i> Assets
                        </a>
                        <ul class="dropdown-menu shadow border-0" aria-labelledby="assetsDropdown">
                            <li><a class="dropdown-item" href="/mes/assets-list">Asset List</a></li>
                            <li><a class="dropdown-item" href="/mes/add-assets">Add Assets</a></li>
                            <li><a class="dropdown-item" href="/mes/manage-checklist-templates">Checklist Templates</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="maintDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-tools me-1"></i> Maintenance
                        </a>
                        <ul class="dropdown-menu shadow border-0" aria-labelledby="maintDropdown">
                            <?php
                            $maintenance_items = [
                                ['/mes/registered_assets', 'fa-calendar-check', 'Schedule'],
                                ['/mes/incoming-maintenance', 'fa-tools', 'Incoming'],
                                ['/mes/completed-work-orders', 'fa-check-circle', 'Completed Orders']
                            ];
                            foreach ($maintenance_items as $item): ?>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($item[0]) ?>"><?= htmlspecialchars($item[2]) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="configDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i> Config
                        </a>
                        <ul class="dropdown-menu shadow border-0" aria-labelledby="configDropdown">
                            <li class="dropdown-header text-uppercase small fw-bold">Database</li>
                            <li><a class="dropdown-item" href="/mes/meta-database">Configure DB</a></li>
                            <li><a class="dropdown-item" href="/mes/tool-state-log">Tool Status Log</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><a class="dropdown-item" href="/mes/mode-color">Mode Colors</a></li>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm rounded-pill px-3" href="#"
                            data-bs-toggle="modal" data-bs-target="#createGroupPageModal">
                            <i class="fas fa-plus-circle me-1"></i> Create Page
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container-fluid py-4">
        <div class="row">
            <main class="col-md-12 p-0 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 fw-bold">Machine Status Board - <?= htmlspecialchars($selectedPageName) ?></h2>

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

                <hr class="mb-4" />

                <?php if ($showBlankCanvas): ?>
                    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 50vh;">
                        <?php if (empty($pages)): ?>
                            <div class="blank-canvas-card p-5 text-center rounded-3" data-bs-toggle="modal" data-bs-target="#createGroupPageModal" style="width: 300px;">
                                <i class="fas fa-file-circle-plus text-secondary fa-4x mb-3"></i>
                                <h5 class="text-muted">Create First Page</h5>
                            </div>
                        <?php else: ?>
                            <div class="blank-canvas-card p-5 text-center rounded-3" onclick="openCreateGroupModal(<?= (int)$selectedPageId ?>)" style="width: 300px;">
                                <i class="fas fa-layer-group text-secondary fa-4x mb-3"></i>
                                <h5 class="text-muted">Add Group to <?= htmlspecialchars($selectedPageName) ?></h5>
                                <p class="text-muted small">Click to configure your first group for this page.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($selectedPageGroups as $g): ?>
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                                        <h5 class="mb-0">
                                            <?= htmlspecialchars($g['group_name']) ?>
                                            <small class="opacity-75 ms-2">| <?= htmlspecialchars($g['location_name']) ?></small>
                                        </h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addEntityModal_<?= (int)$g['group_code'] ?>">
                                                <i class="fas fa-plus"></i>
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
                                    <div class="card-body bg-light">
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

    <!-- Bootstrap JS (with integrity & crossorigin) -->
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
            const entityInput = form.querySelector('input[name="entity"]');
            if (entityInput) entityInput.value = name;
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
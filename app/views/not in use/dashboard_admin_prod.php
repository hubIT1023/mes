
<?php
// /app/views/dashboard_admin.php

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

// Initialize Database connection
require_once __DIR__ . '/../config/Database.php';
$conn = Database::getInstance()->getConnection();

// Fetch groups for this tenant
$groups = [];
try {
    $stmt = $conn->prepare("SELECT * FROM group_location_map WHERE org_id = ? ORDER BY group_name");
    $stmt->execute([$tenant_id]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching groups: " . $e->getMessage());
    $groups = [];
}

// Fetch all assets for this tenant (used in modals)
try {
    $assetStmt = $conn->prepare("SELECT asset_id, asset_name FROM assets WHERE tenant_id = ?");
    $assetStmt->execute([$tenant_id]);
    $tenantAssets = $assetStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tenantAssets = [];
}

// Load header
//include __DIR__ . '../layouts/html/header.php';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub It Dashboard - Live Demo</title>

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
        .asset-card { transition: all 0.2s ease-in-out; }
        .asset-card:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); transform: translateY(-2px); }

        /* Sticky sidebar */
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
            <a href="#login" class="text-slate-600 hover:text-brand-600 transition-colors">Log-in</a>
        </div>
    </nav>
</header>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">

        <!-- Sidebar Column -->
        <div class="col-md-3 col-lg-2 bg-light sidebar-sticky">
            <?php include __DIR__ . '../layouts/html/sidebar_2.php'; ?>
        </div>

        <!-- Main Content Column -->
        <div class="col-md-9 col-lg-10">
            <h2 class="text-3xl font-bold text-center my-4">Business Intelligence Dashboard</h2>

            <div id="wrapper">
                <div id="content-wrapper" class="d-flex flex-column bg-white">
                    <div class="container-fluid">

                        <!-- Success Alert -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <!-- Dashboard Header -->
                        <div class="d-sm-flex align-items-center justify-content-between mb-3">
                            <div class="alert alert-info btn-sm mb-0">
                                Tenant ID: <?= htmlspecialchars($tenant_id, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                                + New Group
                            </button>
                        </div>

                        <hr class="divider my-3">

                        <!-- Groups List -->
                        <?php if (empty($groups)): ?>
                            <div class="alert alert-info">
                                <p>No groups created yet.</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                                    Create Your First Group
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groups as $g): ?>
                                <!-- Group Card -->
                                <div class="card mt-4">
                                    <!-- Group Header -->
                                    <div class="d-flex align-items-center justify-content-between p-3" style="background-color:#426ff5; color:#fcfdff;">
                                        <div>
                                            <h5 class="mb-0"><?= htmlspecialchars($g['group_name']) ?></h5>
                                            <small>
                                                GC: <?= (int)$g['group_code'] ?> | 
                                                LC: <?= (int)$g['location_code'] ?> | 
                                                Location: <?= htmlspecialchars($g['location_name']) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            
                                            <!--button class="btn btn-sm btn-light text-danger me-1" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button-->
                                            
											<button class="btn btn-sm btn-success me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#addEntityModal_<?= (int)$g['group_code'] ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
											
											<button class="btn btn-sm btn-light text-primary me-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Entities Grid -->
                                    <div class="card-body">
                                        <div class="grid-container" style="display: grid; grid-template-columns: repeat(9, 1fr); gap: 1rem;">
                                            <?php
                                   
											
											$modeModel = new ToolStateModel();
											$modeChoices = $modeModel->getModeColorChoices($tenant_id);

											// Pass to card
											$group = $g;
											$org_id = $tenant_id;
											include __DIR__ . '/utilities/entity_toolState_card.php';
											
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add Entity Modal (inside loop) -->
                                <div class="modal fade" id="addEntityModal_<?= (int)$g['group_code'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="/mes/add-entity" method="POST">
                                            <input type="hidden" name="group_code" value="<?= (int)$g['group_code'] ?>">
                                            <input type="hidden" name="location_code" value="<?= (int)$g['location_code'] ?>">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Add Entity to <?= htmlspecialchars($g['group_name']) ?> Group</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Asset Selection -->
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

                                                    <!-- Entity Name (auto-filled) -->
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
                        <?php endif; ?>
                    </div> <!-- /.container-fluid -->
                </div> <!-- /.content-wrapper -->
            </div> <!-- /#wrapper -->
        </div> <!-- /.col-md-9 col-lg-10 -->
    </div> <!-- /.row -->
</div> <!-- /.container-fluid -->

<!-- Create Group Modal -->
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/mes/create-group" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="group_name" class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="group_name" name="group_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="location_name" class="form-label">Location Name</label>
                        <input type="text" class="form-control" id="location_name" name="location_name" required>
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






<?php include __DIR__ . '/layouts/html/footer.php'; ?>

<script>
function updateEntityName(select) {
    const selectedOption = select.options[select.selectedIndex];
    const entityName = selectedOption.getAttribute('data-name') || '';
    select.closest('form').querySelector('input[name="entity"]').value = entityName;
}
</script>



</body>
</html>
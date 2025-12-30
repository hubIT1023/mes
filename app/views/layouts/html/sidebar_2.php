<?php
// Helper to detect active page
$current_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function is_active($path, $current_page) {
    return $path === $current_page ? 'active' : '';
}
?>

<ul class="navbar-nav sidebar accordion bg-white border-end shadow-sm" id="accordionSidebar">

    <div class="sidebar-brand-container d-flex align-items-center justify-content-center py-4">
        <a class="sidebar-brand text-decoration-none" href="/mes/dashboard_admin">
            <img src="../../../Assets/img/hubIT_logo-v2.png"alt="hubIT Logo"class="img-fluid px-3"style="max-height: 50px;" />
        </a>
    </div>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= is_active('/mes/dashboard_admin', $current_page) ?>">
        <a class="nav-link" href="/mes/dashboard_admin">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Admin Dashboard</span>
        </a>
    </li>

    <div class="sidebar-heading">Asset Management</div>
    
    <li class="nav-item <?= is_active('/mes/assets-list', $current_page) ?>">
        <a class="nav-link" href="/mes/assets-list">
            <i class="fas fa-fw fa-boxes"></i>
            <span>Asset List</span>
        </a>
    </li>

    <li class="nav-item <?= is_active('/mes/add-assets', $current_page) ?>">
        <a class="nav-link" href="/mes/add-assets">
            <i class="fas fa-fw fa-plus-square"></i>
            <span>Add Assets</span>
        </a>
    </li>

    <li class="nav-item <?= is_active('/mes/manage-checklist-templates', $current_page) ?>">
        <a class="nav-link" href="/mes/manage-checklist-templates">
            <i class="fas fa-fw fa-tasks"></i>
            <span>Checklist Templates</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Maintenance</div>

    <?php
    $maintenance_items = [
        ['/mes/registered_assets', 'fa-calendar-check', 'Schedule'],
        ['/mes/incoming-maintenance', 'fa-tools', 'Incoming'],
        ['/mes/completed-work-orders', 'fa-check-circle', 'Completed Orders']
    ];
    foreach ($maintenance_items as $item): ?>
        <li class="nav-item <?= is_active($item[0], $current_page) ?>">
            <a class="nav-link" href="<?= $item[0] ?>">
                <i class="fas fa-fw <?= $item[1] ?>"></i>
                <span><?= $item[2] ?></span>
            </a>
        </li>
    <?php endforeach; ?>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">System Config</div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseDatabase"
           aria-expanded="false" aria-controls="collapseDatabase">
            <i class="fas fa-fw fa-database"></i>
            <span>Database</span>
        </a>
        <div id="collapseDatabase" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded mt-2">
                <a class="collapse-item" href="/mes/meta-database">Configure DB</a>
                <a class="collapse-item" href="/mes/tool-state-log">Tool Status Log</a>
            </div>
        </div>
    </li>

    <li class="nav-item <?= is_active('/mes/mode-color', $current_page) ?>">
        <a class="nav-link" href="/mes/mode-color">
            <i class="fas fa-fw fa-palette"></i>
            <span>Mode Colors</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Quick Actions</div>
    
    <li class="nav-item">
        <a class="nav-link text-primary" href="#" data-bs-toggle="modal" data-bs-target="#createGroupPageModal">
            <i class="fas fa-fw fa-plus-circle"></i>
            <span>Create New Page</span>
        </a>
    </li>

    <div class="text-center d-none d-md-inline mt-4 pt-3 border-top">
        <button class="btn btn-sm btn-light rounded-circle border" id="sidebarToggle">
            <i class="fas fa-angle-left"></i>
        </button>
    </div>
</ul>

<style>
    /* Sidebar Base */
    .sidebar {
        min-height: 100vh;
        width: 14rem !important;
    }
    .sidebar .nav-item .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #4b5563;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .sidebar .nav-item .nav-link i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
        color: #9ca3af;
    }
    
    /* Hover & Active States */
    .sidebar .nav-item:hover .nav-link,
    .sidebar .nav-item.active .nav-link {
        color: #2563eb;
        background-color: #eff6ff;
    }
    .sidebar .nav-item.active .nav-link i {
        color: #2563eb;
    }

    /* Heading & Dividers */
    .sidebar-heading {
        padding: 1.5rem 1rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #9ca3af;
        letter-spacing: 0.05rem;
    }
    .sidebar-divider {
        margin: 1rem 0;
        border-top: 1px solid #e5e7eb;
    }

    /* Collapse Inner Items */
    .collapse-inner .collapse-item {
        display: block;
        padding: 0.5rem 1rem 0.5rem 2.8rem;
        font-size: 0.8rem;
        color: #4b5563;
        text-decoration: none;
    }
    .collapse-inner .collapse-item:hover {
        color: #2563eb;
    }
</style>

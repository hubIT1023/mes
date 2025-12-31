<!-- Topbar -->

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="/mes/dashboard_admin">
            <img src="/../../assets/img/hubIT_logo-v2.png" alt="Logo" style="max-height: 40px;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav">
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
                            <li><a class="dropdown-item" href="<?= $item[0] ?>"><?= $item[2] ?></a></li>
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
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/mes/mode-color">Mode Colors</a></li>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm rounded-pill px-3" href="#" data-bs-toggle="modal" data-bs-target="#createGroupPageModal">
                        <i class="fas fa-plus-circle me-1"></i> Create Page
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

/* Topbar Refinement */
.navbar-nav .nav-link {
    font-size: 0.9rem;
    font-weight: 500;
    color: #4b5563 !important;
    padding: 0.5rem 1rem !important;
    transition: color 0.2s ease;
}

.navbar-nav .nav-link:hover, 
.navbar-nav .nav-link.active {
    color: #2563eb !important;
}

.navbar-nav .nav-link i {
    color: #9ca3af;
}

/* Elegant Dropdowns */
.dropdown-menu {
    border-radius: 0.75rem;
    padding: 0.5rem;
    margin-top: 10px !important;
}

.dropdown-item {
    font-size: 0.85rem;
    padding: 0.6rem 1rem;
    border-radius: 0.5rem;
    color: #4b5563;
}

.dropdown-item:hover {
    background-color: #eff6ff;
    color: #2563eb;
}

.dropdown-header {
    font-size: 0.7rem;
    letter-spacing: 0.05rem;
    color: #9ca3af;
}

<!-- End of Topbar -->
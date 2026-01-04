<?php
// /app/controllers/AdminDashboardController.php
//an update

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/url.php'; // ✅ Keep as procedural
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/PageModel.php';
require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/AssetModel.php';
require_once __DIR__ . '/../models/ToolStateModel.php';

class AdminDashboardController
{
    public function index(): void
    {
        AuthMiddleware::checkAuth();

        $tenant_id = AuthMiddleware::getTenantId();
        $tenant_name = AuthMiddleware::getTenantName();

        $conn = Database::getInstance()->getConnection();

        $groups = GroupModel::fetchGroups($conn, $tenant_id);
        $allPages = PageModel::fetchAllPages($conn, $tenant_id);
        $tenantAssets = AssetModel::fetchTenantAssets($conn, $tenant_id);

        $pages = [];
        foreach ($allPages as $pageId => $pageName) {
            $pages[(int)$pageId] = ['page_id' => (int)$pageId, 'page_name' => $pageName];
        }

        // ✅ Call global function directly
        $selectedPageId = determineSelectedPage($pages);
        if ($selectedPageId !== null) {
            $_SESSION['last_page_id'] = $selectedPageId;
        }

        $hasAnyPage = !empty($pages);
        $selectedPageGroups = $selectedPageId
            ? array_filter($groups, fn($g) => (int)$g['page_id'] === $selectedPageId)
            : [];
        $currentPageHasRealGroups = !empty($selectedPageGroups);
        $showBlankCanvas = empty($pages) || !$currentPageHasRealGroups;
        $selectedPageName = ($selectedPageId && isset($pages[$selectedPageId]))
            ? $pages[$selectedPageId]['page_name']
            : 'Dashboard';

        $modeModel = new ToolStateModel();
        $modeChoices = $modeModel->getModeColorChoices($tenant_id);

        // Make all vars available in view
        require_once __DIR__ . '/../views/dashboard_admin.php';
    }
}
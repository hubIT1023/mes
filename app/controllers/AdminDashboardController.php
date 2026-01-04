<?php
// /app/controllers/AdminDashboardController.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ... rest of your code
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/url.php';
require_once __DIR__ . '/../config/Database.php';

// ✅ FIXED: All models are in /app/models/, so use ../models/
require_once __DIR__ . '/../models/FetchPageModel.php';
require_once __DIR__ . '/../models/FetchGroupModel.php';
require_once __DIR__ . '/../models/FetchAssetModel.php';
require_once __DIR__ . '/../models/ToolStateModel.php';

class AdminDashboardController
{
    public function index(): void
    {
        AuthMiddleware::checkAuth();

        $tenant_id = AuthMiddleware::getTenantId();
        $tenant_name = AuthMiddleware::getTenantName();

        $conn = Database::getInstance()->getConnection();

        $groups = FetchGroupModel::fetchGroups($conn, $tenant_id);
        $allPages = FetchPageModel::fetchAllPages($conn, $tenant_id);
        $tenantAssets = FetchAssetModel::fetchTenantAssets($conn, $tenant_id);

        $pages = [];
        foreach ($allPages as $pageId => $pageName) {
            $pages[(int)$pageId] = ['page_id' => (int)$pageId, 'page_name' => $pageName];
        }

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

        require_once __DIR__ . '/../views/dashboard_admin.php';
    }
}
<?php
// routes.php

// --- Auth Routes ---
$authRoutes = [
    'GET /register'  => ['RegisterController', 'register'],
    'POST /register' => ['RegisterController', 'submit'],

    'GET /signin'    => ['SigninController', 'signin'],
    'POST /signin'   => ['SigninController', 'authenticate'],
    'GET /signout'   => ['SigninController', 'signout'],

    'GET /hub_portal' => ['SigninController', 'hubPortal'],
];

// --- Static Pages ---
$staticPages = [
    'GET /'                 => ['PagesController', 'welcome'],
    'GET /welcome'          => ['PagesController', 'welcome'],
    'GET /about'            => ['PagesController', 'about'],
    'GET /canvas'           => ['PagesController', 'canvas'],
    'GET /mms_admin'        => ['PagesController', 'mms_Admin'],
    'GET /dashboard_admin'  => ['PagesController', 'Dashboard_Admin'],
];


// --- Business Intelligence ---
$biRoutes = [
    
    'POST /create-page'            => ['GroupPageController', 'store'],
    'POST /mes/rename-page'  => ['GroupPageController', 'rename'],
    'POST /delete-page'  => ['GroupPageController', 'destroy'],
	'POST /create-group'           => ['CreateGroupController', 'handleCreateGroup'],
    'POST /update-group'           => ['UpdateGroupController', 'handleUpdate'],
    'POST /delete-group'           => ['DeleteGroupController', 'handleDelete'],
    'POST /add-entity'             => ['AddEntityToDashboardController', 'handleAddEntity'],
	'POST /update-entity-position' => ['UpdateEntityPositionController', 'handleUpdate'],
    'POST /change-tool-state'      => ['ToolStateController', 'handleChangeState'],
];

// --- Meta Database ---
$metaDatabaseRoutes = [
    'GET /meta-database'       => ['MetaDatabaseController', 'showForm'],
    'POST /meta-data-settings' => ['MetaDatabaseController', 'saveMetadata'],
];

// --- Mode Color ---
$modeColorRoutes = [
    'GET /mode-color'        => ['ModeColorController', 'index'],
    'POST /mode-color'       => ['ModeColorController', 'store'],
    'GET /mode-color/edit'   => ['ModeColorController', 'edit'],      // ?id=123
    'POST /mode-color/update'=> ['ModeColorController', 'update'],    // POST with id
    'POST /mode-color/delete'=> ['ModeColorController', 'destroy'],   // POST with id
];

// --- Maintenance Management ---
$maintenanceRoutes = [
    'GET /dashboard_upcoming_maint'     => ['MaintenanceDashboardController', 'action' => 'upcoming'],
    'GET /completed_work_orders'        => ['CompletedWorkOrdersController', 'index'],
    'GET /completed_work_order_details'=> ['CompletedWorkOrdersController', 'view'],
];

// --- Demo Pages ---
$demoRoutes = [
    'GET /demo/demo_dashboard' => ['PagesController', 'demo_dashboard'],
    'GET /demo/demo_mes'       => ['PagesController', 'demo_mes'],
];

// --- MMS / Forms ---
$formMmsRoutes = [
    'GET /form_mms/addAsset'              => ['AssetController', 'create'],
    'POST /form_mms/addAsset'             => ['AssetController', 'store'],

    'GET /form_mms/addMaintenance'        => ['AssetMaintenanceController', 'action' => 'create'],
    'POST /form_mms/addMaintenance'       => ['AssetMaintenanceController', 'action' => 'store'],

    'GET /form_mms/checklist_template'    => ['ChecklistTemplateController', 'action' => 'create'],
    'POST /form_mms/checklist_template'   => ['ChecklistTemplateController', 'action' => 'store'],
    'GET /form_mms/checklist_template/:id'=> ['ChecklistTemplateController', 'action' => 'edit'],
    'POST /form_mms/checklist_template/update'=> ['ChecklistTemplateController', 'action' => 'update'],

    'GET /form_mms/routine_maintenance'   => ['RoutineMaintenanceController', 'action' => 'generateForm'],
    'POST /form_mms/routine_maintenance'  => ['RoutineMaintenanceController', 'action' => 'generate'],

    'GET /form_mms/checklists'           => ['ChecklistController', 'action' => 'index'],
    'GET /form_mms/checklist_edit'       => ['ChecklistController', 'action' => 'edit'],
    'POST /form_mms/checklist_update'    => ['ChecklistController', 'action' => 'update'],
];

// --- Maintenance Checklist ---
$maintenanceChecklistRoutes = [
    'POST /maintenance_checklist/associate' => ['MaintenanceChecklistController', 'associate'],
    'GET /maintenance_checklist/view'       => ['MaintenanceChecklistViewController', 'action' => 'show'],
    'POST /maintenance_checklist/update'    => ['MaintenanceChecklistUpdateController', 'action' => 'update'],
];

// --- Machine Parts ---
$machinePartsRoutes = [
    'POST /machine-parts'                => ['MachinePartController', 'store'],
    'POST /machine-parts/delete'         => ['MachinePartController', 'destroy'],
    'POST /machine-parts/update'         => ['MachinePartController', 'update'], // 👈 NEW
    'POST /machine-parts/update-desc'    => ['MachinePartController', 'updateDescription'],
    'GET /parts-list'                    => ['MachinePartController', 'list'],
];

// --- API ---
$apiRoutes = [
    'GET /api/get_maintenance_type_by_work_order' => ['RoutineMaintenanceController', 'getMaintenanceTypeByWorkOrder'],
];

// --- Merge all routes ---
return array_merge(
    $authRoutes,
    $staticPages,
    $biRoutes,
    $metaDatabaseRoutes,
    $modeColorRoutes,
    $maintenanceRoutes,
    $demoRoutes,
    $formMmsRoutes,
    $maintenanceChecklistRoutes,
    $machinePartsRoutes,
    $apiRoutes
);

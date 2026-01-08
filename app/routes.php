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
	//'GET /dashboard_admin' => ['AdminDashboardController', 'index'],//--Refractore
    'GET /dashboard_admin'  => ['PagesController', 'Dashboard_Admin'],
];

// --- Business Intelligence ---
$biRoutes = [
    'POST /create-page'            => ['GroupPageController', 'store'],
    'POST /rename-page'            => ['GroupPageController', 'rename'],
    'POST /delete-page'            => ['GroupPageController', 'destroy'],
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
    'GET /mode-color/edit'   => ['ModeColorController', 'edit'],
    'POST /mode-color/update'=> ['ModeColorController', 'update'],
    'POST /mode-color/delete'=> ['ModeColorController', 'destroy'],
];

// --- Maintenance Management --- ✅ FIXED
$maintenanceRoutes = [
    'GET /dashboard_upcoming_maint'     => ['MaintenanceDashboardController', 'upcoming'], // ✅ Simple syntax
    'GET /completed_work_orders'        => ['CompletedWorkOrdersController', 'index'],
    'GET /completed_work_order_details' => ['CompletedWorkOrdersController', 'view'],
];

// --- Demo Pages ---
$demoRoutes = [
    'GET /demo/demo_dashboard' => ['PagesController', 'demo_dashboard'],
    'GET /demo/demo_mes'       => ['PagesController', 'demo_mes'],
];

// --- MMS / Forms ---
$formMmsRoutes = [
    'GET /form_mms/addAsset'              		=> ['AssetController', 'create'],
    'POST /form_mms/addAsset'             		=> ['AssetController', 'store'],
    'GET /form_mms/addMaintenance'        		=> ['AssetMaintenanceController', 'create'],
    'POST /form_mms/addMaintenance'       		=> ['AssetMaintenanceController', 'store'],
    'GET /form_mms/checklist_template'    		=> ['ChecklistTemplateController', 'create'],
    'POST /form_mms/checklist_template'   		=> ['ChecklistTemplateController', 'store'],
    'GET /form_mms/checklist_template/:id'		=> ['ChecklistTemplateController', 'edit'],
    'POST /form_mms/checklist_template/update'	=> ['ChecklistTemplateController', 'update'],
    'GET /form_mms/routine_maintenance'   		=> ['RoutineMaintenanceController', 'generateForm'],
    'POST /form_mms/routine_maintenance'  		=> ['RoutineMaintenanceController', 'generate'],
    'GET /form_mms/checklists'            		=> ['ChecklistController', 'index'],
    'GET /form_mms/checklist_edit'        		=> ['ChecklistController', 'edit'],
    'POST /form_mms/checklist_update'     		=> ['ChecklistController', 'update'],
];

// --- Maintenance Checklist ---
$maintenanceChecklistRoutes = [
    'POST /maintenance_checklist/associate' => ['MaintenanceChecklistController', 'associate'],
    'GET /maintenance_checklist/view'       => ['MaintenanceChecklistViewController', 'show'],
    'POST /maintenance_checklist/update'    => ['MaintenanceChecklistUpdateController', 'update'],
];

// --- Machine Parts ---
$machinePartsRoutes = [
    'POST /machine-parts'             => ['MachinePartController', 'store'],
    'POST /machine-parts/delete'      => ['MachinePartController', 'destroy'],
    'POST /machine-parts/update'      => ['MachinePartController', 'update'],
    'POST /machine-parts/update-desc' => ['MachinePartController', 'updateDescription'],
    'GET /parts-list'                 => ['MachinePartController', 'list'],
];

// --- API ---
$apiRoutes = [
    'GET /api/get_maintenance_type_by_work_order' => ['RoutineMaintenanceController', 'getMaintenanceTypeByWorkOrder'],
];

// --- Scheduler Config (TBPM) ---
$schedulerRoutes = [
    'GET /form_mms/scheduler_config'         => ['SchedulerConfigController', 'edit'],
    'POST /form_mms/scheduler_config_update'=> ['SchedulerConfigController', 'update'],
];

// --- 🔌 Device Registration ---
$deviceRoutes = [
    'GET /device/register'         => ['DeviceController', 'create'],
    'POST /device/register'        => ['DeviceController', 'store'],
    'GET /device/register-success' => ['DeviceController', 'registerSuccess'],
    'GET /device'                  => ['DeviceController', 'index'],
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
    $apiRoutes,
	$schedulerRoutes
	$deviceRoutes
);
<?php
  //routes.php
return [
   

    // --- Register Organization ---
    'GET /register'  => ['RegisterController', 'register'],
    'POST /register' => ['RegisterController', 'submit'],

    // --- Sign-in ---
    'GET /signin'     => ['SigninController', 'signin'],
    'POST /signin'    => ['SigninController', 'authenticate'],
    'GET /signout'    => ['SigninController', 'signout'],

    // --- Tenant Portal ---
    'GET /hub_portal' => ['SigninController', 'hubPortal'],
	
	 // --- Static Pages ---
    'GET /'        => ['PagesController', 'welcome'],
    'GET /welcome' => ['PagesController', 'welcome'],
    'GET /about'   => ['PagesController', 'about'],
    'GET /canvas'  => ['PagesController', 'canvas'],
	'GET /mms_admin'  => ['PagesController', 'mms_Admin'],
	'GET /dashboard_admin'  => ['PagesController', 'Dashboard_Admin'],
	
	//---- Business intellegence Dashboard -----
	
	// Edit Entity Card Position
	'POST /update-entity-position' => ['UpdateEntityPositionController', 'handleUpdate'],
	
	// Create new page
	'POST /create-page' => ['GroupPageController', 'store'],
	
	// Group Management
	'POST /create-group' => ['CreateGroupController', 'handleCreateGroup'],
	
	// Update group
	'POST /update-group' => ['UpdateGroupController', 'handleUpdate'],

	// Delete group  
	'POST /delete-group' => ['DeleteGroupController', 'handleDelete'],
	
	// Add entity to group
    'POST /add-entity' => ['AddEntityToDashboardController', 'handleAddEntity'],
	
	// Change Tool State
	'POST /change-tool-state' => ['ToolStateController', 'handleChangeState'],
	
	// Meta_database configutation page
	'GET /meta-database' => ['MetaDatabaseController', 'showForm'],
    'POST /meta-data-settings' => ['MetaDatabaseController', 'saveMetadata'],
	
	// Mode Color CRUD 

	'GET /mode-color' => ['ModeColorController', 'index'],
	'POST /mode-color' => ['ModeColorController', 'store'],
	'GET /mode-color/edit' => ['ModeColorController', 'edit'],       // ?id=123
	'POST /mode-color/update' => ['ModeColorController', 'update'],   // POST with id
	'POST /mode-color/delete' => ['ModeColorController', 'destroy'],  // POST with id
	
	//-----------  Maintenance Management -----------------------------
	
	// Dashboard for Upcoming Maintenance
	'GET /dashboard_upcoming_maint' => ['MaintenanceDashboardController', 'action' => 'upcoming'],
	
	// --- COMPLETED WORK ORDERS DASHBOARD ---
	// routes.php
	'GET /completed_work_orders' => ['CompletedWorkOrdersController', 'index'],
	'GET /completed_work_order_details' => ['CompletedWorkOrdersController', 'view'], // ✅ Flat route
		

	// --- DEMO ---
	'GET /demo/demo_dashboard'  => ['PagesController', 'demo_dashboard'],
	'GET /demo/demo_mes'        => ['PagesController', 'demo_mes'],
	
	// --- Add Assets (MMS Form Pages) ---
	'GET /form_mms/addAsset'  => ['AssetController', 'create'],
	'POST /form_mms/addAsset' => ['AssetController', 'store'],
	
	// Maintenance
	'GET /form_mms/addMaintenance'  => ['AssetMaintenanceController', 'action' => 'create'],
	'POST /form_mms/addMaintenance' => ['AssetMaintenanceController', 'action' => 'store'],
	
	// Checklist Template Routes
	'GET /form_mms/checklist_template'   => ['ChecklistTemplateController', 'action' => 'create'],
	'POST /form_mms/checklist_template'  => ['ChecklistTemplateController', 'action' => 'store'],
	'GET /form_mms/checklist_template/:id' => ['ChecklistTemplateController', 'action' => 'edit'],
	'POST /form_mms/checklist_template/update' => ['ChecklistTemplateController', 'action' => 'update'],
	
	// Routine Maintenance Generator
	'GET /form_mms/routine_maintenance'  => ['RoutineMaintenanceController', 'action' => 'generateForm'],
	'POST /form_mms/routine_maintenance' => ['RoutineMaintenanceController', 'action' => 'generate'],
	'GET /api/get_maintenance_type_by_work_order' => ['RoutineMaintenanceController', 'getMaintenanceTypeByWorkOrder'],

	
	
	// Checklist – View all / Edit / Update
	'GET /form_mms/checklists' => ['ChecklistController', 'action' => 'index'],
	'GET /form_mms/checklist_edit' => ['ChecklistController', 'action' => 'edit'],
	'POST /form_mms/checklist_update' => ['ChecklistController', 'action' => 'update'],
	
	// Associate checklist to an asset (Maintenance Checklist @ schedule routine maintenance)
   'POST /maintenance_checklist/associate' => ['MaintenanceChecklistController', 'associate'],
   
   // Maintenance Checklist (Instance of a template)
	'GET /maintenance_checklist/view' => ['MaintenanceChecklistViewController', 'action' => 'show'],
    'POST /maintenance_checklist/update' => ['MaintenanceChecklistUpdateController', 'action' => 'update'],
	
	// Machine Parts
	'POST /machine-parts' => ['MachinePartController', 'store'],
	'POST /machine-parts/delete' => ['MachinePartController', 'destroy'],
	
	'GET /parts-list' => ['MachinePartController', 'list'],
	
	
];
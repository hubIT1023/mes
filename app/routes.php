<?php

// --- Auth ---
$authRoutes = [
    'GET /register'  => ['RegisterController', 'register'],
    'POST /register' => ['RegisterController', 'submit'],

    'GET /signin'    => ['SigninController', 'signin'],
    'POST /signin'   => ['SigninController', 'authenticate'],
    'GET /signout'   => ['SigninController', 'signout'],

    'GET /hub_portal'=> ['SigninController', 'hubPortal'],
];

// --- Static ---
$staticPages = [
    'GET /'                => ['PagesController', 'welcome'],
    'GET /welcome'         => ['PagesController', 'welcome'],
    'GET /about'           => ['PagesController', 'about'],
    'GET /canvas'          => ['PagesController', 'canvas'],
    'GET /dashboard_admin' => ['PagesController', 'Dashboard_Admin'],
];

// --- Dashboard / Pages ---
$biRoutes = [
    'POST /create-page'  => ['GroupPageController', 'store'],
    'POST /rename-page'  => ['GroupPageController', 'rename'],
    'POST /delete-page'  => ['GroupPageController', 'destroy'],

    'POST /create-group' => ['CreateGroupController', 'handleCreateGroup'],
    'POST /update-group' => ['UpdateGroupController', 'handleUpdate'],
    'POST /delete-group' => ['DeleteGroupController', 'handleDelete'],
    'POST /add-entity'   => ['AddEntityToDashboardController', 'handleAddEntity'],
];

// --- Mode Color ---
$modeColorRoutes = [
    'GET /mode-color'  => ['ModeColorController', 'index'],
    'POST /mode-color' => ['ModeColorController', 'store'],
];

// --- Machine Parts ---
$machinePartsRoutes = [
    'GET /parts-list'             => ['MachinePartController', 'list'],
    'POST /machine-parts'         => ['MachinePartController', 'store'],
    'POST /machine-parts/update'  => ['MachinePartController', 'update'],
    'POST /machine-parts/delete'  => ['MachinePartController', 'destroy'],
];

// Merge all
return array_merge(
    $authRoutes,
    $staticPages,
    $biRoutes,
    $modeColorRoutes,
    $machinePartsRoutes
);

<?php
// Start session and validate
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['tenant'])) {
    echo "<div class='alert alert-danger'>Session expired. Please log in.</div>";
    exit;
}

// Include controller
require_once __DIR__ . '/../../controllers/AssociateChecklistController.php';

// Instantiate controller and call AJAX action
$controller = new AssociateChecklistController();
$controller->loadChecklist();
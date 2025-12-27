<?php
// /public/api/getMaintenanceDetails.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$orgId = $_SESSION['org_id'] ?? null;
$option = $_GET['option'] ?? null;

if (!$orgId || !$option || !is_numeric($option)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

global $pdo;
if (!$pdo) {
    require_once __DIR__ . '/../../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
}

try {
    $stmt = $pdo->prepare("
        SELECT maintenance_type, interval_days, checklist_id 
        FROM scheduled_maintenance 
        WHERE org_id = ? AND checklist_option = ?
    ");
    $stmt->execute([$orgId, (int)$option]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Not found']);
    }
} catch (Exception $e) {
    error_log("API failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
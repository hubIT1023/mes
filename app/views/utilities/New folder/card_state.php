<?php 
// dashboard.php
session_start();

if (!isset($_SESSION['org_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/DB_con.php';
use Api\Config\DB_con;

$conn = DB_con::connect();

// Fetch groups for this org
$stmt = $conn->prepare("SELECT * FROM groups WHERE org_id = :org_id ORDER BY created_at DESC");
$stmt->execute(['org_id' => $_SESSION['org_id']]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all entities
$entitiesStmt = $conn->prepare("SELECT * FROM registered_tools WHERE org_id = :org_id");
$entitiesStmt->execute(['org_id' => $_SESSION['org_id']]);
$entities = $entitiesStmt->fetchAll(PDO::FETCH_ASSOC);


// Machine state
$state = 'IS';

$color_1 = '#b8c1b3';

switch ($state) {
    case "PROD":
        $color = 'btn-success';
        $label = 'PROD';
        break;

    case "FAILT":
        $color = 'btn-danger';
        $label = 'MAINT-COR';
        break;

    case "MAINT":
        $color = 'btn-warning';
        $label = 'MAINT-PRE';
        break;

    case "PROCESS":
        $color = 'btn-primary';
        $label = 'PROCESS';
        break;

    case "EDG/SDG":
        $color =  'btn-info';
        $label = 'EDG/SDG';
        break;

    case "IS":
        $color = 'btn-is';
        $label = 'IS';
        break;

    default:
        $color = 'btn-secondary';
        $label = 'UNKNOWN';
        break;
}
echo "State: $state, Color: $color, Label: $label";


?>

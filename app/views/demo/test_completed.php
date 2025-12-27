<!-- Create test_completed.php -->
<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';

if (!isset($_SESSION['tenant_id'])) {
    die("Not logged in");
}

$tenant_id = $_SESSION['tenant_id'];
$conn = Database::getInstance()->getConnection();

// Simple query without filters
$sql = "SELECT TOP 10 * FROM dbo.completed_work_order WHERE tenant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$tenant_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Test Results:</h2>";
echo "<p>Tenant ID: " . htmlspecialchars($tenant_id) . "</p>";
echo "<p>Records found: " . count($results) . "</p>";

if (empty($results)) {
    echo "<p><strong>No records found!</strong> Check:</p>";
    echo "<ol>";
    echo "<li>Does your tenant_id exist in completed_work_order table?</li>";
    echo "<li>Are there any completed work orders for this tenant?</li>";
    echo "</ol>";
} else {
    echo "<pre>";
    print_r($results);
    echo "</pre>";
}
?>
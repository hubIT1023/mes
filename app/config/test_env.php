<?php
require_once __DIR__ . "/Database.php";

$db = Database::getInstance()->getConnection();

// Test query (optional)
// $stmt = $db->query("SELECT name FROM sys.databases");
// $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<h2>✅ Connected using .env config!</h2><ul>";
// foreach ($result as $r) {
//     echo "<li>$r</li>";
// }
echo "</ul>";
?>
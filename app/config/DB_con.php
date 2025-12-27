<?php 
//database mssql Connection using windows authentication
 
$serverName = "HUBIT\HUBIT"; 
$database   = "Tool_Monitoring_System";


try {
    $dsn = "sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=true;Authentication=ActiveDirectoryIntegrated";

    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connection established!<br />";


} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

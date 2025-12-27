<?php
// app/models/GroupPageModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupPageModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // ✅ Method to get placeholder record
    public function getPlaceholderRecord(string $orgId, int $pageId): ?array {
        $stmt = $this->conn->prepare("
            SELECT id, group_code, location_code, group_name, location_name
            FROM group_location_map 
            WHERE org_id = ? AND page_id = ? AND group_name = '---'
        ");
        $stmt->execute([$orgId, $pageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Method to update placeholder record
    public function updatePlaceholder(array $data): bool {
        $sql = "
            UPDATE group_location_map 
            SET 
                group_code = ?,
                location_code = ?,
                group_name = ?,
                location_name = ?,
                page_name = ?,
                updated_at = GETDATE()
            WHERE org_id = ? AND page_id = ? AND group_name = '---'
        ";

        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            $data['group_code'],
            $data['location_code'],
            $data['group_name'],
            $data['location_name'],
            $data['page_name'],
            $data['org_id'],
            $data['page_id']
        ]);
    }

    // ✅ Keep your existing createPage method
    public function createPage(array $data): bool {
        $sql = "
            INSERT INTO group_location_map (
                org_id, 
                group_code, 
                location_code, 
                group_name, 
                location_name,
                page_id, 
                page_name,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, GETDATE()
            )
        ";

        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            $data['org_id'],    
            $data['group_code'],  
            $data['location_code'],  
            $data['group_name'],  
            $data['location_name'],
            $data['page_id'],   
            $data['page_name']  
        ]);
    }
}
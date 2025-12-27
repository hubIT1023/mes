<?php
require_once __DIR__ . '/../config/Database.php';

class UpdateEntityPositionModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function updatePosition(string $orgId, int $entityId, int $row_pos, int $col_pos): bool {
        // Prevent duplicates: check if (row,col) already used in this group
        $stmt = $this->conn->prepare("
            SELECT 1 FROM registered_tools 
            WHERE org_id = ? 
              AND id != ? 
              AND group_code = (SELECT group_code FROM registered_tools WHERE id = ?)
              AND location_code = (SELECT location_code FROM registered_tools WHERE id = ?)
              AND row_pos = ? 
              AND col_pos = ?
        ");
        $stmt->execute([$orgId, $entityId, $entityId, $entityId, $row_pos, $col_pos]);

        if ($stmt->fetch()) {
            // Conflict — do not allow
            return false;
        }

        // Update position
        $stmt = $this->conn->prepare("
            UPDATE registered_tools 
            SET row_pos = ?, col_pos = ?
            WHERE id = ? AND org_id = ?
        ");
        return $stmt->execute([$row_pos, $col_pos, $entityId, $orgId]);
    }
}
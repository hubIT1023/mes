<?php
// app/models/ToolStateModel.php

require_once __DIR__ . '/../config/Database.php';

class ToolStateModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

	//Receive input from user	
	public function updateToolState(array $data): void
{
    $stmt = $this->conn->prepare("
        UPDATE tool_state
        SET
            group_code    = :group_code,
            location_code = :location_code,
            col_2         = :col_2,
            col_3         = :col_3,
            col_4         = :col_4,
            col_5         = :col_5,
            col_6         = :col_6,
            col_8         = :col_8,
            col_9         = :col_9
        WHERE org_id = :org_id
          AND col_1  = :col_1
    ");

    $stmt->execute($data);
}
			 

    
	// custom stopcause (IDELE,PROD,MAINT...)
	public function getModeColorChoices(string $orgId): array {
        try {
            $stmt = $this->conn->prepare("
                SELECT mode_key, label
                FROM mode_color
                WHERE org_id = ?
                ORDER BY label
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            error_log("Failed to fetch mode_color choices: " . $e->getMessage());
            return [];
        }
    }
	
}
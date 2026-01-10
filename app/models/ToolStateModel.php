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
			 
	//process the data
	
	public function processToolStateData(array $data): void
{
    $stmt = $this->conn->prepare("
        UPDATE tool_state
        SET
            col_10 = col_3,
            col_7  = NOW()
        WHERE org_id = :org_id
          AND col_1  = :col_1
    ");

    $stmt->execute($data);
}
			 
	//save data to history
	public function saveHistoryToMachineLog(array $data): void
{
    $stmt = $this->conn->prepare("
        INSERT INTO machine_log (
            org_id, group_code, location_code,
            col_1, col_2, col_3, col_4, col_5,
            col_6, col_7, col_8, col_9, col_10
        )
        SELECT
            org_id, group_code, location_code,
            col_1, col_2, col_3, col_4, col_5,
            col_6, NOW(), col_8, col_9, col_10
        FROM tool_state
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
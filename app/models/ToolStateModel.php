<?php
//app/models/ToolStateModel.php

require_once __DIR__ . '/../config/Database.php';

class ToolStateModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function saveToolState(array $data): array {
        if (empty($data['org_id']) || empty($data['col_1'])) {
            return ['success' => false, 'error' => 'Missing org_id or asset_id'];
        }

        try {
            $this->conn->beginTransaction();

            // ✅ Use col_1 (asset_id) as the unique key
            $check = $this->conn->prepare("
                SELECT id FROM tool_state 
                WHERE org_id = ? AND col_1 = ?
            ");
            $check->execute([$data['org_id'], $data['col_1']]);
            $row = $check->fetch(PDO::FETCH_ASSOC);

            // Set default values
            $fullData = array_merge($data, [
                'col_6' => date('Y-m-d H:i:s'), // timestamp
                'col_7' => date('Y-m-d H:i:s'), // start time
                'col_9' => '',
                'col_10' => 'active',
                'col_11' => '',
                'col_12' => '',
                'col_13' => '',
                'col_14' => '',
                'col_15' => '',
                'col_16' => '',
            ]);

            if ($row) {
                // ✅ UPDATE by asset_id (col_1)
                $sql = "
                    UPDATE tool_state SET
                        group_code = :group_code,
                        location_code = :location_code,
                        col_2 = :col_2,
                        col_3 = :col_3,
                        col_4 = :col_4,
                        col_5 = :col_5,
                        col_6 = :col_6,
                        col_7 = :col_7,
                        col_8 = :col_8,
                        col_9 = :col_9,
                        col_10 = :col_10,
                        col_11 = :col_11,
                        col_12 = :col_12,
                        col_13 = :col_13,
                        col_14 = :col_14,
                        col_15 = :col_15,
                        col_16 = :col_16
                    WHERE org_id = :org_id AND col_1 = :col_1
                ";
            } else {
                // ✅ INSERT new record
                $sql = "
                    INSERT INTO tool_state (
                        org_id, group_code, location_code,
                        col_1, col_2, col_3, col_4, col_5,
                        col_6, col_7, col_8,
                        col_9, col_10, col_11, col_12,
                        col_13, col_14, col_15, col_16
                    ) VALUES (
                        :org_id, :group_code, :location_code,
                        :col_1, :col_2, :col_3, :col_4, :col_5,
                        :col_6, :col_7, :col_8,
                        :col_9, :col_10, :col_11, :col_12,
                        :col_13, :col_14, :col_15, :col_16
                    )
                ";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'org_id' => $fullData['org_id'],
                'group_code' => $fullData['group_code'],
                'location_code' => $fullData['location_code'],
                'col_1' => $fullData['col_1'],
                'col_2' => $fullData['col_2'],
                'col_3' => $fullData['col_3'],
                'col_4' => $fullData['col_4'],
                'col_5' => $fullData['col_5'],
                'col_6' => $fullData['col_6'],
                'col_7' => $fullData['col_7'],
                'col_8' => $fullData['col_8'],
                'col_9' => $fullData['col_9'],
                'col_10' => $fullData['col_10'],
                'col_11' => $fullData['col_11'],
                'col_12' => $fullData['col_12'],
                'col_13' => $fullData['col_13'],
                'col_14' => $fullData['col_14'],
                'col_15' => $fullData['col_15'],
                'col_16' => $fullData['col_16'],
            ]);

            // Log to history if PROD
            if (strtoupper($fullData['col_3']) === 'PROD') {
                $this->logToHistory($fullData);
            }

            $this->conn->commit();
            return ['success' => true];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("ToolStateModel error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    private function logToHistory(array $data): void {
        $sql = "
            INSERT INTO history (
                org_id, group_code, location_code,
                col_1, col_2, col_3, col_4, col_5,
                col_6, col_7, col_8,
                col_9, col_10, col_11, col_12,
                col_13, col_14, col_15, col_16
            ) VALUES (
                :org_id, :group_code, :location_code,
                :col_1, :col_2, :col_3, :col_4, :col_5,
                :col_6, :col_7, :col_8,
                :col_9, :col_10, :col_11, :col_12,
                :col_13, :col_14, :col_15, :col_16
            )
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
    }
	
	// In ToolStateModel.php or ModeColorModel.php
	public function getModeColorChoices(string $orgId): array {
		try {
			$stmt = $this->conn->prepare("
				SELECT mode_key, label
				FROM mode_color
				WHERE org_id = ?
				ORDER BY mode_key
			");
			$stmt->execute([$orgId]);
			return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [mode_key => label]
		} catch (PDOException $e) {
			error_log("Failed to fetch mode_color choices: " . $e->getMessage());
			return [];
		}
	}
	
}
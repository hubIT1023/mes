<?php
// app/models/ToolStateModel.php

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

    $dateTimeNow = date('Y-m-d H:i:s');
    $orgId = $data['org_id'];
    $assetId = $data['col_1']; // ← PRIMARY KEY

    try {
        $this->conn->beginTransaction();

        // --- STEP 1: Ensure row exists using col_1 (asset_id) ---
        $check = $this->conn->prepare("SELECT col_3 FROM tool_state WHERE org_id = ? AND col_1 = ?");
        $check->execute([$orgId, $assetId]);
        $existingRow = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existingRow) {
            // INSERT new row — include col_3 from input!
            $insert = $this->conn->prepare("
                INSERT INTO tool_state (
                    org_id, group_code, location_code, col_1, col_2,
                    col_3, col_4, col_5, col_6, col_7, col_8, col_9,
                    col_10, col_11
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    '', ''
                )
            ");
            $insert->execute([
                $orgId,
                $data['group_code'] ?? 0,
                $data['location_code'] ?? 0,
                $assetId,
                $data['col_2'] ?? '',
                $data['col_3'] ?? 'IDLE', // ← SET col_3 HERE!
                $data['col_4'] ?? '',
                $data['col_5'] ?? '',
                $dateTimeNow, // col_6
                $dateTimeNow, // col_7
                $data['col_8'] ?? '',
                $data['col_9'] ?? ''
            ]);
            $finalCol3 = $data['col_3'] ?? 'IDLE';
        } else {
            $finalCol3 = $existingRow['col_3'];
        }

        // --- STEP 2: Update row based on NEW col_3 value ---
        $newCol3 = $data['col_3'];

        if ($newCol3 !== 'PROD') {
            // NON-PROD: reset start time, save previous mode
            $stmt = $this->conn->prepare("
                UPDATE tool_state SET
                    group_code = ?, location_code = ?, col_2 = ?,
                    col_3 = ?, col_4 = ?, col_5 = ?,
                    col_6 = ?, col_10 = ?, col_8 = ?
                WHERE org_id = ? AND col_1 = ?
            ");
            $stmt->execute([
                $data['group_code'],
                $data['location_code'],
                $data['col_2'],
                $newCol3,
                $data['col_4'],
                $data['col_5'],
                $dateTimeNow,      // new start time
                $finalCol3,        // previous col_3
                $data['col_8'],
                $orgId,
                $assetId
            ]);

        } elseif ($newCol3 === 'PROD') {
            // PROD: set end time and person completed
            $stmt = $this->conn->prepare("
                UPDATE tool_state SET
                    group_code = ?, location_code = ?, col_2 = ?,
                    col_3 = ?, col_4 = ?, col_5 = ?,
                    col_7 = ?, col_8 = ?, col_9 = ?
                WHERE org_id = ? AND col_1 = ?
            ");
            $stmt->execute([
                $data['group_code'],
                $data['location_code'],
                $data['col_2'],
                $newCol3,
                $data['col_4'],
                $data['col_5'],
                $dateTimeNow,      // col_7 = end time
                $data['col_8'],
                $data['col_9'],
                $orgId,
                $assetId
            ]);

            // --- STEP 3: Log to machine_log ---
            $logStmt = $this->conn->prepare("
                INSERT INTO machine_log (
                    org_id, group_code, location_code,
                    col_1, col_2, col_3, col_4, col_5,
                    col_6, col_7, col_8, col_9,
                    col_10, col_11, dateStamp
                )
                SELECT 
                    org_id, group_code, location_code,
                    col_1, col_2, col_3, col_4, col_5,
                    col_6, col_7, col_8, col_9,
                    col_10, 'Completed', ?
                FROM tool_state
                WHERE org_id = ? AND col_1 = ?
            ");
            $logStmt->execute([$dateTimeNow, $orgId, $assetId]);
        }

        $this->conn->commit();
        return ['success' => true];

    } catch (Exception $e) {
        $this->conn->rollBack();
        error_log("ToolStateModel error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("ToolStateModel DB error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Database operation failed'];
    }
}
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
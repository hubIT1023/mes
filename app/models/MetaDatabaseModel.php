<?php
require_once __DIR__ . '/../config/Database.php';

class MetaDatabaseModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getMetadata(string $orgId): array {
        $stmt = $this->conn->prepare("
            SELECT col_number, label, description 
            FROM tool_state_metadata 
            WHERE org_id = ?
        ");
        $stmt->execute([$orgId]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['col_number']] = [
                'label' => $row['label'],
                'description' => $row['description']
            ];
        }
        return $result;
    }

    public function saveMetadata(string $orgId, array $updates): bool {
        try {
            $this->conn->beginTransaction();

            // Clear existing metadata for org
            $clear = $this->conn->prepare("DELETE FROM tool_state_metadata WHERE org_id = ?");
            $clear->execute([$orgId]);

            // Insert new metadata
            if (!empty($updates)) {
                $insert = $this->conn->prepare("
                    INSERT INTO tool_state_metadata (org_id, col_number, label, description)
                    VALUES (:org_id, :col_number, :label, :description)
                ");

                foreach ($updates as $data) {
                    $insert->execute([
                        'org_id' => $data['org_id'],
                        'col_number' => $data['col_number'],
                        'label' => $data['label'],
                        'description' => $data['description']
                    ]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("MetaDatabaseModel save error: " . $e->getMessage());
            return false;
        }
    }
}
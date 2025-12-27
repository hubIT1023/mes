<?php
// app/models/CreateGroupModel.php

require_once __DIR__ . '/../config/Database.php';

class CreateGroupModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getPageName(int $pageId, string $orgId): ?string {
        $stmt = $this->conn->prepare("
            SELECT TOP 1 page_name 
            FROM group_location_map 
            WHERE org_id = ? AND page_id = ?
        ");
        $stmt->execute([$orgId, $pageId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function isGroupCodeUsed(int $group_code, string $orgId): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM group_location_map 
            WHERE org_id = ? AND group_code = ?
        ");
        $stmt->execute([$orgId, $group_code]);
        return (bool) $stmt->fetch();
    }

    public function isLocationCodeUsed(int $location_code, string $orgId): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM group_location_map 
            WHERE org_id = ? AND location_code = ?
        ");
        $stmt->execute([$orgId, $location_code]);
        return (bool) $stmt->fetch();
    }

	public function createGroup(array $data): bool {
		$sql = "
			INSERT INTO group_location_map (
				org_id, page_id, page_name, group_code, location_code,
				group_name, location_name, seq_id, created_at
			) VALUES (
				:org_id, :page_id, :page_name, :group_code, :location_code,
				:group_name, :location_name, :seq_id, GETDATE()
			)
		";

		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([
			'org_id' => $data['org_id'],
			'page_id' => $data['page_id'],
			'page_name' => $data['page_name'],
			'group_code' => $data['group_code'],
			'location_code' => $data['location_code'],
			'group_name' => $data['group_name'],
			'location_name' => $data['location_name'],
			'seq_id' => $data['seq_id'] ?? null
		]);
	}
    
    // ✅ FIXED: Returns null instead of false
    public function getPlaceholderRecord(string $orgId, int $pageId): ?array {
        $stmt = $this->conn->prepare("
            SELECT id FROM group_location_map 
            WHERE org_id = ? AND page_id = ? AND group_name = '---'
        ");
        $stmt->execute([$orgId, $pageId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    // ✅ FIXED: Removed updated_at from UPDATE statement
    // Update updatePlaceholder() method in CreateGroupModel.php
	public function updatePlaceholder(array $data): bool {
		$sql = "
			UPDATE group_location_map 
			SET 
				group_code = ?,
				location_code = ?,
				group_name = ?,
				location_name = ?,
				page_name = ?,
				seq_id = ?
			WHERE id = ?
		";
		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([
			$data['group_code'],
			$data['location_code'],
			$data['group_name'],
			$data['location_name'],
			$data['page_name'],
			$data['seq_id'],  // ✅ Add sequence ID
			$data['id']
		]);
	}
	
	
	public function getNextSequenceId(string $orgId, int $pageId): int {
		$stmt = $this->conn->prepare("
			SELECT ISNULL(MAX(seq_id), 0) + 1 
			FROM group_location_map 
			WHERE org_id = ? AND page_id = ? AND group_name != '---'
		");
		$stmt->execute([$orgId, $pageId]);
		return (int) $stmt->fetchColumn();
	}
}
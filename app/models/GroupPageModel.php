<?php
// app/models/GroupPageModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupPageModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getNextPageId(string $orgId): int {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(MAX(page_id::INTEGER), 0) + 1 
            FROM group_location_map 
            WHERE org_id = ?
        ");
        $stmt->execute([$orgId]);
        return (int) $stmt->fetchColumn();
    }

    public function isPageNameUsed(string $orgId, string $pageName): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM group_location_map 
            WHERE org_id = ? AND page_name = ?
            LIMIT 1
        ");
        $stmt->execute([$orgId, $pageName]);
        return (bool) $stmt->fetch();
    }

    public function createPage(array $data): bool {
        $sql = "
            INSERT INTO group_location_map (
                org_id, page_id, page_name,
                group_code, location_code,
                group_name, location_name,
                created_at
            ) VALUES (
                :org_id, :page_id, :page_name,
                :group_code, :location_code,
                :group_name, :location_name,
                CURRENT_TIMESTAMP
            )
        ";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function renamePage(string $orgId, int $pageId, string $newName): bool {
        $stmt = $this->conn->prepare("
            UPDATE group_location_map 
            SET page_name = :page_name, updated_at = CURRENT_TIMESTAMP
            WHERE org_id = :org_id AND page_id = :page_id
        ");
        return $stmt->execute([
            'org_id' => $orgId,
            'page_id' => $pageId,
            'page_name' => $newName
        ]);
    }

    // In app/models/GroupPageModel.php

	public function deletePage(string $orgId, int $pageId): bool {
		// First, check if page exists (any row with this page_id)
		$existsStmt = $this->conn->prepare("
			SELECT 1 FROM group_location_map 
			WHERE org_id = ? AND page_id = ? 
			LIMIT 1
		");
		$existsStmt->execute([$orgId, $pageId]);
		if (!$existsStmt->fetch()) {
			return true; // Page doesn't exist — idempotent success
		}

		try {
			$this->conn->beginTransaction();

			// Delete all related data (groups, entities, placeholder)
			$stmt1 = $this->conn->prepare("
				DELETE FROM registered_tools 
				WHERE org_id = ? AND page_id = ?
			");
			$stmt1->execute([$orgId, $pageId]);

			$stmt2 = $this->conn->prepare("
				DELETE FROM tool_state 
				WHERE org_id = ? AND page_id = ?
			");
			$stmt2->execute([$orgId, $pageId]);

			$stmt3 = $this->conn->prepare("
				DELETE FROM group_location_map 
				WHERE org_id = ? AND page_id = ?
			");
			$stmt3->execute([$orgId, $pageId]);

			$this->conn->commit();
			return true;
		} catch (Exception $e) {
			$this->conn->rollback();
			error_log("Delete page failed: " . $e->getMessage());
			return false;
		}
	}

	// Add this helper to get a safe redirect page
	public function getFirstPageId(string $orgId): ?int {
		$stmt = $this->conn->prepare("
			SELECT page_id FROM group_location_map 
			WHERE org_id = ? 
			ORDER BY page_id::INTEGER 
			LIMIT 1
		");
		$stmt->execute([$orgId]);
		$result = $stmt->fetchColumn();
		return $result ? (int)$result : null;
	}

   public function getPageName(int $pageId, string $orgId): ?string {
    $stmt = $this->conn->prepare("
        SELECT page_name 
        FROM group_location_map 
        WHERE org_id = ? AND page_id = ?
        LIMIT 1
    ");
    $stmt->execute([$orgId, $pageId]);
    return $stmt->fetchColumn() ?: null;
}
}
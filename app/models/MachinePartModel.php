<?php
// app/models/MachinePartModel.php

require_once __DIR__ . '/../config/Database.php';

class MachinePartModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Get all parts for a specific entity
    public function getByEntity(string $orgId, string $assetId, string $entity): array {
        $stmt = $this->conn->prepare("
            SELECT * FROM machine_parts_list 
            WHERE org_id = ? AND asset_id = ? AND entity = ?
            ORDER BY part_name
        ");
        $stmt->execute([$orgId, $assetId, $entity]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create new part
    public function create(array $data): bool {
        $stmt = $this->conn->prepare("
            INSERT INTO machine_parts_list (
                org_id, asset_id, entity, part_id, part_name, serial_no,
                vendor_id, mfg_code, sap_code, category,
                parts_available_on_hand, description, image_path
            ) VALUES (
                :org_id, :asset_id, :entity, :part_id, :part_name, :serial_no,
                :vendor_id, :mfg_code, :sap_code, :category,
                :parts_available_on_hand, :description, :image_path
            )
        ");
        return $stmt->execute($data);
    }

    // Update part
    public function update(int $id, string $orgId, array $data): bool {
        $stmt = $this->conn->prepare("
            UPDATE machine_parts_list SET
                part_id = :part_id,
                part_name = :part_name,
                serial_no = :serial_no,
                vendor_id = :vendor_id,
                mfg_code = :mfg_code,
                sap_code = :sap_code,
                category = :category,
                parts_available_on_hand = :parts_available_on_hand,
                description = :description,
                image_path = :image_path
            WHERE id = :id AND org_id = :org_id
        ");
        $data['id'] = $id;
        $data['org_id'] = $orgId;
        return $stmt->execute($data);
    }

    // Delete part
    public function delete(int $id, string $orgId): bool {
        $stmt = $this->conn->prepare("DELETE FROM machine_parts_list WHERE id = ? AND org_id = ?");
        return $stmt->execute([$id, $orgId]);
    }

    // Check if part_id exists for this entity
    public function partExistsForEntity(string $orgId, string $assetId, string $entity, string $partId, ?int $excludeId = null): bool {
        $sql = "SELECT 1 FROM machine_parts_list WHERE org_id = ? AND asset_id = ? AND entity = ? AND part_id = ?";
        $params = [$orgId, $assetId, $entity, $partId];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    // Handle image upload
    public function uploadImage($file): ?string {
		if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
			error_log("Upload error: " . $file['error']);
			return null;
		}

		$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
		if (!in_array($file['type'], $allowedTypes)) {
			error_log("Invalid file type: " . $file['type']);
			return null;
		}

		// Save to /app/parts_img/
		$uploadDir = __DIR__ . '/../parts_img/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0777, true);
			error_log("Created directory: " . $uploadDir);
		}

		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		$filename = 'part_' . uniqid() . '.' . $ext;
		$targetPath = $uploadDir . $filename;

		if (move_uploaded_file($file['tmp_name'], $targetPath)) {
			error_log("File saved: " . $targetPath);
			return '/app/parts_img/' . $filename; // ← URL path for browser
		}

		error_log("Failed to move file");
		return null;
	}
	
	
	// ✅ NEW: Get filtered parts
    public function getFilteredParts(string $orgId, array $filters): array {
        $sql = "SELECT * FROM machine_parts_list WHERE org_id = ?";
        $params = [$orgId];
        $conditions = [];

        if ($filters['entity']) {
            $conditions[] = "entity LIKE ?";
            $params[] = '%' . $filters['entity'] . '%';
        }
        if ($filters['part_name']) {
            $conditions[] = "part_name LIKE ?";
            $params[] = '%' . $filters['part_name'] . '%';
        }
        if ($filters['vendor_id']) {
            $conditions[] = "vendor_id LIKE ?";
            $params[] = '%' . $filters['vendor_id'] . '%';
        }
        if ($filters['part_id']) {
            $conditions[] = "part_id LIKE ?";
            $params[] = '%' . $filters['part_id'] . '%';
        }

        if ($conditions) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY entity, part_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ NEW: Get unique entities
    public function getUniqueEntities(string $orgId): array {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT entity FROM machine_parts_list 
            WHERE org_id = ? 
            ORDER BY entity
        ");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
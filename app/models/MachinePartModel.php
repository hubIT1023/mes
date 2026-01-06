<?php
// app/models/MachinePartModel.php

require_once __DIR__ . '/../config/Database.php';

class MachinePartModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getByEntity(string $orgId, string $assetId, string $entity): array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM machine_parts_list 
            WHERE org_id = ? AND asset_id = ? AND entity = ?
            ORDER BY part_name
        ");
        $stmt->execute([$orgId, $assetId, $entity]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
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
        return (bool) $stmt->execute($data);
    }

    public function update(int $id, string $orgId, array $data): bool
	{
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
		return (bool) $stmt->execute($data);
	}

    public function delete(int $id, string $orgId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM machine_parts_list WHERE id = ? AND org_id = ?");
        return (bool) $stmt->execute([$id, $orgId]);
    }

    public function partExistsForEntity(
        string $orgId,
        string $assetId,
        string $entity,
        string $partId,
        ?int $excludeId = null
    ): bool {
        $sql = "SELECT 1 FROM machine_parts_list WHERE org_id = ? AND asset_id = ? AND entity = ? AND part_id = ?";
        $params = [$orgId, $assetId, $entity, $partId];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1"; // 🔒 Add for efficiency

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    // In app/models/MachinePartModel.php

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

    $uploadDir = __DIR__ . '/../parts_img/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: $uploadDir");
            return null;
        }
    }

    $filename = 'part_' . uniqid() . '.jpg';
    $targetPath = $uploadDir . $filename;

    // Compress and resize
    if (!$this->resizeAndCompressImage($file['tmp_name'], $targetPath, 800, 75)) {
        error_log("Failed to process image");
        return null;
    }

    // Return web-accessible path
    return '/app/parts_img/' . $filename;
}

private function resizeAndCompressImage(string $source, string $dest, int $maxSize = 800, int $quality = 75): bool {
    $info = getimagesize($source);
    if (!$info) return false;

    switch ($info[2]) {
        case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($source); break;
        case IMAGETYPE_PNG:  $image = imagecreatefrompng($source); break;
        case IMAGETYPE_GIF:  $image = imagecreatefromgif($source); break;
        default: return false;
    }

    if (!$image) return false;

    $origW = imagesx($image);
    $origH = imagesy($image);
    $scale = min($maxSize / $origW, $maxSize / $origH, 1);
    $newW = (int)($origW * $scale);
    $newH = (int)($origH * $scale);

    $resized = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    $result = imagejpeg($resized, $dest, $quality);

    imagedestroy($image);
    imagedestroy($resized);
    return $result;
}

    public function getFilteredParts(string $orgId, array $filters): array
    {
        $sql = "SELECT * FROM machine_parts_list WHERE org_id = ?";
        $params = [$orgId];
        $conditions = [];

        if (!empty($filters['entity'])) {
            $conditions[] = "entity LIKE ?";
            $params[] = '%' . $filters['entity'] . '%';
        }
        if (!empty($filters['part_name'])) {
            $conditions[] = "part_name LIKE ?";
            $params[] = '%' . $filters['part_name'] . '%';
        }
        if (!empty($filters['vendor_id'])) {
            $conditions[] = "vendor_id LIKE ?";
            $params[] = '%' . $filters['vendor_id'] . '%';
        }
        if (!empty($filters['part_id'])) {
            $conditions[] = "part_id LIKE ?";
            $params[] = '%' . $filters['part_id'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY entity, part_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueEntities(string $orgId): array
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT entity FROM machine_parts_list 
            WHERE org_id = ? 
            ORDER BY entity
        ");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
	
	public function getById(int $id, string $orgId): ?array
{
    $stmt = $this->conn->prepare("
        SELECT * FROM machine_parts_list 
        WHERE id = ? AND org_id = ?
    ");
    $stmt->execute([$id, $orgId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// In app/models/MachinePartModel.php

public function updateDescription(int $id, string $orgId, string $description): bool
{
    $stmt = $this->conn->prepare("
        UPDATE machine_parts_list 
        SET description = ? 
        WHERE id = ? AND org_id = ?
    ");
    return (bool) $stmt->execute([$description, $id, $orgId]);
}
}
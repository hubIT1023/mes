<?php
// /app/models/ModeColorModel.php

require_once __DIR__ . '/../config/Database.php';

class ModeColorModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAll(string $orgId): array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM mode_color 
            WHERE org_id = ? 
            ORDER BY mode_key
        ");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $orgId, int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM mode_color 
            WHERE id = ? AND org_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id, $orgId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(string $orgId, string $mode_key, string $label, string $tailwind_class): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO mode_color (org_id, mode_key, label, tailwind_class)
            VALUES (?, ?, ?, ?)
        ");
        return (bool) $stmt->execute([$orgId, $mode_key, $label, $tailwind_class]);
    }

    public function update(int $id, string $orgId, string $mode_key, string $label, string $tailwind_class): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE mode_color 
            SET mode_key = ?, label = ?, tailwind_class = ?
            WHERE id = ? AND org_id = ?
        ");
        return (bool) $stmt->execute([$mode_key, $label, $tailwind_class, $id, $orgId]);
    }

    public function delete(int $id, string $orgId): bool
    {
        $stmt = $this->conn->prepare("
            DELETE FROM mode_color 
            WHERE id = ? AND org_id = ?
        ");
        return (bool) $stmt->execute([$id, $orgId]);
    }

    public function modeKeyExists(string $orgId, string $mode_key, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM mode_color WHERE org_id = ? AND mode_key = ?";
        $params = [$orgId, $mode_key];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1"; // 🔒 Add LIMIT for efficiency

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }
}
<?php
// /app/models/FetchGroupModel.php

class FetchGroupModel
{
    public static function fetchGroups(PDO $conn, int $tenant_id): array
    {
        try {
            $stmt = $conn->prepare("
                SELECT id, group_code, location_code, group_name, location_name,
                       org_id, created_at, page_id, page_name, seq_id
                FROM group_location_map 
                WHERE org_id = ? AND group_name != '---'
                ORDER BY page_id, COALESCE(seq_id, 9999), created_at
            ");
            $stmt->execute([$tenant_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DB error fetching groups: " . $e->getMessage());
            return [];
        }
    }
}
<?php
// /app/models/FetchPageModel.php

class FetchPageModel
{
    public static function fetchAllPages(PDO $conn, int $tenant_id): array
    {
        try {
            $stmt = $conn->prepare("
                SELECT DISTINCT page_id, page_name
                FROM group_location_map 
                WHERE org_id = ?
                ORDER BY page_id
            ");
            $stmt->execute([$tenant_id]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            error_log("DB error fetching pages: " . $e->getMessage());
            return [];
        }
    }
}
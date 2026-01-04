<?php
// /app/models/FetchAssetModel.php

class FetchAssetModel
{
    public static function fetchTenantAssets(PDO $conn, int $tenant_id): array
    {
        try {
            $stmt = $conn->prepare("SELECT asset_id, asset_name FROM assets WHERE tenant_id = ?");
            $stmt->execute([$tenant_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DB error fetching tenant assets: " . $e->getMessage());
            return [];
        }
    }
}
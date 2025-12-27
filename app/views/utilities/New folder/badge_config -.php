<?php
// /app/views/utilities/tool_card//badge_config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    $entities = [];
    return;
}

global $pdo;

if (!$pdo) {
    require_once __DIR__ . '/../../../../../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
    if (!$pdo) {
        $entities = [];
        return;
    }
}

if (!isset($group)) {
    $entities = [];
    return;
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM registered_tools
        WHERE group_code = :group_code
          AND location_code = :location_code
          AND org_id = :org_id
        ORDER BY entity ASC
    ");
    $stmt->execute([
        'group_code' => $group['group_code'],
        'location_code' => $group['location_code'],
        'org_id' => $orgId
    ]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching entities: " . $e->getMessage());
    $entities = [];
}

/**
 * Fetches state configurations from the database, caches them, and returns
 * the label and CSS class for a given state key.
 *
 * @param string $state The state key (label) to look up (e.g., 'PROD').
 * @global PDO $pdo A database connection object.
 * @global string $orgId The ID of the current organization.
 * @return array An associative array with 'label' and 'class' keys.
 */
function getStateBadge(string $state): array
{
    // Caches the state configurations to avoid multiple database queries per request.
    static $stateConfigs = null;
    global $pdo, $orgId;

    // Load config only once per request.
    if ($stateConfigs === null) {
        // Fallback if no database access is available.
        if (!$pdo || !$orgId) {
            return ['label' => $state, 'class' => 'btn-secondary'];
        }

        try {
            // Updated SQL query to search for the state in the 'label' column.
            $sql = "
                SELECT state_key, label, css_class
                FROM state_config
                WHERE org_id = ? OR org_id = 'default'
                ORDER BY CASE WHEN org_id = ? THEN 0 ELSE 1 END
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$orgId, $orgId]);

            $stateConfigs = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                // Correctly using the state_key column for the lookup key.
                $key = strtoupper($row['state_key']);
                // Organization-specific configurations override the 'default' ones.
                if (!isset($stateConfigs[$key])) {
                    $stateConfigs[$key] = [
                        'label' => $row['label'],
                        'class' => $row['css_class']
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Failed to load state_config: " . $e->getMessage());
            $stateConfigs = []; // Ensure the variable is set to prevent repeated attempts.
        }
    }

    $key = strtoupper($state);

    // Return the config if found, otherwise provide a consistent fallback.
    return $stateConfigs[$key] ?? [
        'label' => $key,
        'class' => 'btn-primary'
        //'class' => 'btn-secondary'
    ];
}



/*
// State mapping function
function getStateBadge(string $state): array
{
    static $stateConfigs = null;
    global $pdo, $orgId;

    // ? Load config only once per request
    if ($stateConfigs === null) {
        if (!$pdo || !$orgId) {
            // Fallback if no DB access
            return ['label' => $state, 'class' => 'btn-secondary'];
        }

        try {
            $sql = "
                SELECT state_key, label, css_class
                FROM state_config
                WHERE org_id = ? OR org_id = 'default'
                ORDER BY 
                    CASE WHEN org_id = ? THEN 0 ELSE 1 END
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$orgId, $orgId]);

            $stateConfigs = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $key = strtoupper($row['state_key']);
                // Org-specific overrides 'default'
                if (!isset($stateConfigs[$key])) {
                    $stateConfigs[$key] = [
                        'label' => $row['label'],
                        'class' => $row['css_class']
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Failed to load state_config: " . $e->getMessage());
            $stateConfigs = []; 
        }
    }

    
    $key = strtoupper($state);
    return $stateConfigs[$key] ?? [
        'label' => $key,
        'class' => 'bg-custom-prod'  // Bootstrap 4/5 compatible
    ];
}
*/
<?php
// /app/views/utilities/tool_card/entity_toolState_card.php


//error_reporting(E_ALL);
//ini_set('display_errors', 1);


if (!isset($group) || !isset($org_id) || !isset($conn)) {
    echo "<div class='alert alert-danger'>Error: Missing required context (group, org_id, or conn).</div>";
    return;
}

$groupCode = (int)($group['group_code'] ?? 0);
$locationCode = (int)($group['location_code'] ?? 0);
$locationName = htmlspecialchars($group['location_name'] ?? 'Unknown Location');

// === Fetch entities ===
try {
    $stmt = $conn->prepare("
        SELECT id, asset_id, entity, group_code, location_code, row_pos, col_pos
        FROM registered_tools
        WHERE group_code = :group_code
          AND location_code = :location_code
          AND org_id = :org_id
        ORDER BY row_pos, col_pos
    ");
    $stmt->execute([
        'group_code' => $groupCode,
        'location_code' => $locationCode,
        'org_id' => $org_id
    ]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB error fetching entities: " . $e->getMessage());
    $entities = [];
}

// === Fetch tool states ===
try {
    $stmt = $conn->prepare("
        SELECT col_2 AS entity, col_3 AS stop_cause
        FROM tool_state
        WHERE org_id = :org_id
          AND group_code = :group_code
          AND location_code = :location_code
    ");
    $stmt->execute([
        'org_id' => $org_id,
        'group_code' => $groupCode,
        'location_code' => $locationCode
    ]);
    $states = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log("DB error fetching states: " . $e->getMessage());
    $states = [];
}

// === Fetch $modeChoices for state dropdown ===
$modeChoices = [];
try {
    $stmt = $conn->prepare("SELECT mode_key, label FROM mode_color WHERE org_id = ? ORDER BY label");
    $stmt->execute([$org_id]);
    $modeChoices = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log("DB error fetching mode choices: " . $e->getMessage());
    $modeChoices = [];
}

// === Helper: getStateBadge ===
if (!function_exists('getStateBadge')) {
    function getStateBadge(string $state, $conn, string $org_id) {
        static $cache = [];
        $cacheKey = "$org_id|" . strtoupper(trim($state));
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $fallback = [
            'label' => strtoupper(trim($state)) ?: 'UNKNOWN',
            'class' => 'bg-gray-500'
        ];

        try {
            $stmt = $conn->prepare("SELECT label, tailwind_class FROM mode_color WHERE org_id = ? AND mode_key = ?");
            $stmt->execute([$org_id, strtoupper(trim($state))]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $result = $row ? ['label' => $row['label'], 'class' => $row['tailwind_class']] : $fallback;
        } catch (PDOException $e) {
            error_log("getStateBadge DB error: " . $e->getMessage());
            $result = $fallback;
        }

        $cache[$cacheKey] = $result;
        return $result;
    }
}

// === Helper: renderDataAttributes ===
if (!function_exists('renderDataAttributes')) {
    function renderDataAttributes(
        string $assetId,
        string $entityName,
        int $groupCode,
        int $locationCode,
        string $locationName,
        string $dateTime
    ) {
        $attrs = [
            'data-asset-id' => htmlspecialchars($assetId),
            'data-header' => htmlspecialchars($entityName),
            'data-group-code' => $groupCode,
            'data-location-code' => $locationCode,
            'data-location-name' => htmlspecialchars($locationName),
            'data-date' => htmlspecialchars($dateTime),
        ];

        foreach ($attrs as $key => $val) {
            echo "$key=\"$val\" ";
        }
    }
}

// === Build grid ===
$maxRow = 1;
$grid = [];

foreach ($entities as $entity) {
    $r = (int)$entity['row_pos'];
    $c = (int)$entity['col_pos'];
    $maxRow = max($maxRow, $r);
    if ($c >= 1 && $c <= 5) {
	//if ($c >= 1 && $c <= 5) {	
        $grid[$r][$c] = $entity;
    }
}

// === CSRF Token Safety ===
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<?php
// /app/views/utilities/tool_card/entity_toolState_card.php

if (!isset($group, $org_id, $conn)) {
    echo "<div class='alert alert-danger'>Error: Missing required context (group, org_id, or conn).</div>";
    return;
}

define('MAX_COLS', 9);

$groupCode = (int)($group['group_code'] ?? 0);
$locationCode = (int)($group['location_code'] ?? 0);
$locationName = htmlspecialchars($group['location_name'] ?? 'Unknown Location');

// ======================
// === Helper Functions ===
// ======================

if (!function_exists('getStateBadge')) {
    function getStateBadge(string $state, PDO $conn, string $org_id): array {
        static $cache = [];
        $cacheKey = "$org_id|$state";
        if (isset($cache[$cacheKey])) return $cache[$cacheKey];

        $fallback = ['label' => strtoupper(trim($state)) ?: 'UNKNOWN', 'class' => 'bg-gray-500'];
        try {
            $stmt = $conn->prepare("SELECT label, tailwind_class FROM mode_color WHERE org_id = ? AND mode_key = ?");
            $stmt->execute([$org_id, strtoupper(trim($state))]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $result = $row ? ['label' => $row['label'], 'class' => $row['tailwind_class']] : $fallback;
        } catch (PDOException $e) {
            error_log("getStateBadge DB error: " . $e->getMessage());
            $result = $fallback;
        }

        return $cache[$cacheKey] = $result;
    }
}

if (!function_exists('renderDataAttributes')) {
    function renderDataAttributes(array $entity, string $locationName, string $dateTime) {
        $attrs = [
            'data-asset-id' => $entity['asset_id'] ?? '',
            'data-header' => $entity['entity'] ?? '',
            'data-group-code' => $entity['group_code'] ?? '',
            'data-location-code' => $entity['location_code'] ?? '',
            'data-location-name' => $locationName,
            'data-date' => $dateTime
        ];
        foreach ($attrs as $key => $value) {
            echo sprintf('%s="%s" ', $key, htmlspecialchars($value));
        }
    }
}

// ======================
// === Fetch Data ===
// ======================

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

// ======================
// === Build Grid ===
// ======================

$maxRow = 1;
$grid = [];

foreach ($entities as $entity) {
    $r = (int)$entity['row_pos'];
    $c = (int)$entity['col_pos'];
    if ($c >= 1 && $c <= MAX_COLS) $grid[$r][$c] = $entity;
    $maxRow = max($maxRow, $r);
}

$currentDateTime = date('Y-m-d H:i:s');

?>

<!-- ====================== -->
<!-- === Tool Cards Grid === -->
<!-- ====================== -->

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-4">
<?php for ($row = 1; $row <= $maxRow; $row++): ?>
    <?php for ($col = 1; $col <= MAX_COLS; $col++): ?>
        <?php if (isset($grid[$row][$col])): 
            $entity = $grid[$row][$col];
            $assetId = htmlspecialchars($entity['asset_id']);
            $entityName = htmlspecialchars($entity['entity']);
            $stopCause = $states[$entityName] ?? 'IDLE';
            $badge = getStateBadge($stopCause, $conn, $org_id);
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex justify-between items-start p-2">
                <button class="flex-grow text-left text-sm font-semibold text-blue-700"
                        data-bs-toggle="modal"
                        data-bs-target="#associateAccessoriesModal"
                        <?php renderDataAttributes($entity, $locationName, $currentDateTime); ?>>
                    <?= $entityName ?>
                    <div class="text-xs text-gray-500">Pos: (<?= $row ?>, <?= $col ?>)</div>
                </button>
                <button class="btn btn-sm btn-light text-primary flex-shrink-0"
                        data-bs-toggle="modal"
                        data-bs-target="#editPositionModal_<?= (int)$entity['id'] ?>">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
            <button class="w-full py-2 text-white font-bold <?= htmlspecialchars($badge['class']) ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#changeStateModal"
                    <?php renderDataAttributes($entity, $locationName, $currentDateTime); ?>>
                <?= htmlspecialchars($badge['label']) ?>
            </button>
        </div>
        <?php else: ?>
        <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg h-24 flex items-center justify-center"></div>
        <?php endif; ?>
    <?php endfor; ?>
<?php endfor; ?>
</div>

<!-- ====================== -->
<!-- === Modals Section === -->
<!-- ====================== -->

<?php include __DIR__ . '/modals/associateAccessoriesModal.php'; ?>
<?php include __DIR__ . '/modals/changeStateModal.php'; ?>

<!-- ====================== -->
<!-- === JS Section === -->
<!-- ====================== -->

<script>
document.addEventListener('DOMContentLoaded', function() {

    function handleStopCauseChange(selectEl) {
        const container = document.getElementById('customInputContainer');
        const customInput = document.getElementById('ts_customInput');

        if (selectEl.value === 'CUSTOM') {
            container.style.display = 'block';
            selectEl.removeAttribute('name');
            customInput.setAttribute('name', 'col_3');
        } else {
            container.style.display = 'none';
            selectEl.setAttribute('name', 'col_3');
            customInput.removeAttribute('name');
        }
    }

    function populateModal(modalId, btn) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const mapping = {
            assetId: btn.getAttribute('data-asset-id'),
            entity: btn.getAttribute('data-header'),
            groupCode: btn.getAttribute('data-group-code'),
            locationCode: btn.getAttribute('data-location-code'),
            locationName: btn.getAttribute('data-location-name'),
            dateTime: btn.getAttribute('data-date')
        };

        modal.querySelectorAll('[data-field]').forEach(el => {
            const key = el.getAttribute('data-field');
            if (mapping[key] !== undefined) el.value = mapping[key];
        });
    }

    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', e => {
            const targetId = btn.getAttribute('data-bs-target').replace('#', '');
            populateModal(targetId, btn);
        });
    });

    window.handleStopCauseChange = handleStopCauseChange;

});
</script>

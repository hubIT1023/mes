<?php
// /app/views/utilities/tool_card/entity_toolState_card.php

if (!isset($group) || !isset($org_id) || !isset($conn)) {
    echo "<div class='alert alert-danger'>Error: Missing required context.</div>";
    return;
}

$groupCode = (int)($group['group_code'] ?? 0);
$groupName = htmlspecialchars($group['group_name'] ?? 'Unknown Group');
$locationCode = (int)($group['location_code'] ?? 0);
$locationName = htmlspecialchars($group['location_name'] ?? 'Unknown Location');

// Fetch entities and states (Queries kept as per your logic)
try {
    $stmt = $conn->prepare("SELECT id, asset_id, entity, group_code, location_code, row_pos, col_pos FROM registered_tools WHERE group_code = :gc AND location_code = :lc AND org_id = :org ORDER BY row_pos, col_pos");
    $stmt->execute(['gc' => $groupCode, 'lc' => $locationCode, 'org' => $org_id]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT col_2 AS entity, col_3 AS stop_cause FROM tool_state WHERE org_id = :org AND group_code = :gc AND location_code = :lc");
    $stmt->execute(['org' => $org_id, 'gc' => $groupCode, 'lc' => $locationCode]);
    $states = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $entities = $states = [];
}

// Helper for Data Attributes to maintain DRY principle
function getCardDataAttrs($assetId, $entityName, $groupCode, $groupName, $locationCode, $locationName) {
    return sprintf(
        'data-asset-id="%s" data-entity="%s" data-group-code="%s" data-group-name="%s" data-location-code="%s" data-location-name="%s" data-date="%s"',
        htmlspecialchars($assetId), htmlspecialchars($entityName), $groupCode, htmlspecialchars($groupName), $locationCode, htmlspecialchars($locationName), date('Y-m-d H:i:s')
    );
}

// Build grid logic
$maxRow = 1;
$grid = [];
foreach ($entities as $entity) {
    $r = (int)$entity['row_pos'];
    $c = (int)$entity['col_pos'];
    $maxRow = max($maxRow, $r);
    if ($c >= 1 && $c <= 5) $grid[$r][$c] = $entity;
}

$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<div class="row row-cols-2 row-cols-md-5 g-4">
    <?php for ($row = 1; $row <= $maxRow; $row++): ?>
        <?php for ($col = 1; $col <= 5; $col++): ?>
            <div class="col">
                <?php if (isset($grid[$row][$col])): 
                    $entity = $grid[$row][$col];
                    $stopCause = $states[$entity['entity']] ?? 'IDLE';
                    $badge = getStateBadge($stopCause, $conn, $org_id);
                    $dataAttrs = getCardDataAttrs($entity['asset_id'], $entity['entity'], $groupCode, $groupName, $locationCode, $locationName);
                ?>
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between">
                            <button class="btn btn-link p-0 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#associateAcc-PartsModal" <?= $dataAttrs ?>>
                                <?= htmlspecialchars($entity['entity']) ?>
                            </button>
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editPositionModal_<?= $entity['id'] ?>">
                                <i class="fas fa-map-pin"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="badge rounded-pill bg-primary-subtle text-primary mb-2" data-bs-toggle="modal" data-bs-target="#LoadWorkModal" <?= $dataAttrs ?>>
                                WIP
                            </div>
                            <button class="btn <?= htmlspecialchars($badge['class']) ?> w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#setMaintModal" <?= $dataAttrs ?>>
                                <?= htmlspecialchars($badge['label']) ?>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="border border-dashed rounded-3 bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-plus text-muted opacity-25"></i>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    <?php endfor; ?>
</div>

<div class="modal fade" id="associatePartsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="AddPartsForm" method="POST" action="/mes/machine-parts" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Associate Machine Parts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="org_id" value="<?= $org_id ?>">
                    <input type="hidden" name="asset_id" class="fill-asset-id">
                    <input type="hidden" name="group_code" class="fill-group-code">
                    <input type="hidden" name="location_code" class="fill-location-code">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control fill-location-name" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Entity</label>
                            <input type="text" name="entity" class="form-control fill-entity" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Part Name/Code</label>
                        <input type="text" name="mfg_code" class="form-control" placeholder="ex. Akim Motor" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Association</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentContext = {};

    // 1. Capture data when any modal-triggering button is clicked
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.dataset.assetId) {
                currentContext = {
                    assetId: this.dataset.assetId,
                    entity: this.dataset.entity,
                    groupCode: this.dataset.groupCode,
                    groupName: this.dataset.groupName,
                    locationCode: this.dataset.locationCode,
                    locationName: this.dataset.locationName,
                    date: this.dataset.date
                };
            }
        });
    });

    // 2. Inject data when a modal is shown
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function () {
            // Fill inputs by class name to allow multiple fields to be updated
            modal.querySelectorAll('.fill-asset-id').forEach(el => el.value = currentContext.assetId || '');
            modal.querySelectorAll('.fill-entity').forEach(el => el.value = currentContext.entity || '');
            modal.querySelectorAll('.fill-group-code').forEach(el => el.value = currentContext.groupCode || '');
            modal.querySelectorAll('.fill-location-code').forEach(el => el.value = currentContext.locationCode || '');
            modal.querySelectorAll('.fill-location-name').forEach(el => el.value = currentContext.locationName || '');
            
            // Special handling for text displays (if not inputs)
            const titleEntity = modal.querySelector('.display-entity');
            if (titleEntity) titleEntity.textContent = currentContext.entity;
        });
    });
});
</script>
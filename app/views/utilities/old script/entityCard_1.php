<?php
// /app/views/utilities/tool_card/entity_toolState_card_1.php
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
            'class' => 'bg-secondary'
        ];
        try {
            $stmt = $conn->prepare("SELECT label, tailwind_class FROM mode_color WHERE org_id = ? AND mode_key = ?");
            $stmt->execute([$org_id, strtoupper(trim($state))]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Map Tailwind class → Bootstrap class if needed
            $bootstrapClass = match (true) {
                str_contains($row['tailwind_class'] ?? '', 'bg-green') => 'bg-success',
                str_contains($row['tailwind_class'] ?? '', 'bg-blue') => 'bg-primary',
                str_contains($row['tailwind_class'] ?? '', 'bg-yellow') => 'bg-warning',
                str_contains($row['tailwind_class'] ?? '', 'bg-red') => 'bg-danger',
                default => $row['tailwind_class'] ?? 'bg-secondary'
            };
            $result = $row ? ['label' => $row['label'], 'class' => $bootstrapClass] : $fallback;
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
            'data-group-code' => (string)$groupCode,
            'data-location-code' => (string)$locationCode,
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
    if ($c >= 1 && $c <= 9) {
        $grid[$r][$c] = $entity;
    }
}

// === CSRF Token Safety ===
$csrfToken = $_SESSION['csrf_token'] ?? '';
$currentDateTime = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Asset Card - Bootstrap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; padding: 40px; }
        .custom-card {
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            transition: transform 0.2s;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .custom-card:hover { transform: translateY(-5px); }
        .pulse-dot {
            width: 8px; height: 8px; border-radius: 50%; position: relative;
        }
        .pulse-dot::after {
            content: ''; position: absolute; width: 100%; height: 100%;
            background: inherit; border-radius: 50%; animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            70% { transform: scale(2.5); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 576px) { .grid-container { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px) { .grid-container { grid-template-columns: repeat(5, 1fr); } }
        @media (min-width: 992px) { .grid-container { grid-template-columns: repeat(9, 1fr); } }
        .empty-cell {
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

<div class="grid-container">
<?php for ($row = 1; $row <= $maxRow; $row++): ?>
    <?php for ($col = 1; $col <= 9; $col++): ?>
        <?php if (isset($grid[$row][$col])): ?>
            <?php
            $entity = $grid[$row][$col];
            $assetId = $entity['asset_id'];
            $entityName = $entity['entity'];
            $stopCause = $states[$entityName] ?? 'IDLE';
            $badge = getStateBadge($stopCause, $conn, $org_id);
            ?>
            <div class="d-flex justify-content-center">
                <div class="card custom-card border-0 p-4">
                    <!-- Card Header -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <button
                            class="btn btn-link text-start p-0 text-decoration-none text-primary fw-semibold"
                            data-bs-toggle="modal"
                            data-bs-target="#associateAcc-PartsModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="View details for <?= htmlspecialchars($entityName) ?>"
                        >
                            <?= htmlspecialchars($entityName) ?>
                        </button>
                        <button
                            class="btn btn-sm btn-light text-primary p-1"
                            data-bs-toggle="modal"
                            data-bs-target="#editPositionModal_<?= (int)$entity['id'] ?>"
                            aria-label="Edit position"
                        >
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                    </div>

                    <!-- WIP / Pulse Badge -->
                    <div class="d-flex flex-column gap-2 align-items-end mb-4">
                        <div class="badge bg-primary-subtle text-primary d-flex align-items-center gap-2 px-3 py-2" style="font-size: 0.75rem;">
                            <div class="pulse-dot bg-primary"></div>
                            <div
                                class="text-start small fw-medium text-dark bg-light px-2 py-1 rounded cursor-pointer"
                                data-bs-toggle="modal"
                                data-bs-target="#LoadWorkModal"
                                <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            >
                                WIP
                            </div>
                        </div>
                    </div>

                    <!-- Compliance Bars -->
                    <div class="bg-light rounded-4 p-3 mb-4 border">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-bold text-secondary mb-1" style="font-size: 11px;">
                                <span>WOF COMPLIANCE</span>
                                <span class="text-warning">Due: 01 Sep</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 75%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small fw-bold text-secondary mb-1" style="font-size: 11px;">
                                <span>CALIBRATION</span>
                                <span class="text-primary">Due: 14 Oct</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- State Button -->
                    <div class="mb-4">
                        <button
                            type="button"
                            class="btn w-100 py-2 text-white fw-bold rounded
                                <?= htmlspecialchars($badge['class']) ?> 
                                hover-opacity-90 transition-all"
                            data-bs-toggle="modal"
                            data-bs-target="#setMaintModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="Current state: <?= htmlspecialchars($badge['label']) ?>"
                        >
                            <?= htmlspecialchars($badge['label']) ?>
                        </button>
                    </div>

                    <!-- Metrics -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="p-2 border rounded-3 bg-white">
                                <div class="small text-muted fw-bold" style="font-size: 10px;">TEMP</div>
                                <div class="h5 fw-bold m-0">84<span class="small text-muted fw-normal">°C</span></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded-3 bg-white">
                                <div class="small text-muted fw-bold" style="font-size: 10px;">PRESSURE</div>
                                <div class="h5 fw-bold m-0">107<span class="small text-muted fw-normal">bar</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Downtime Chart -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold">5-Day Downtime</span>
                            <span class="small text-muted">Total: 4.2h</span>
                        </div>
                        <div class="d-flex align-items-end gap-1" style="height: 40px;">
                            <div class="bg-light flex-grow-1 rounded-top" style="height: 40%;"></div>
                            <div class="bg-light flex-grow-1 rounded-top" style="height: 70%;"></div>
                            <div class="bg-light flex-grow-1 rounded-top" style="height: 30%;"></div>
                            <div class="bg-danger flex-grow-1 rounded-top" style="height: 90%;"></div>
                            <div class="bg-light flex-grow-1 rounded-top" style="height: 50%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Position Modal -->
            <div class="modal fade" id="editPositionModal_<?= (int)$entity['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form action="/mes/update-entity-position" method="POST">
                        <input type="hidden" name="entity_id" value="<?= (int)$entity['id'] ?>">
                        <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Position: <?= htmlspecialchars($entityName) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Row</label>
                                        <input type="number" class="form-control" name="row_pos"
                                            value="<?= (int)$entity['row_pos'] ?>" min="1" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Column</label>
                                        <input type="number" class="form-control" name="col_pos"
                                            value="<?= (int)$entity['col_pos'] ?>" min="1" max="9" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">Columns 1–9 per row</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Move</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-center">
                <div class="empty-cell">Empty Slot</div>
            </div>
        <?php endif; ?>
    <?php endfor; ?>
<?php endfor; ?>
</div>

<!-- =============================== -->
<!-- SHARED MODALS -->
<!-- =============================== -->

<!-- associateAcc-PartsModal -->
<div class="modal fade" id="associateAcc-PartsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title">ASSOCIATE PARTS/ACCESSORIES</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <button type="button" class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#associateAccessoriesModal"
                        data-use-stored-context="true">
                        ASSOCIATE ACCESSORIES
                    </button>
                    <button type="button" class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#associatePartsModal"
                        data-use-stored-context="true">
                        ASSOCIATE PARTS
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- setMaintModal -->
<div class="modal fade" id="setMaintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">More Actions</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <button type="button" class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#changeStateModal"
                        data-use-stored-context="true">
                        Change State
                    </button>
                    <button type="button" class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#standingIssueModal"
                        data-use-stored-context="true">
                        Post Standing Issue
                    </button>
                    <button type="button" class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#associateAccessoriesModal"
                        data-use-stored-context="true">
                        Maint Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ASSOCIATE PARTS MODAL -->
<div class="modal fade" id="associatePartsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form id="AddPartsForm" method="POST" action="/mes/machine-parts" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="associatePartsModalLabel">Associate Machine Parts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="asset_id" id="ap_modal_asset_id_hidden">
                    <input type="hidden" name="entity" id="ap_modal_entity_hidden">
                    <input type="hidden" name="group_code" id="ap_modal_group_code">
                    <input type="hidden" name="location_code" id="ap_modal_location_code">
                    <input type="hidden" name="col_1" id="ap_modal_asset_id">
                    <input type="hidden" name="col_6" id="ap_modal_date_time">
                    <input type="hidden" name="col_7" id="ap_modal_start_time">

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Location</label>
                            <input type="text" id="ap_modal_location" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Entity</label>
                            <input type="text" id="ap_ipt_entity" name="col_2" class="form-control" readonly />
                        </div>
                        <div class="col">
                            <label class="form-label">Asset ID</label>
                            <input type="text" id="ap_modal_asset_id_display" class="form-control" readonly />
                        </div>
                        <div class="col">
                            <label class="form-label">Maker</label>
                            <input type="text" name="mfg_code" class="form-control" placeholder="ex. Akim">
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Part ID *</label>
                            <input type="text" name="part_id" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Part Name *</label>
                            <input type="text" name="part_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Serial No</label>
                            <input type="text" name="serial_no" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Vendor ID</label>
                            <input type="text" name="vendor_id" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">SAP Code</label>
                            <input type="text" name="sap_code" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="">-- Select Priority Level --</option>
                                <option value="HIGH">HIGH</option>
                                <option value="MEDIUM">MEDIUM</option>
                                <option value="LOW">LOW</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Part Image (Optional)</label>
                        <input type="file" name="part_image" class="form-control" accept="image/*">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Added By *</label>
                            <input type="text" name="col_8" id="ap_posted_by" class="form-control" placeholder="Type Your Name" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Date / Time</label>
                            <input type="text" class="form-control" id="ap_modal_datetime_display" value="<?= htmlspecialchars(date('Y-m-d H:i:s')) ?>" readonly />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="ap_submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="ap_spinner" role="status"></span>
                        <span id="ap_submitText">ADD</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- CHANGE STATE MODAL -->
<div class="modal fade" id="changeStateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form id="toolStateForm" method="POST" action="/mes/change-tool-state">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="changeStateModalLabel">Change Entity Mode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="group_code" id="ts_modal_group_code">
                    <input type="hidden" name="location_code" id="ts_modal_location_code">
                    <input type="hidden" name="col_1" id="ts_modal_asset_id">
                    <input type="hidden" name="col_6" id="ts_modal_date_time">
                    <input type="hidden" name="col_7" id="ts_modal_start_time">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Location</label>
                            <input type="text" id="ts_modal_location" class="form-control" readonly />
                        </div>
                        <div class="col">
                            <label class="form-label">Group</label>
                            <input type="text" id="ts_modal_group" class="form-control" readonly />
                        </div>
                        <div class="col">
                            <label class="form-label">Date / Time</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars(date('Y-m-d H:i:s')) ?>" readonly />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Asset ID</label>
                            <input type="text" id="ts_modal_asset_id_display" class="form-control" readonly />
                        </div>
                        <div class="col">
                            <label class="form-label">Entity</label>
                            <input type="text" id="ts_ipt_entity" name="col_2" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <select id="ts_modal_stopcause" name="col_3" class="form-select" required onchange="handleStopCauseChange(this.value)">
                                <option value="">Select stop cause</option>
                                <?php foreach ($modeChoices as $mode_key => $label): ?>
                                    <option value="<?= htmlspecialchars($mode_key) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                                <option value="CUSTOM">Other (specify)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="customInputContainer" style="display:none;">
                        <label class="form-label">Custom Stop Cause</label>
                        <input type="text" id="ts_customInput" class="form-control" name="col_3" />
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Issue(s)</label>
                            <input type="text" name="col_4" id="ts_modal_issue" list="issueOptions" class="form-control" required />
                            <datalist id="issueOptions"></datalist>
                        </div>
                        <div class="col">
                            <label class="form-label">Action(s)</label>
                            <input type="text" name="col_5" id="ts_modal_action" list="actionOptions" class="form-control" required />
                            <datalist id="actionOptions"></datalist>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Posted By</label>
                        <input type="text" name="col_8" id="ts_posted_by" class="form-control" placeholder="Type Your Name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="ts_submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="ts_spinner" role="status"></span>
                        <span id="ts_submitText">Submit</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript: Unified data flow for ALL modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentEntityContext = null;

function captureContextFromButton(btn) {
    return {
        assetId: btn.getAttribute('data-asset-id'),
        entity: btn.getAttribute('data-header'),
        groupCode: btn.getAttribute('data-group-code'),
        locationCode: btn.getAttribute('data-location-code'),
        locationName: btn.getAttribute('data-location-name'),
        dateTime: btn.getAttribute('data-date')
    };
}

['setMaintModal', 'associateAcc-PartsModal'].forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (btn && btn.hasAttribute('data-asset-id')) {
                currentEntityContext = captureContextFromButton(btn);
            }
        });
    }
});

const actionModals = [
    'changeStateModal', 'standingIssueModal', 'associateAccessoriesModal',
    'associatePartsModal', 'LoadWorkModal'
];

actionModals.forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const ctx = btn?.hasAttribute('data-asset-id') 
            ? captureContextFromButton(btn)
            : (btn?.hasAttribute('data-use-stored-context') && currentEntityContext ? currentEntityContext : null);
        if (!ctx) return;

        // Handle full-form modals
        if (modalId === 'associatePartsModal') {
            document.getElementById('ap_ipt_entity').value = ctx.entity;
            document.getElementById('ap_modal_asset_id').value = ctx.assetId;
            document.getElementById('ap_modal_asset_id_display').value = ctx.assetId;
            document.getElementById('ap_modal_group_code').value = ctx.groupCode;
            document.getElementById('ap_modal_location_code').value = ctx.locationCode;
            document.getElementById('ap_modal_location').value = ctx.locationName;
            document.getElementById('ap_modal_date_time').value = ctx.dateTime;
            document.getElementById('ap_modal_start_time').value = ctx.dateTime;
            document.getElementById('ap_modal_asset_id_hidden').value = ctx.assetId;
            document.getElementById('ap_modal_entity_hidden').value = ctx.entity;
            document.getElementById('associatePartsModalLabel').textContent = 'Add Part to: ' + ctx.entity;
        } else if (modalId === 'changeStateModal') {
            document.getElementById('ts_ipt_entity').value = ctx.entity;
            document.getElementById('ts_modal_asset_id').value = ctx.assetId;
            document.getElementById('ts_modal_asset_id_display').value = ctx.assetId;
            document.getElementById('ts_modal_group_code').value = ctx.groupCode;
            document.getElementById('ts_modal_location_code').value = ctx.locationCode;
            document.getElementById('ts_modal_location').value = ctx.locationName;
            document.getElementById('ts_modal_group').value = ctx.groupCode;
            document.getElementById('ts_modal_date_time').value = ctx.dateTime;
            document.getElementById('ts_modal_start_time').value = ctx.dateTime;
            document.getElementById('changeStateModalLabel').textContent = 'Change Mode: ' + ctx.entity;
        }
        // Add other modals as needed
    });
});

function handleStopCauseChange(value) {
    const container = document.getElementById('customInputContainer');
    const stopCauseSelect = document.getElementById('ts_modal_stopcause');
    const customInput = document.getElementById('ts_customInput');
    if (value === 'CUSTOM') {
        container.style.display = 'block';
        customInput.setAttribute('name', 'col_3');
        stopCauseSelect.removeAttribute('name');
    } else {
        container.style.display = 'none';
        customInput.removeAttribute('name');
        stopCauseSelect.setAttribute('name', 'col_3');
    }
}
</script>

</body>
</html>
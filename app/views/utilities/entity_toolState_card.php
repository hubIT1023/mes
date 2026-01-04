<?php
// /app/views/utilities/tool_card/entity_toolState_card.php

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
    if ($c >= 1 && $c <= 9) {
        $grid[$r][$c] = $entity;
    }
}

// === CSRF Token Safety ===
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<!-- Tool State Cards Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-4">
    <?php for ($row = 1; $row <= $maxRow; $row++): ?>
        <?php for ($col = 1; $col <= 9; $col++): ?>
            <?php if (isset($grid[$row][$col])): ?>
                <?php
                $entity = $grid[$row][$col];
                $assetId = $entity['asset_id'];
                $entityName = $entity['entity'];
                $currentDateTime = date('Y-m-d H:i:s');
                $stopCause = $states[$entityName] ?? 'IDLE';
                $badge = getStateBadge($stopCause, $conn, $org_id);
                ?>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Header -->
                    <div class="flex justify-between items-start p-2 border-b border-gray-100">
                        <button
                            class="flex-grow text-left text-sm font-semibold text-blue-700 hover:underline focus:outline-none"
                            data-bs-toggle="modal"
                            data-bs-target="#associateAcc-PartsModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="View details for <?= htmlspecialchars($entityName) ?>"
                        >
                            <?= htmlspecialchars($entityName) ?>
                            <!-- <div class="text-xs text-gray-500 mt-1">Pos: (<?= (int)$row ?>, <?= (int)$col ?>)</div> -->
                        </button>
                        <button
                            class="btn btn-sm btn-light text-primary flex-shrink-0 p-1"
                            data-bs-toggle="modal"
                            data-bs-target="#editPositionModal_<?= (int)$entity['id'] ?>"
                            aria-label="Edit position"
                        >
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-2 space-y-2">
                        <!-- WOF Due -->
                        <div
                            class="text-left text-xs font-medium text-gray-700 py-1 px-2 bg-yellow-50 rounded hover:bg-yellow-100 cursor-pointer"
                            data-bs-toggle="modal"
                            data-bs-target="#LoadWorkModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="WOF Due for <?= htmlspecialchars($entityName) ?>"
                        >
                            WOF Due
                        </div>

                        <!-- Cal Due -->
                        <div
                            class="text-left text-xs font-medium text-gray-700 py-1 px-2 bg-blue-50 rounded hover:bg-blue-100 cursor-pointer"
                            data-bs-toggle="modal"
                            data-bs-target="#CalDueModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="Calibration Due"
                        >
                            Cal Due
                        </div>

                        <!-- Load Work -->
                        <div
                            class="text-left text-xs font-medium text-gray-700 py-1 px-2 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer"
                            data-bs-toggle="modal"
                            data-bs-target="#LoadWorkModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="Load work"
                        >
                            Load Work to Process
                        </div>

                        <!-- State Badge -->
                        <button
                            class="w-full py-2 text-white font-bold rounded transition-all hover:opacity-90 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 <?= htmlspecialchars($badge['class']) ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#setMaintModal"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>
                            aria-label="Current state: <?= htmlspecialchars($badge['label']) ?>"
                        >
                            <?= htmlspecialchars($badge['label']) ?>
                        </button>
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
                <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg h-24 flex items-center justify-center"></div>
            <?php endif; ?>
        <?php endfor; ?>
    <?php endfor; ?>
</div>

<!-- =============================== -->
<!-- SHARED MODALS -->
<!-- =============================== -->

<!-- associateAcc-PartsModal -->
<div class="modal fade" id="associateAcc-PartsModal" tabindex="-1" aria-labelledby="associateAccPartsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="associateAccPartsModalLabel">ASSOCIATE PARTS/ACCESSORIES</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <button
                        type="button"
                        class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#associateAccessoriesModal"
                        data-use-stored-context="true"
                    >
                        ASSOCIATE ACCESSORIES
                    </button>
                    <button
                        type="button"
                        class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#associatePartsModal"
                        data-use-stored-context="true"
                    >
                        ASSOCIATE PARTS
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- setMaintModal -->
<div class="modal fade" id="setMaintModal" tabindex="-1" aria-labelledby="setMaintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="setMaintModalLabel">More Actions</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <button
                        type="button"
                        class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#changeStateModal"
                        data-use-stored-context="true"
                    >
                        Change State
                    </button>
                    <button
                        type="button"
                        class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#standingIssueModal"
                        data-use-stored-context="true"
                    >
                        Post Standing Issue
                    </button>
                    <button
                        type="button"
                        class="list-group-item list-group-item-action"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#associateAccessoriesModal"
                        data-use-stored-context="true"
                    >
                        Maint Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ASSOCIATE ACCESSORIES (Simple version) -->
<div class="modal fade" id="associateAccessoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title">Associate Accessories</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/mes/associate-accessories">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="parent_asset_id" id="acc_asset_id"> <!-- Renamed to avoid conflict -->
                    <input type="hidden" name="parent_entity" id="acc_entity">
                    
                    <!-- Display parent entity -->
                    <div class="mb-3">
                        <label class="form-label">Parent Tool</label>
                        <input class="form-control" type="text" id="acc_entity_display" readonly>
                    </div>

                    <p class="mb-2">Associate a new asset with this tool</p>
                    
                    <!-- Child asset fields -->
                    <div class="mb-2">
                        <input class="form-control" name="child_asset_id" placeholder="Child Asset ID" required>
                    </div>
                    <div class="mb-2">
                        <input class="form-control" name="child_asset_name" placeholder="Child Asset Name" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="operator" placeholder="Operator" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">ASSOCIATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LOAD WORK MODAL -->
<div class="modal fade" id="LoadWorkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title">LOAD WORK TO PROCESS</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="asset_id" id="lw_asset_id">
                    <input type="hidden" name="entity" id="lw_entity">
                    <input type="hidden" name="group_code" id="lw_group_code">
                    <input type="hidden" name="location_code" id="lw_location_code">
                    <input class="form-control mb-2" placeholder="Material No." required>
                    <input class="form-control mb-2" type="number" placeholder="Quantity" min="1" required>
                    <input class="form-control mb-2" placeholder="Operator" required>
                    <button type="submit" class="btn btn-primary w-100">LOAD</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CAL DUE MODAL -->
<div class="modal fade" id="CalDueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-blue-600 text-white">
                <h6 class="modal-title">CALIBRATION DUE</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <p>Calibration is due soon.</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- STANDING ISSUE MODAL -->
<div class="modal fade" id="standingIssueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Standing Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="asset_id" id="si_asset_id">
                    <div class="mb-3">
                        <label class="form-label">Issue Description</label>
                        <textarea class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reported By</label>
                        <input class="form-control" placeholder="Your name" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Post Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ASSOCIATE PARTS MODAL (Full Form) -->
<div class="modal fade" id="associatePartsModal" tabindex="-1" aria-labelledby="associatePartsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form id="AddPartsForm" method="POST" action="/mes/machine-parts" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="associatePartsModalLabel">Associate Machine Parts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                    <hr class="divider my-0 mb-3">

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
                        <span class="spinner-border spinner-border-sm d-none" id="ap_spinner" role="status" aria-hidden="true"></span>
                        <span id="ap_submitText">ADD</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- CHANGE STATE MODAL -->
<div class="modal fade" id="changeStateModal" tabindex="-1" aria-labelledby="changeStateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form id="toolStateForm" method="POST" action="/mes/change-tool-state">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStateModalLabel">Change Entity Mode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <select id="ts_modal_stopcause" name="col_3" class="form-control" required onchange="handleStopCauseChange(this.value)">
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
<script>
let currentEntityContext = null;

// Capture context from ANY button with data attributes
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

// Handle both setMaintModal and associateAcc-PartsModal
['setMaintModal', 'associateAcc-PartsModal'].forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (btn.hasAttribute('data-asset-id')) {
                currentEntityContext = captureContextFromButton(btn);
            }
        });
    }
});

// Handle all action modals
const actionModals = [
    'changeStateModal', 'standingIssueModal', 'associateAccessoriesModal',
    'associatePartsModal', 'LoadWorkModal'
];

actionModals.forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;

        // Direct trigger
        if (btn.hasAttribute('data-asset-id')) {
            currentEntityContext = captureContextFromButton(btn);
        }

        // From gateway modals
        if (btn.hasAttribute('data-use-stored-context') && currentEntityContext) {
            const ctx = currentEntityContext;
            const prefixMap = {
                'LoadWorkModal': 'lw',
                'standingIssueModal': 'si',
                'associateAccessoriesModal': 'acc',
                'associatePartsModal': 'ap'
            };

            // Handle simple modals
            if (modalId === 'LoadWorkModal') {
                document.getElementById('lw_asset_id').value = ctx.assetId;
                document.getElementById('lw_entity').value = ctx.entity;
                document.getElementById('lw_group_code').value = ctx.groupCode;
                document.getElementById('lw_location_code').value = ctx.locationCode;
            } else if (modalId === 'standingIssueModal') {
                document.getElementById('si_asset_id').value = ctx.assetId;
            } else if (modalId === 'associateAccessoriesModal') {
                document.getElementById('acc_asset_id').value = ctx.assetId;
                document.getElementById('acc_entity').value = ctx.entity;
                document.getElementById('acc_entity_display').value = ctx.entity;
            }
            // Handle full-form modals
            else if (modalId === 'associatePartsModal') {
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
        }
    });
});

function handleStopCauseChange(value) {
    const container = document.getElementById('customInputContainer');
    if (value === 'CUSTOM') {
        container.style.display = 'block';
        document.getElementById('ts_customInput').setAttribute('name', 'col_3');
        document.querySelector('#ts_modal_stopcause').removeAttribute('name');
    } else {
        container.style.display = 'none';
        document.getElementById('ts_customInput').removeAttribute('name');
        document.querySelector('#ts_modal_stopcause').setAttribute('name', 'col_3');
    }
}
</script>
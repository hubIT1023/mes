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
    if ($c >= 1 && $c <= 5) {
	//if ($c >= 1 && $c <= 5) {	
        $grid[$r][$c] = $entity;
    }
}

// === CSRF Token Safety ===
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<style>
    .downtime-chart { cursor: pointer; }
</style>
<!-- Tool State Cards Grid -->
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 row-cols-lg-5 g-4">
    <?php for ($row = 1; $row <= $maxRow; $row++): ?>
        <?php for ($col = 1; $col <= 5; $col++): ?>
            <div class="col">
                <?php if (isset($grid[$row][$col])): ?>
                    <?php
                        $entity = $grid[$row][$col];
                        $assetId = $entity['asset_id'];
                        $entityName = $entity['entity'];
                        $currentDateTime = date('Y-m-d H:i:s');
                        $stopCause = $states[$entityName] ?? 'IDLE';
                        $badge = getStateBadge($stopCause, $conn, $org_id);
                    ?>

                    <div class="card h-100 shadow-sm border-1 rounded-3 transition-all hover-shadow">
                        <div class="card-header bg-light border-bottom-0 p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex flex-column align-items-start gap-1">
                                    <div class="d-flex align-items-center gap-2">
									
									<!--------------DROPDOWN LIST ------------------------------------------------------------------->
                                        <!--i class="fas fa-microchip text-secondary small"></i-->
										<div class="dropdown">
											<a class="btn btn-sm border-0" 
											   href="#" 
											   id="alertsDropdown" 
											   role="button" 
											   data-bs-toggle="dropdown" 
											   aria-expanded="false">
												<i class="fas fa-list text-secondary"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-0 overflow-hidden" 
												 aria-labelledby="alertsDropdown" 
												 style="min-width: 280px;">
												
												<h6 class="dropdown-header bg-danger text-white py-3" style="font-size: 1.1rem;">
													<i class="fas fa-exclamation-triangle me-2"></i> Breakdown Details
												</h6>

												<a class="dropdown-item d-flex align-items-center py-3 border-bottom" href="#">
													<div class="me-3">
														<div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
															<i class="fas fa-wrench text-white"></i>
														</div>
													</div>
													<div>
														<div class="fw-bold text-danger small">Breakdown Issue(s)</div>
														<div class="text-muted small">
															<?php // brkdwn_info($entity); ?>
															Motor Overheat Detected
														</div>
													</div>
												</a>

												<a class="dropdown-item d-flex align-items-center py-3 border-bottom" href="#">
													<div class="me-3">
														<div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
															<i class="far fa-clock text-white"></i>
														</div>
													</div>
													<div>
														<div class="fw-bold text-danger small">Downtime</div>
														<div class="text-muted small">
															<?php // downtime($entity); ?>
															02h 45m
														</div>
													</div>
												</a>

												<a class="dropdown-item d-flex align-items-center py-3" href="#">
													<div class="me-3">
														<div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
															<i class="fas fa-tools text-white"></i>
														</div>
													</div>
													<div>
														<div class="fw-bold text-warning small">Standing Issue(s)</div>
														<div class="text-muted small">
															<?php // standing_issue($entity); ?>
															Pending Part Replacement
														</div>
													</div>
												</a>
											</div>
										</div>
										
										<!--------------------------------------------------------------------------------->
										
                                        <button class="btn btn-link p-0 fw-bold text-decoration-none text-dark fs-5 lh-1"
                                            data-bs-toggle="modal" data-bs-target="#associateAcc-PartsModal"
                                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>>
                                            <?= htmlspecialchars($entityName) ?>
                                        </button>
                                    </div>

                                
                                </div>

                                <button class="btn btn-sm btn-light border p-1 rounded-2 text-primary"
                                    data-bs-toggle="modal" data-bs-target="#editPositionModal_<?= (int)$entity['id'] ?>">
                                    <i class="fas fa-map-pin"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3 pt-0">
					
                            <div class="mb-1" data-bs-toggle="modal" data-bs-target="#CalDueModal" style="cursor: pointer;">
                                <div class="d-flex justify-content-between small fw-bold mb-0">
                                    <span class="text-muted" style="font-size: 10px;">WOF</span>
                                    <span class="text-primary" style="font-size: 10px;">Due: 14 Oct</span>
                                </div>
                                <div class="progress bg-light" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                            </div>

                            <div class="mb-3" data-bs-toggle="modal" data-bs-target="#CalDueModal" style="cursor: pointer;">
                                <div class="d-flex justify-content-between small fw-bold mb-0">
                                    <span class="text-muted" style="font-size: 10px;">CAL</span>
                                    <span class="text-primary" style="font-size: 10px;">Due: 14 Oct</span>
                                </div>
                                <div class="progress bg-light" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 65%"></div>
                                </div>
                            </div>
							
							<div class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center gap-2 px-2 py-1" 
									 style="font-size: 0.7rem; cursor: pointer;"
									 data-bs-toggle="modal" data-bs-target="#LoadWorkModal"
									 <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>>
									<span class="spinner-grow spinner-grow-sm text-primary" role="status" style="width: 8px; height: 8px;"></span>
									WIP
							</div>
                            
                            <div class="row g-2 mb-1 text-center py-2 ">
							
							 <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-body-tertiary">
                                        <div class="text-muted fw-bold mb-1" style="font-size: 9px;"> Actual OPT</div>
                                        <div class="h6 fw-bold m-0">1200<small class="fw-normal">cnts</small></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-body-tertiary">
                                        <div class="text-muted fw-bold mb-1" style="font-size: 9px;">Target OPT</div>
                                        <div class="h6 fw-bold m-0">3000<small class="fw-normal text-muted">cnts</small></div>
                                    </div>
                                </div>
							
                                <!--div class="col-6">
                                    <div class="p-2 border rounded-3 bg-body-tertiary">
                                        <div class="text-muted fw-bold mb-1" style="font-size: 9px;">TEMP</div>
                                        <div class="h6 fw-bold m-0">84<small class="fw-normal">°C</small></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-body-tertiary">
                                        <div class="text-muted fw-bold mb-1" style="font-size: 9px;">PRESSURE</div>
                                        <div class="h6 fw-bold m-0">107<small class="fw-normal text-muted">b</small></div>
                                    </div>
                                </div-->
                            </div>

                            <button class="btn <?= htmlspecialchars($badge['class']) ?> w-100 fw-bold py-2 mb-3 shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#setMaintModal"
                                <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>>
                                <?= htmlspecialchars($badge['label']) ?>
                            </button>
                            
                            <div class="border-top pt-2">
									<div class="d-flex justify-content-between align-items-center mb-1">
										<span class="fw-bold" style="font-size: 10px;">5-DAY DOWNTIME</span>
										<span class="text-muted" style="font-size: 10px;">Total: 4.2h</span>
									</div>
									<div style="height: 80px; width: 100%; position: relative;">
										<canvas class="downtime-chart" 
												data-chart-values="[40, 70, 30, 90, 50]" 
												data-chart-labels='["Mon", "Tue", "Wed", "Thu", "Fri"]'
												data-chart-notes='["Scheduled Maintenance", "Motor Failure", "Sensor Calibration", "Line Jam", "Cleaning"]'
												data-chart-colors='["#d1e7dd", "#f8d7da", "#d1e7dd", "#f8d7da", "#fff3cd"]'
												data-chart-borders='["#198754", "#dc3545", "#198754", "#dc3545", "#ffc107"]'>
										</canvas>

								</div>
                            </div>
                        </div>
                    </div>

                   	<!-- =============================== -->
					<!-- GRIRD --Edit Position Modal -->
					<!-- =============================== -->			

					
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
											<small class="text-muted">Columns 1–5 per row</small>
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
                    <div class="d-flex align-items-center justify-content-center border border-2 border-dashed rounded-3 bg-light opacity-50" style="height: 280px;">
                        <i class="fas fa-plus-circle text-muted opacity-25 fa-2x"></i>
                    </div>
                <?php endif; ?>
            </div>
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
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title" id="associateAccPartsModalLabel">ASSOCIATE PARTS/ACCESSORIES</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <!--button type="button" class="list-group-item list-group-item-action"
                            data-bs-dismiss="modal"
                            data-bs-toggle="modal"
                            data-bs-target="#listAccessoriesModal"
                            data-use-stored-context="true">
                        Associated Accessories
                    </button-->
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
<!--div class="modal fade" id="associateAccessoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title">Associate Accessories</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<? //= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="org_id" value="<? //= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="asset_id" id="acc_asset_id">
                    <input type="hidden" name="entity" id="acc_entity">
                    <input class="form-control mb-2" type="text" name="entity_display" id="acc_entity_display" readonly>
                    <textarea class="form-control mb-2" name="issue" placeholder="Issue" required></textarea>
                    <textarea class="form-control mb-2" name="action" placeholder="Action taken" required></textarea>
                    <input class="form-control mb-2" name="operator" placeholder="Operator" required>
                    <button type="submit" class="btn btn-primary w-100">ASSOCIATE</button>
                </div>
            </form>
        </div>
    </div>
</div-->

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
                    <input type="hidden" name="csrf_token" value="<?//= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="org_id" value="<?//= htmlspecialchars($org_id) ?>">
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



<!-- STANDING ISSUE MODAL -->

<div class="modal fade" id="standingIssueModal" tabindex="-1" aria-labelledby="associatePartsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-md">
        <form id="AddPartsForm" method="POST" action="/mes/machine-parts" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
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
                <div class="modal-header bg-info text-white">
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
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="changeStateModalLabel">Change Entity Mode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="group_code" id="ts_modal_group_code">
                    <input type="hidden" name="location_code" id="ts_modal_location_code">
                    <input type="hidden" name="col_1" id="ts_modal_asset_id">
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
                            <input type="text" class="form-control" id="ts_dateTime_now" name="col_6" 
							value="<?= htmlspecialchars(date('Y-m-d H:i:s')) ?>" readonly />
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
                            <select id="ts_modal_stopcause" name="col_3" class="form-control" 
							          required onchange="handleStopCauseChange(this.value)">
                                <option value="">Select stop cause</option>
                                <?php foreach ($modeChoices as $mode_key => $label): ?>
                                    <option value="<?= htmlspecialchars($mode_key) ?>">
											<?= htmlspecialchars($label) ?>
									</option>
                                <?php endforeach; ?>
                                <!--option value="CUSTOM">Other (specify)</option-->
                            </select>
                        </div>
                    </div>

                    <!-- 🔴 FIXED: Removed name="col_3" from input -->
                    <!--div class="mb-3" id="customInputContainer" style="display:none;">
                        <label class="form-label">Custom Stop Cause</label>
                        <input type="text" id="ts_customInput" class="form-control" />
                    </div-->

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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const charts = document.querySelectorAll('.downtime-chart');
    
    charts.forEach(canvas => {
        try {
            // 1. Parse all data attributes at once
            const values = JSON.parse(canvas.dataset.chartValues || '[]');
            const labels = JSON.parse(canvas.dataset.chartLabels || '[]');
            const notes  = JSON.parse(canvas.dataset.chartNotes  || '[]');
            const colors = JSON.parse(canvas.dataset.chartColors || '[]');
            const borders = JSON.parse(canvas.dataset.chartBorders || '[]');

            // 2. Initialize the Chart
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels, 
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: borders,
                        borderWidth: { top: 2, right: 0, bottom: 0, left: 0 },
                        borderRadius: 3,
                        barPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const value = context.parsed.y;
                                    const reason = notes[index] || 'No reason specified';
                                    
                                    // Returns an array for multi-line display
                                    return [
                                        'Downtime: ' + value + 'h',
                                        'Reason: ' + reason
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: { display: false },
                        y: { 
                            display: false, 
                            beginAtZero: true,
                            suggestedMax: Math.max(...values, 10) 
                        }
                    }
                }
            });
        } catch (e) {
            console.error("Chart initialization failed for element:", canvas, e);
        }
    });
});
</script>



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
    'changeStateModal',
    'standingIssueModal',
    'associateAccessoriesModal',
    'associatePartsModal',
    'LoadWorkModal'
];

actionModals.forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;

        // Direct trigger: capture fresh context
        if (btn.hasAttribute('data-asset-id')) {
            currentEntityContext = captureContextFromButton(btn);
        }

        // Triggered via gateway modal → use stored context
        if (btn.hasAttribute('data-use-stored-context') && currentEntityContext) {
            const ctx = currentEntityContext;

            if (modalId === 'LoadWorkModal') {
                document.getElementById('lw_asset_id').value = ctx.assetId;
                document.getElementById('lw_entity').value = ctx.entity;
                document.getElementById('lw_group_code').value = ctx.groupCode;
                document.getElementById('lw_location_code').value = ctx.locationCode;

            } else if (modalId === 'associateAccessoriesModal') {
                document.getElementById('acc_asset_id').value = ctx.assetId;
                document.getElementById('acc_entity').value = ctx.entity;
                document.getElementById('acc_entity_display').value = ctx.entity;

            } else if (modalId === 'standingIssueModal') {
                // ✅ FIXED: Populate the correct ap_* fields used in the standing issue form
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
                document.querySelector('#standingIssueModal .modal-title').textContent = 'Post Standing Issue: ' + ctx.entity;

            } else if (modalId === 'associatePartsModal') {
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
                // Note: ts_modal_date_time doesn't exist; using current time on backend is fine
                document.getElementById('changeStateModalLabel').textContent = 'Change Mode: ' + ctx.entity;
            }
        }
    });
});

function handleStopCauseChange(value) {
    const container = document.getElementById('customInputContainer');
    const select = document.getElementById('ts_modal_stopcause');
    const customInput = document.getElementById('ts_customInput');
    if (value === 'CUSTOM') {
        container.style.display = 'block';
        select.removeAttribute('name');
        customInput.setAttribute('name', 'col_3');
    } else {
        container.style.display = 'none';
        select.setAttribute('name', 'col_3');
        customInput.removeAttribute('name');
    }
}
</script>
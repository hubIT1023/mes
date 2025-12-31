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
        WHERE group_code 		= :group_code
          AND location_code 	= :location_code
          AND org_id 			= :org_id
        ORDER BY row_pos, col_pos
    ");
    $stmt->execute([
        'group_code' 	=> $groupCode,
        'location_code' => $locationCode,
        'org_id' 		=> $org_id
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
        WHERE org_id 		= :org_id
          AND group_code 	= :group_code
          AND location_code = :location_code
    ");
    $stmt->execute([
        'org_id' 		=> $org_id,
        'group_code' 	=> $groupCode,
        'location_code' => $locationCode
    ]);
    $states = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log("DB error fetching states: " . $e->getMessage());
    $states = [];
}

// === Fetch data  getStateBadge===
if (!function_exists('getStateBadge')) {
    function getStateBadge(string $state, $conn, string $org_id) {
		static $cache = [];

		$cacheKey = "$org_id|$state";
		if (isset($cache[$cacheKey])) {
			return $cache[$cacheKey];
		}

		// Default fallback
		$fallback = [
			'label' => strtoupper(trim($state)) ?: 'UNKNOWN',
			'class' => 'bg-gray-500'
		];

		try {
			$stmt = $conn->prepare("
				SELECT label, tailwind_class 
				FROM mode_color 
				WHERE org_id = ? AND mode_key = ?
			");
			$stmt->execute([$org_id, strtoupper(trim($state))]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row) {
				$result = [
					'label' => $row['label'],
					'class' => $row['tailwind_class']
				];
			} else {
				$result = $fallback;
			}
		} catch (PDOException $e) {
			error_log("getStateBadge DB error: " . $e->getMessage());
			$result = $fallback;
		}

		$cache[$cacheKey] = $result;
		return $result;
	}
}

if (!function_exists('renderDataAttributes')) {
    function renderDataAttributes(
        string $assetId,
        string $entityName,
        int $groupCode,
        int $locationCode,
        string $locationName,
        string $dateTime
    ) {
        echo 'data-asset-id			="' . htmlspecialchars($assetId) . '" ';
        echo 'data-header			="' . htmlspecialchars($entityName) . '" ';
        echo 'data-group-code		="' . $groupCode . '" ';
        echo 'data-location-code	="' . $locationCode . '" ';
        echo 'data-location-name	="' . htmlspecialchars($locationName) . '" ';
        echo 'data-date				="' . htmlspecialchars($dateTime) . '" ';
    }
}

// === Determine max row ===
$maxRow = 1;
foreach ($entities as $entity) {
    $maxRow = max($maxRow, (int)$entity['row_pos']);
}

// === Build grid lookup ===
$grid = [];
foreach ($entities as $entity) {
    $r = (int)$entity['row_pos'];
    $c = (int)$entity['col_pos'];
    if ($c >= 1 && $c <= 9) {
        $grid[$r][$c] = $entity;
    }
}
?>

<!-- Tool State Cards Grid -->
<div class="grid-container" style="display: grid; grid-template-columns: repeat(9, 1fr); gap: 1rem;">
    <?php for ($row = 1; $row <= $maxRow; $row++): ?>
        <?php for ($col = 1; $col <= 9; $col++): ?>
            <?php if (isset($grid[$row][$col])): ?>
                <?php
                $entity = $grid[$row][$col];
                $assetId = htmlspecialchars($entity['asset_id']);
                $entityName = htmlspecialchars($entity['entity']);
                $currentDateTime = date('Y-m-d H:i:s');
                $stopCause = $states[$entityName] ?? 'IDLE';
                $badge = getStateBadge($stopCause, $conn, $org_id);
                ?>
                <div class="card" style="width: 160px;">
                    <!-- Entity Card Header + Edit Button -->
				<div class="d-flex align-items-start justify-content-between w-100" style="min-height: 60px;">
					<!-- Entity Name (as button) -->
					<button type="button"
							class="bg-white text-primary small text-start border-0 flex-grow-1 me-2"
							data-bs-toggle="modal"
							data-bs-target="#associateAccessoriesModal"
							style="padding: 0.5rem 0.75rem; text-align: left;"
							<?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>>
						<strong><?= $entityName ?></strong>
						<small class="text-muted d-block mt-1">Pos: (<?= $row ?>, <?= $col ?>)</small>
					</button>

					<!-- Edit Position Button -->
					<button class="btn btn-sm btn-light text-primary flex-shrink-0" 
							title="Edit"
							data-bs-toggle="modal" 
							data-bs-target="#editPositionModal_<?= (int)$entity['id'] ?>">
						<i class="fas fa-edit"></i>
					</button>
				</div>
					
					

                    <!-- State Badge -->
                    <button type="button"
                            class="card-body d-flex align-items-center justify-content-center text-white large fw-bold <?= $badge['class'] ?> w-100 border-1"
                            data-bs-toggle="modal"
                            data-bs-target="#changeStateModal"
                            style="height: 60px; padding: 0; margin: 0;"
                            <?php renderDataAttributes($assetId, $entityName, $groupCode, $locationCode, $locationName, $currentDateTime); ?>>
                        <?= htmlspecialchars($badge['label']) ?>
                    </button>
                </div>

                <!-- Edit Position Modal -->
                <div class="modal fade" id="editPositionModal_<?= (int)$entity['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="/mes/update-entity-position" method="POST">
                            <input type="hidden" name="entity_id" value="<?= (int)$entity['id'] ?>">
                            <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Position: <?= $entityName ?></h5>
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
                                    <button type="submit" class="btn btn-primary">Move </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- Empty Placeholder -->
                <div class="card" style="width: 162px; border: 2px dashed #ccc; background-color: #f9f9f9;">
                    <div class="card-body d-flex align-items-center justify-content-center text-muted" style="height: 100px;">
                        <!--button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#addEntityModal_<?= (int)$group['group_code'] ?>">
                            + Add
                        </button-->
                    </div>
                </div>
            <?php endif; ?>
        <?php endfor; ?>
    <?php endfor; ?>
</div>



<!-- Modal: Add Machine Parts -->
<div class="modal fade" id="associateAccessoriesModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <form id="AddAccesoriesForm" method="POST" action="/mes/machine-parts" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="associateAccessoriesModalLabel">Associate Machine Parts</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">

          <!-- Hidden Inputs (Populated by JS) -->
          <input type="hidden" name="asset_id" id="acc_modal_asset_id_hidden">
          <input type="hidden" name="entity" id="acc_modal_entity_hidden">
          <input type="hidden" name="group_code" id="acc_modal_group_code">
          <input type="hidden" name="location_code" id="acc_modal_location_code">
          <input type="hidden" name="col_1" id="acc_modal_asset_id"> <!-- asset_id -->
          <input type="hidden" name="col_3" id="acc_tool_state_col3">
          <input type="hidden" name="col_6" id="acc_modal_date_time">
          <input type="hidden" name="col_7" id="acc_modal_start_time">

          <!-- Display Fields -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Location</label>
              <input type="text" id="acc_modal_location" class="form-control" readonly />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Entity</label>
              <input type="text" id="acc_ipt_entity" name="col_2" class="form-control" readonly />
            </div>
            <div class="col">
              <label class="form-label">Asset ID</label>
              <input type="text" id="acc_modal_asset_id_display" class="form-control" readonly />
            </div>
			<div class="col">
                <label class="form-label">Maker</label>
                <input type="text" name="mfg_code" class="form-control" Placeholder = "ex. Akim">
            </div>
          </div>

          <div class="mb-3">
            <hr class="divider my-0">
          </div>

          <!-- Part Details -->
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
            <textarea name="description" class="form-control" ></textarea>
          </div>
			
			
          <!-- Image Upload -->
          <div class="mb-3">
            <label class="form-label">Part Image (Optional)</label>
            <input type="file" name="part_image" class="form-control" accept="image/*">
          </div>


          <div class="row mb-3">
            <div class="col">
                <label class="form-label">Added By *</label>
                <input type="text"  name="col_8" id="acc_posted_by" class="form-control"value="" placeholder="Type Your Name" required>
            </div>
            <div class="col">
                <label class="form-label">Date / Time</label>
                <input type="text" class="form-control" id="acc_modal_datetime_display" value="<?= htmlspecialchars(date('Y-m-d H:i:s')) ?>" readonly />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="acc_submitBtn">
            <span class="spinner-border spinner-border-sm d-none" id="acc_spinner" role="status" aria-hidden="true"></span>
            <span id="acc_submitText">ADD</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>



<!-- Modal: Change Entity Mode -->
<div class="modal fade" id="changeStateModal" tabindex="-1" 
     aria-labelledby="changeStateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form id="toolStateForm" method="POST" action="/mes/change-tool-state">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="changeStateModalLabel">Change Entity Mode</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Hidden Inputs -->
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
          <input type="hidden" name="group_code" id="ts_modal_group_code">
          <input type="hidden" name="location_code" id="ts_modal_location_code">
          <input type="hidden" name="col_1" id="ts_modal_asset_id">
          <input type="hidden" name="col_6" id="ts_modal_date_time">
          <input type="hidden" name="col_7" id="ts_modal_start_time">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

          <!-- Display Fields -->
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

          <!-- Stop Cause -->
          <div class="row mb-3">
            <div class="col">
              <select id="ts_modal_stopcause" name="col_3" class="form-control" required onchange="handleStopCauseChange(this.value)">
				<option value="">Select stop cause</option>
				<?php foreach ($modeChoices as $mode_key => $label): ?>
					<option value="<?= htmlspecialchars($mode_key) ?>">
						<?= htmlspecialchars($label) ?>
					</option>
				<?php endforeach; ?>
				<option value="CUSTOM">Other (specify)</option>
			</select>
            </div>
          </div>

          <!-- Custom Input -->
          <div class="mb-3" id="customInputContainer" style="display:none;">
            <label class="form-label">Custom Stop Cause</label>
            <input type="text" id="ts_customInput" class="form-control" />
          </div>

          <!-- Issues / Actions -->
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

<!-- JavaScript for Modal Initialization -->

<script>
function handleStopCauseChange(value) {
    const container = document.getElementById('customInputContainer');
    const customInput = document.getElementById('ts_customInput');
    const select = document.getElementById('ts_modal_stopcause');
    
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('changeStateModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const assetId = btn.getAttribute('data-asset-id');
        const entity = btn.getAttribute('data-header');
        const groupCode = btn.getAttribute('data-group-code');
        const locationCode = btn.getAttribute('data-location-code');
        const locationName = btn.getAttribute('data-location-name');
        const dateTime = btn.getAttribute('data-date');

        // Populate fields
        document.getElementById('ts_ipt_entity').value = entity;
        document.getElementById('ts_modal_asset_id').value = assetId;
        document.getElementById('ts_modal_asset_id_display').value = assetId;
        document.getElementById('ts_modal_group_code').value = groupCode;
        document.getElementById('ts_modal_location_code').value = locationCode;
        document.getElementById('ts_modal_location').value = locationName;
        document.getElementById('ts_modal_group').value = groupCode;
        document.getElementById('ts_modal_date_time').value = dateTime;
        document.getElementById('ts_modal_start_time').value = dateTime;
        document.getElementById('changeStateModalLabel').textContent = 'Change Mode: ' + entity;
    });
});

// Handle custom stop cause
function handleStopCauseChange(value) {
    const container = document.getElementById('customInputContainer');
    const customInput = document.getElementById('ts_customInput');
    const select = document.getElementById('ts_modal_stopcause');
    
    if (value === 'CUSTOM') {
        container.style.display = 'block';
        select.removeAttribute('name'); // Remove name from select
        customInput.setAttribute('name', 'col_3'); // Add name to custom input
    } else {
        container.style.display = 'none';
        select.setAttribute('name', 'col_3'); // Restore name to select
        customInput.removeAttribute('name');
    }
}
</script>


<!-- Modal Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('associateAccessoriesModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const assetId = btn.getAttribute('data-asset-id');
        const entity = btn.getAttribute('data-header');
        const groupCode = btn.getAttribute('data-group-code');
        const locationCode = btn.getAttribute('data-location-code');
        const locationName = btn.getAttribute('data-location-name');
        const dateTime = btn.getAttribute('data-date');

        // Populate display fields
        document.getElementById('acc_ipt_entity').value = entity;
        document.getElementById('acc_modal_asset_id').value = assetId;
        document.getElementById('acc_modal_asset_id_display').value = assetId;
        document.getElementById('acc_modal_group_code').value = groupCode;
        document.getElementById('acc_modal_location_code').value = locationCode;
        document.getElementById('acc_modal_location').value = locationName;
        document.getElementById('acc_modal_date_time').value = dateTime;
        document.getElementById('acc_modal_start_time').value = dateTime;
        document.getElementById('acc_modal_datetime_display').value = dateTime;
        document.getElementById('associateAccessoriesModalLabel').textContent = 'Add Part to: ' + entity;

        // Set hidden fields for form submission
        document.getElementById('acc_modal_asset_id_hidden').value = assetId;
        document.getElementById('acc_modal_entity_hidden').value = entity;
    });
});
</script>
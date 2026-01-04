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
<!-- === Edit Position Modal === -->
<!-- ====================== -->
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



<!-- ====================== -->
<!-- === Associate Accessories Modal === -->
<!-- ====================== -->
<div class="modal fade" id="associateAccessoriesModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <form id="AddAccessoriesForm" method="POST" action="/mes/machine-parts" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="associateAccessoriesModalLabel">Associate Machine Parts</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">

          <!-- Hidden Inputs (Populated by JS) -->
          <input type="hidden" name="asset_id" id="acc_modal_asset_id_hidden" data-field="assetId">
          <input type="hidden" name="entity" id="acc_modal_entity_hidden" data-field="entity">
          <input type="hidden" name="group_code" id="acc_modal_group_code" data-field="groupCode">
          <input type="hidden" name="location_code" id="acc_modal_location_code" data-field="locationCode">
          <input type="hidden" name="col_6" id="acc_modal_date_time" data-field="dateTime">

          <!-- Display Fields -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Location</label>
              <input type="text" id="acc_modal_location" class="form-control" readonly data-field="locationName" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Entity</label>
              <input type="text" id="acc_ipt_entity" name="col_2" class="form-control" readonly data-field="entity"/>
            </div>
            <div class="col">
              <label class="form-label">Asset ID</label>
              <input type="text" id="acc_modal_asset_id_display" class="form-control" readonly data-field="assetId"/>
            </div>
          </div>

          <!-- Other fields (Part ID, Name, Serial, Vendor, SAP, Category, Description, Image) -->
          <div class="row mb-3">
            <div class="col"><label>Part ID *</label><input type="text" name="part_id" class="form-control" required></div>
            <div class="col"><label>Part Name *</label><input type="text" name="part_name" class="form-control" required></div>
          </div>
          <div class="row mb-3">
            <div class="col"><label>Serial No</label><input type="text" name="serial_no" class="form-control"></div>
            <div class="col"><label>Vendor ID</label><input type="text" name="vendor_id" class="form-control"></div>
          </div>
          <div class="row mb-3">
            <div class="col"><label>SAP Code</label><input type="text" name="sap_code" class="form-control"></div>
            <div class="col"><label>Category</label>
              <select name="category" class="form-select">
                <option value="">-- Select Priority Level --</option>
                <option value="HIGH">HIGH</option>
                <option value="MEDIUM">MEDIUM</option>
                <option value="LOW">LOW</option>
              </select>
            </div>
          </div>
          <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
          <div class="mb-3"><label>Part Image</label><input type="file" name="part_image" class="form-control" accept="image/*"></div>

          <div class="row mb-3">
            <div class="col"><label>Added By *</label><input type="text" name="col_8" class="form-control" placeholder="Type Your Name" required></div>
            <div class="col"><label>Date / Time</label><input type="text" class="form-control" value="<?= $currentDateTime ?>" readonly></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ====================== -->
<!-- === Change State Modal === -->
<!-- ====================== -->
<div class="modal fade" id="changeStateModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <form method="POST" action="/mes/change-tool-state">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Entity Mode</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
          <input type="hidden" name="group_code" id="ts_modal_group_code" data-field="groupCode">
          <input type="hidden" name="location_code" id="ts_modal_location_code" data-field="locationCode">
          <input type="hidden" name="col_1" id="ts_modal_asset_id" data-field="assetId">
          <input type="hidden" name="col_6" id="ts_modal_date_time" data-field="dateTime">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

          <div class="row mb-3">
            <div class="col"><label>Asset ID</label><input type="text" id="ts_modal_asset_id_display" class="form-control" readonly data-field="assetId"></div>
            <div class="col"><label>Entity</label><input type="text" id="ts_ipt_entity" name="col_2" class="form-control" readonly data-field="entity"></div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label>Stop Cause</label>
              <select id="ts_modal_stopcause" name="col_3" class="form-control" onchange="handleStopCauseChange(this)">
                <option value="">Select stop cause</option>
                <option value="CUSTOM">Other (specify)</option>
              </select>
            </div>
          </div>

          <div class="mb-3" id="customInputContainer" style="display:none;">
            <label>Custom Stop Cause</label>
            <input type="text" id="ts_customInput" class="form-control" />
          </div>

          <div class="mb-3">
            <label>Posted By</label>
            <input type="text" name="col_8" class="form-control" placeholder="Type Your Name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

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

    window.handleStopCauseChange = handleStopCauseChange;

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
            const targetId = btn.getAttribute('data-bs-target').replace('#','');
            populateModal(targetId, btn);
        });
    });
});
</script>

<?php
// /public/forms/setToolState_modal.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    die("Organization ID missing");
}

// Use $pdo from dashboard (already connected)
global $pdo;

if (!$pdo) {
    require_once __DIR__ . '/../../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
    if (!$pdo) {
        die("Database connection failed.");
    }
}
?>

<div class="modal fade" id="toolStateModal2" tabindex="-1" 
  aria-labelledby="toolStateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form id="toolStateForm" method="POST" action="/handlers/setToolState_handler.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="toolStateModalLabel">ADD PARTICULARS</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Debug Info -->
          <div class="alert alert-info d-none" id="debugOutput">
           Location Name: <span id="dbg_location"></span><br>
            Group Code: <span id="dbg_group"></span>
          </div>

          <!-- Hidden Inputs -->
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($orgId) ?>">
          <input type="hidden" name="group_code" id="modal_group_code">
          <input type="hidden" name="location_code" id="modal_location_code">
          <input type="hidden" name="col_1" id="modal_asset_id">
          <input type="hidden" name="col_3" id="tool_state_col3">
          <input type="hidden" name="col_6" id="modal_date_time">
          <input type="hidden" name="col_7" id="modal_start_time">

          <!-- Display Fields -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Location</label>
              <input type="text" id="modal_location" class="form-control" readonly />
            </div>
            <div class="col">
              <label class="form-label">Group</label>
              <input type="text" id="modal_group" class="form-control" readonly />
            </div>
            <div class="col">
              <label class="form-label">Date / Time</label>
              <input type="text" class="form-control" value="<?= date('Y-m-d H:i:s') ?>" readonly />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Asset ID</label>
              <input type="text" id="modal_asset_id_display" class="form-control" readonly />
            </div>
            <div class="col">
              <label class="form-label">Entity</label>
              <input type="text" id="ipt_entity" name="col_2" class="form-control" readonly />
            </div>
          </div>

          <!-- Stop Cause -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Stop Cause</label>
              <select id="modal_stopcause" name="col_3" class="form-control" required onchange="handleStopCauseChange(this.value)">
                <option value="">Select stop cause</option>
                <?php
                $stmt = $pdo->prepare (
                    "SELECT code, description FROM stop_causes 
                     WHERE org_id = ? AND active = TRUE 
                     ORDER BY description"
                );

                $stmt->execute([$orgId]);
                foreach ($stmt as $cause): ?>
                    <option value="<?= htmlspecialchars($cause['code']) ?>">
                        <?= htmlspecialchars($cause['description']) ?>
                    </option>
                <?php endforeach; ?>
                <option value="IDLE">IDLE</option>
                <option value="CUSTOM">Other (specify)</option>
              </select>
            </div>
          </div>

          <!-- Custom Input -->
          <div class="mb-3" id="customInputContainer" style="display:none;">
            <label class="form-label">Custom Stop Cause</label>
            <input type="text" id="customInput" class="form-control" />
          </div>

          <!-- Issues / Actions -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Issue(s)</label>
              <input type="text" name="col_4" id="modal_issue" list="issueOptions" class="form-control" required />
              <datalist id="issueOptions"></datalist>
            </div>
            <div class="col">
              <label class="form-label">Action(s)</label>
              <input type="text" name="col_5" id="modal_action" list="actionOptions" class="form-control" required />
              <datalist id="actionOptions"></datalist>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Posted By</label>
            <input type="text" name="col_8" id="posted_by" class="form-control" placeholder="Type Your Name" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitBtn">
            <span class="spinner-border spinner-border-sm d-none" id="spinner" role="status"></span>
            <span id="submitText">Submit</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
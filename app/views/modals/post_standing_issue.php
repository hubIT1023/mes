<!-- STANDING ISSUE MODAL -->
<div class="modal fade" id="standingIssueModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form id="standingIssueForm" method="POST" action="/post-standing-issue">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Post Standing Issue</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Security & Org -->
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">

          <!-- Context (mapped to asset/location) -->
          <input type="hidden" name="col_1" id="si_asset_id">        <!-- asset_id -->
          <input type="hidden" name="col_2" id="si_entity">          <!-- entity name -->
          <input type="hidden" name="group_code" id="si_group_code">
          <input type="hidden" name="location_code" id="si_location_code">

          <!-- Standing Issue Specific Fields -->
		  
          <input type="hidden" name="col_12" value="STANDING_ISSUE"> <!-- Flag -->
          <input type="hidden" name="col_16" id="si_timestamp_start"> <!-- Start time -->

          <!-- Display-only info -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Location</label>
              <input type="text" id="si_location_display" class="form-control" readonly>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Entity</label>
              <input type="text" id="si_entity_display" class="form-control" readonly>
            </div>
            <div class="col">
              <label class="form-label">Asset ID</label>
              <input type="text" id="si_asset_id_display" class="form-control" readonly>
            </div>
          </div>

          <!-- Status -->
          <div class="mb-3">
		  <label class="form-label">Status</label>
		  <div>
			<div class="form-check form-check-inline">
			  <input class="form-check-input" type="radio" name="col_13" id="status-active" value="ACTIVE" checked>
			  <label class="form-check-label" for="status-active">Active</label>
			</div>
			<div class="form-check form-check-inline">
			  <input class="form-check-input" type="radio" name="col_13" id="status-done" value="DONE">
			  <label class="form-check-label" for="status-done">Action(s) Done</label>
			</div>
		  </div>
		</div>

          <!-- Issue Description -->
          <div class="mb-3">
            <label class="form-label">Issue Description</label>
            <textarea name="col_14" class="form-control" rows="3" placeholder="Describe the standing issue..." required></textarea>
          </div>

          <!-- Optional: Action Taken (only if status = DONE) -->
          <div class="mb-3" id="si_action_field" style="display:none;">
            <label class="form-label">Resolution / Action Taken</label>
            <textarea name="col_15" class="form-control" rows="2" placeholder="What was done to resolve it?"></textarea>
          </div>

          <!-- Reported By -->
          <div class="mb-3">
            <label class="form-label">Reported By</label>
            <input name="col_18" class="form-control" placeholder="Your name" required>
          </div>

          <button type="submit" class="btn btn-danger w-100">Post Issue</button>
        </div>
      </div>
    </form>
  </div>
</div>


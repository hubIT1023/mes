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
                    <input type="hidden" name="org_id" 		value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="group_code" 		id="ts_modal_group_code">
                    <input type="hidden" name="location_code" 	id="ts_modal_location_code">
                    <input type="hidden" name="col_1" 			id="ts_modal_asset_id">
                    <input type="hidden" name="csrf_token" 	value="<?= htmlspecialchars($csrfToken) ?>">

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
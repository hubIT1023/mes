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
                    <input type="hidden" name="csrf_token" 	value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="org_id" 		value="<?= htmlspecialchars($org_id) ?>">
                    <input type="hidden" name="asset_id" 		id="ap_modal_asset_id_hidden">
                    <input type="hidden" name="entity" 			id="ap_modal_entity_hidden">
                    <input type="hidden" name="group_code" 		id="ap_modal_group_code">
                    <input type="hidden" name="location_code" 	id="ap_modal_location_code">
                    <input type="hidden" name="col_1" 			id="ap_modal_asset_id">
                    <input type="hidden" name="col_6" 			id="ap_modal_date_time">
                    <input type="hidden" name="col_7" 			id="ap_modal_start_time">

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
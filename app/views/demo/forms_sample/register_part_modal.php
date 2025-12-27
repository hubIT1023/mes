<!-- /public/forms/register_part_modal.php -->
<div class="modal fade" id="registerPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/handler/registerPart_handler.php">
                <div class="modal-header">
                    <h5 class="modal-title">Register Tool Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Asset ID</label>
                        <input type="text" name="asset_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Part Name</label>
                        <input type="text" name="part_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Serial No</label>
                        <input type="text" name="serial_no" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned To (Entity)</label>
                        <input type="text" name="assigned_to" class="form-control" placeholder="e.g. CENTURA_4">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vendor ID</label>
                        <input type="text" name="vendor_id" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mfg Code</label>
                        <input type="text" name="mfg_code" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SAP Code</label>
                        <input type="text" name="sap_code" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parts on Hand</label>
                        <input type="number" name="parts_available_on_hand" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Part</button>
                </div>
            </form>
        </div>
    </div>
</div>
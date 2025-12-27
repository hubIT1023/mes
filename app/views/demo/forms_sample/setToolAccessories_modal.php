<!-- Modal: Add Accessories -->
<div class="modal fade" id="entityAccessoriesModal" tabindex="-1" 
     aria-labelledby="entityAccessoriesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form id="AddAccesoriesForm" method="POST" action="/handlers/xxx.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="entityAccessoriesModalLabel"></h5>
          <!--button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button-->
        </div>

        <div class="modal-body">

         <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

          <!-- Debug Info -->
          <!--div class="alert alert-info d-none" id="acc_debugOutput">
            Location Name: <span id="acc_dbg_location"></span><br>
            Group Code: <span id="acc_dbg_group"></span>
          </div-->

          <!-- Hidden Inputs -->
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($orgId) ?>">
          <input type="hidden" name="group_code" id="acc_modal_group_code">
          <input type="hidden" name="location_code" id="acc_modal_location_code">
          <input type="hidden" name="col_1" id="acc_modal_asset_id">
          <input type="hidden" name="col_3" id="acc_tool_state_col3">
          <input type="hidden" name="col_6" id="acc_modal_date_time">
          <input type="hidden" name="col_7" id="acc_modal_start_time">

          <!-- Display Fields -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Location</label>
              <input type="text" id="acc_modal_location" class="form-control" readonly />
            </div>
            <!--div class="col">
              <label class="form-label">Group</label>
              <input type="text" id="acc_modal_group" class="form-control" readonly />
            </div-->
            <!--div class="col">
              <label class="form-label">Date / Time</label>
              <input type="text" class="form-control" id="acc_modal_datetime_display" value="<?= htmlspecialchars(date('Y-m-d H:i:s')) ?>" readonly />
            </div>
          </div>

          <div class="row mb-3"-->

            <div class="col">
              <label class="form-label">Entity</label>
              <input type="text" id="acc_ipt_entity" name="col_2" class="form-control" readonly />
            </div>

            <div class="col">
              <label class="form-label">Asset ID</label>
              <input type="text" id="acc_modal_asset_id_display" class="form-control" readonly />
            </div>
            
          </div>

          <div class="mb-3">
             <hr class="divider my-0">
          </div>

          <!-- details -->
          <!--div class="row mb-3">
            <div class="col">
              <label class="form-label">Accessories Name</label>
              <input type="text" name="col_4" id="acc_modal_issue" list="issueOptions" class="form-control" required />
              
            </div>
            <div class="col">
              <label class="form-label">Description</label>
              <input type="text" name="col_5" id="acc_modal_action" list="actionOptions" class="form-control" required />
             
            </div>
          </div>

           <div class="row mb-3">
                <div class="col">
                  <label class="form-label">Accessories Asset ID</label>
                  <input type="text" name="col_4" id="acc_modal_issue" list="issueOptions" class="form-control" required />
                </div>

                <div class="col">
                  <label class="form-label">Serial No.</label>
                  <input type="text" name="col_5" id="acc_modal_action" list="actionOptions" class="form-control" required />
                </div>
            </div-->

           <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Part Asset ID</label>
                    <input type="text" name="asset_id" class="form-control" required>
                </div>

                <div class="col">
                    <label class="form-label">Part Name</label>
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

                <!--div class="col">
                    <label class="form-label">Assigned To (Entity)</label>
                    <input type="text" name="assigned_to" class="form-control" placeholder="e.g. CENTURA_4">
                </div -->
            </div>

        
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Mfg Code</label>
                    <input type="text" name="mfg_code" class="form-control">
                </div>
                <div class="col">
                    <label class="form-label">SAP Code</label>
                    <input type="text" name="sap_code" class="form-control">
                </div>

                 <div class="col">
                    <label class="form-label">Parts on Hand</label>
                    <input type="number" name="parts_available_on_hand" class="form-control" value="0">
                </div>
            </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="">
                </div>

   
          <div class="row mb-3">
            <div class="col">
                <label class="form-label">Added By</label>
                <input type="textarea" name="col_8" id="acc_posted_by" class="form-control" placeholder="Type Your Name" required>
             </div>

            <div class="col">
                  <label class="form-label">Date / Time</label>
                  <input type="text" class="form-control" id="acc_modal_datetime_display" value="<?= htmlspecialchars(date('Y-m-d H:i:s')) ?>" readonly />
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
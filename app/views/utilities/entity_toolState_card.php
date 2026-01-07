<?php
// /app/views/utilities/tool_card/entity_toolState_card.php
if (!isset($group) || !isset($org_id) || !isset($conn)) {
    echo "<div class='alert alert-danger'>Error: Missing required context (group, org_id, or conn).</div>";
    return;
}
$groupCode = (int)($group['group_code'] ?? 0);
$locationCode = (int)($group['location_code'] ?? 0);
$locationName = htmlspecialchars($group['location_name'] ?? 'Unknown Location');

…                </div>
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
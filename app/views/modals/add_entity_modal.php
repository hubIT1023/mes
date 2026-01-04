<?php
// This file is included in a loop; expects $group and $tenantAssets
?>
<div class="modal fade" id="addEntityModal_<?= (int)$group['group_code'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form action="/mes/add-entity" method="POST">
            <input type="hidden" name="group_code" value="<?= (int)$group['group_code'] ?>">
            <input type="hidden" name="location_code" value="<?= (int)$group['location_code'] ?>">
            <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Entity to <?= htmlspecialchars($group['group_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Asset</label>
                        <select class="form-select" name="asset_id" required onchange="updateEntityName(this)">
                            <option value="">-- Choose an asset --</option>
                            <?php foreach ($tenantAssets as $asset): ?>
                                <option value="<?= htmlspecialchars($asset['asset_id']) ?>"
                                        data-name="<?= htmlspecialchars($asset['asset_name']) ?>">
                                    <?= htmlspecialchars($asset['asset_id']) ?> - <?= htmlspecialchars($asset['asset_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entity Name</label>
                        <input type="text" class="form-control" name="entity" readonly required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Entity</button>
                </div>
            </div>
        </form>
    </div>
</div>
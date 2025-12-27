<?php
// This file is included inside a loop – $g is available
if (!isset($g)) {
    die('Error: Group data not available.');
}

$org_id_safe = isset($_SESSION['org_id']) 
    ? htmlspecialchars($_SESSION['org_id'], ENT_QUOTES, 'UTF-8') 
    : '';
?>

<!-- Unique Modal ID per Group Code -->
<div class="modal fade" id="addEntityModal_<?= $g['group_code'] ?>" tabindex="-1" aria-labelledby="addEntityModalLabel_<?= $g['group_code'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form action="/handler/addEntity_handler.php" method="POST">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="addEntityModalLabel_<?= $g['group_code'] ?>">
            Add Entity to Group <?= htmlspecialchars($g['group_code']) ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Hidden Fields (already filled) -->
          <input type="hidden" name="group_code" value="<?= htmlspecialchars($g['group_code']) ?>">
          <input type="hidden" name="location_code" value="<?= htmlspecialchars($g['location_code']) ?>">
          <input type="hidden" name="org_id" value="<?= $org_id_safe ?>">

          <!-- Display Group Info -->
          <div class="mb-3">
            <label class="form-label">Group</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($g['group_name']) ?>" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Group Code</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($g['group_code']) ?>" readonly>
          </div>

          <!-- Entity Name -->
          <div class="mb-3">
            <label for="entity_<?= $g['group_code'] ?>" class="form-label">Entity Name</label>
            <input type="text" class="form-control" id="entity_<?= $g['group_code'] ?>" name="entity" required>
          </div>

          <!-- Asset ID -->
          <div class="mb-3">
            <label for="asset_id_<?= $g['group_code'] ?>" class="form-label">Asset ID</label>
            <input type="text" class="form-control" id="asset_id_<?= $g['group_code'] ?>" name="asset_id" required>
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


<!-- Modal: public/forms/createGroup.php -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createGroupModalLabel">Create New Group </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/handler/createGroup_handler.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label for="locationName" class="form-label">Location Name</label>
            <input type="text" class="form-control" id="locationName" name="location_name" required>
          </div>
          <div class="mb-3">
            <label for="groupName" class="form-label">Group Name</label>
            <input type="text" class="form-control" id="groupName" name="group_name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>


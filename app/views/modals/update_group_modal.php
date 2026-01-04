<div class="modal fade" id="updateGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/mes/update-group" method="POST">
                <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::getCsrfToken()) ?>">
                <input type="hidden" id="update_group_id" name="group_id" value="">
                <input type="hidden" id="update_page_id" name="page_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Update Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="update_group_name" name="group_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" class="form-control" id="update_location_name" name="location_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sequence Order</label>
                        <input type="number" class="form-control" id="update_seq_id" name="seq_id" min="1" required>
                        <small class="form-text text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/mes/delete-group" method="POST">
                <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::getCsrfToken()) ?>">
                <input type="hidden" id="delete_group_id" name="group_id" value="">
                <input type="hidden" id="delete_page_id" name="page_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete group "<span id="delete_group_name"></span>" and all its entities? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Group</button>
                </div>
            </form>
        </div>
    </div>
</div>
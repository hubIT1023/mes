<div class="modal fade" id="dashboardPageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="dashboardPageForm" method="POST">
                <input type="hidden" name="org_id" value="<?= htmlspecialchars($tenant_id) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::getCsrfToken()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select class="form-select" id="pageAction" required>
                            <option value="create">Create New Page</option>
                            <option value="rename" <?= $hasAnyPage ? '' : 'disabled' ?>>Rename Page</option>
                            <option value="delete" <?= $hasAnyPage ? '' : 'disabled' ?>>Delete Page</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="pageSelectorField">
                        <label class="form-label">Select Page</label>
                        <select class="form-select" id="pageSelector" name="page_id">
                            <?php foreach ($pages as $p): ?>
                                <option value="<?= (int)$p['page_id'] ?>" <?= (int)$p['page_id'] == ($selectedPageId ?? 0) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['page_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="pageNameField">
                        <label class="form-label">Page Name</label>
                        <input type="text" class="form-control" id="pageNameInput" name="page_name" maxlength="100">
                    </div>

                    <div class="alert alert-warning d-none" id="deleteWarning">
                        <strong>Warning:</strong> This will permanently delete the page and all its groups and entities. Cannot be undone.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Create Page</button>
                </div>
            </form>
        </div>
    </div>
</div>
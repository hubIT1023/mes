	<div class="modal fade" id="editPositionModal_<?= (int)$entity['id'] ?>" tabindex="-1">
						<div class="modal-dialog">
							<form action="/mes/update-entity-position" method="POST">
								<input type="hidden" name="entity_id" value="<?= (int)$entity['id'] ?>">
								<input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title">Edit Position: <?= htmlspecialchars($entityName) ?></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
									</div>
									<div class="modal-body">
										<div class="row">
											<div class="col-6">
												<label class="form-label">Row</label>
												<input type="number" class="form-control" name="row_pos"
													   value="<?= (int)$entity['row_pos'] ?>" min="1" required>
											</div>
											<div class="col-6">
												<label class="form-label">Column</label>
												<input type="number" class="form-control" name="col_pos"
													   value="<?= (int)$entity['col_pos'] ?>" min="1" max="9" required>
											</div>
										</div>
										<div class="mt-3">
											<small class="text-muted">Columns 1–5 per row</small>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-primary">Move</button>
									</div>
								</div>
							</form>
						</div>
					</div>
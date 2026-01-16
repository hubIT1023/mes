<?php 
// machine_logs.php

require __DIR__ . '/../layouts/html/header.php'; 

?>

<div class="container-fluid mt-4 px-4">

    <nav class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-journal-text"></i> 📘 Machine Event Log
        </h2>
        <div class="d-flex gap-2">
            <a href="?" class="btn btn-outline-secondary btn-sm">Reset Filters</a>
            <a href="/mes/mms_admin" class="btn btn-secondary btn-sm">Home</a>
        </div>
    </nav>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Asset ID</label>
                    <input class="form-control form-control-sm" name="asset_id" placeholder="e.g. smt-101"
                           value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>">
                </div>
                
                <!-- ✅ ENTITY DROPDOWN -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Entity Name</label>
                    <select class="form-select form-select-sm" name="entity">
                        <option value="">All Entities</option>
                        <?php if (!empty($entities)): ?>
                            <?php foreach ($entities as $entity): ?>
                                <option value="<?= htmlspecialchars($entity) ?>" 
                                        <?= ($_GET['entity'] ?? '') === $entity ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($entity) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold">From</label>
                    <input type="datetime-local" name="from" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold">To</label>
                    <input type="datetime-local" name="to" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="table-dark">
                <tr>
                    <th style="min-width: 160px;">Time</th>
                    <th>Asset</th>
                    <th>Entity</th>
                    <th>State/StopCause</th>
                    <th>Reason</th>
                    <th>Action Taken</th>
                    <th>Status</th>
                    <th>Done By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No events found matching your criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap small text-muted">
                                <?= date('M d, Y H:i:s', strtotime($log['event_time'] ?? '')) ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($log['asset_id'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['entity'] ?? '') ?></td>
                            <td>
                                <span class="fw-semibold">
                                    <?= htmlspecialchars($log['stopcause_start'] ?? '') ?>
                                </span>
                            </td>
                            <td class="text-wrap" style="max-width: 250px;">
                                <?= htmlspecialchars($log['reason'] ?? '') ?>
                            </td>
                            <td class="text-wrap" style="max-width: 250px;">
                                <?= htmlspecialchars($log['action'] ?? '') ?>
                            </td>
                            <td>
                                <?php 
                                    $status = strtolower($log['status'] ?? '');
                                    $badgeClass = match($status) {
                                        'completed', 'closed' => 'bg-success',
                                        'open', 'pending' => 'bg-warning text-dark',
                                        'in progress' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?> uppercase">
                                    <?= strtoupper($status ?: 'N/A') ?>
                                </span>
                            </td>
                            <td class="small">
                                <i class="bi bi-person text-muted"></i> 
                                <?= htmlspecialchars($log['reported_by'] ?? '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>
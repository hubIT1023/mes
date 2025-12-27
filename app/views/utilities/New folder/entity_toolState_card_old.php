<?php
// public/Assets/tool_state/entity_toolState_card.php

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure $group is passed from parent
if (!isset($group)) {
    echo "<div class='alert alert-danger'>Error: No group data provided.</div>";
    return; // Use return, not exit (to avoid breaking layout)
}

$orgId = $_SESSION['org_id'] ?? null;
if (!$orgId) {
    echo "<div class='alert alert-danger'>Organization ID missing</div>";
    return;
}

// Use global $pdo from dashboard
global $pdo;

if (!$pdo) {
    require_once __DIR__ . '/../../../../../src/Config/DB_con.php';
    $pdo = \App\Config\DB_con::connect();
    if (!$pdo) {
        echo "<div class='alert alert-danger'>Database connection failed</div>";
        return;
    }
}

// ?? DEBUG: Check what $group contains
// Remove in production
echo "<!-- Group: " . print_r($group, true) . " -->";

try {
    //  Fetch entities where group_code, location_code, and org_id match
    $stmt = $pdo->prepare("
        SELECT * FROM registered_tools
        WHERE group_code = :group_code
          AND location_code = :location_code
          AND org_id = :org_id
        ORDER BY entity ASC
    ");
    $stmt->execute([
        'group_code' => $group['group_code'],
        'location_code' => $group['location_code'],
        'org_id' => $orgId
    ]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
     

} catch (PDOException $e) {
    error_log("Error fetching entities: " . $e->getMessage());
    $entities = [];
}

// ?? DEBUG: Check if data was fetched
 if (defined('DEBUG') && DEBUG) {
     echo "<div class='alert alert-info'>Found " . count($entities) . " entities</div>";
}
?>

<!-- Tool State Cards Row -->
<div class="row mt-3">
    <?php if (empty($entities)): ?>
        <div class="col-12">
            <div class="alert alert-warning p-2">
                No entities found for this group.<br>
                GC: <?= (int)$group['group_code'] ?>, 
                LC: <?= (int)$group['location_code'] ?>, 
                Org: <?= htmlspecialchars($orgId) ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($entities as $entity): 
            // Map state (placeholder — later from tool_state table)
            $state = 'PROD'; // Later: fetch from tool_state table
/*
            $key ="SELECT state_key
            VALUES(css_class)
            FROM state_config";
*/
            switch ($state) {
                case "PROD":
                    $color = 'btn-success';
                    $label = 'PROD';
                    break;
                case "FAULT":
                    $color = 'btn-danger';
                    $label = 'MAINT-COR';
                    break;
                case "MAINT":
                    $color = 'btn-warning';
                    $label = 'MAINT-PRE';
                    break;
                case "PROCESS":
                    $color = 'btn-primary';
                    $label = 'PROCESS';
                    break;
                case "EDG/SDG":
                    $color = 'btn-info';
                    $label = 'EDG/SDG';
                    break;
                case "IS":
                    $color = 'btn-secondary';
                    $label = 'IS';
                    break;
                default:
                    $color = 'btn-secondary';
                    $label = 'UNKNOWN';
                    break;
            }

            ?>

            <!-- Entity Card -->

            <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3" data-id="<?= htmlspecialchars($entity['id']) ?>">
                <div class="card shadow-sm" style="width: 190px;">
                    <div class="card-header bg-light text-primary small open-modal_toolState"
                            data-bs-toggle="modal"
                            data-bs-target="#toolStateModal"
                            data-id="<?= htmlspecialchars($entity['asset_id']) ?>"
                            data-header="<?= htmlspecialchars($entity['entity']) ?>"
                            data-group-code="<?= (int)$entity['group_code'] ?>"
                            data-location-code="<?= (int)$entity['location_code'] ?>"
                            data-date="<?= date('Y-m-d H:i:s') ?>"

                    >
                        <strong><?= htmlspecialchars($entity['entity']) ?></strong>
                    </div>

                    <div class="card-body d-flex align-items-center justify-content-center <?= $color ?> w-100 open-modal_toolState"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#toolStateModal"
                            data-id="<?= htmlspecialchars($entity['asset_id']) ?>"
                            data-header="<?= htmlspecialchars($entity['entity']) ?>"
                            data-group-code="<?= (int)$entity['group_code'] ?>"
                            data-location-code="<?= (int)$entity['location_code'] ?>"
                            data-date="<?= date('Y-m-d H:i:s') ?>"
                        >
                            <?= $label ?>
                        

                    </div>
                   
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
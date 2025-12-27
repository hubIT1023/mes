<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Work Orders</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px;
                     box-shadow: 0 0 10px rgba(0,0,0,0.1); }

        h1 { color: #0056b3; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0056b3; }

        .filters { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .filter-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 10px; }
        .filter-group { flex: 1; min-width: 200px; }

        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }

        .btn { background: #0056b3; color: white; border: none; padding: 10px 15px; border-radius: 4px;
               cursor: pointer; font-size: 14px; font-weight: bold; }
        .btn:hover { background: #003d7a; }

        .btn-reset { background: #6c757d; }
        .btn-reset:hover { background: #545b62; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #0056b3; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e9ecef; }

        .no-records { text-align: center; padding: 20px; color: #6c757d; font-style: italic; }

        .pagination { margin-top: 20px; text-align: center; }
        .pagination a, .pagination span { display: inline-block; padding: 8px 12px; margin: 0 4px;
            border: 1px solid #ddd; text-decoration: none; color: #0056b3; }
        .pagination .current { background: #0056b3; color: white; border-color: #0056b3; }

        .view-details { background: #28a745; color: white; padding: 5px 10px; border-radius: 4px;
                        text-decoration: none; font-size: 12px; }
        .view-details:hover { background: #218838; }

        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Completed Work Orders</h1>

    <!-- Filter Form -->
    <form method="GET" action="/mes/completed_work_orders" class="filters">
        <div class="filter-row">
            <div class="filter-group">
                <label for="work_order_ref">Work Order Reference:</label>
                <input type="text" id="work_order_ref" name="work_order_ref"
                       value="<?= htmlspecialchars($_GET['work_order_ref'] ?? '') ?>"
                       placeholder="Enter work order reference">
            </div>

            <div class="filter-group">
                <label for="asset_id">Asset ID:</label>
                <input type="text" id="asset_id" name="asset_id"
                       value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>"
                       placeholder="Enter asset ID">
            </div>

            <div class="filter-group">
                <label for="date_from">Date From:</label>
                <input type="date" id="date_from" name="date_from"
                       value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
            </div>

            <div class="filter-group">
                <label for="date_to">Date To:</label>
                <input type="date" id="date_to" name="date_to"
                       value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-group">
                <button type="submit" class="btn">Apply Filters</button>
                <a href="/mes/completed_work_orders" class="btn btn-reset">Reset</a>
            </div>
        </div>
    </form>

    <!-- Results -->
    <?php if (!empty($completed_work_orders)): ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Work Order</th>
                    <th>Asset ID</th>
                    <th>Asset Name</th>
                    <th>Checklist ID</th>
                    <th>Technician</th>
                    <th>Date Completed</th>
                    <th>Archived At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($completed_work_orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['maintenance_checklist_id']) ?></td>
                    <td><?= htmlspecialchars($order['work_order_ref']) ?></td>
                    <td><?= htmlspecialchars($order['asset_id']) ?></td>
                    <td><?= htmlspecialchars($order['asset_name']) ?></td>
                    <td><?= htmlspecialchars($order['checklist_id']) ?></td>
                    <td><?= htmlspecialchars($order['technician_name'] ?? '') ?></td>
                    <td><?= $order['date_completed'] ? date('Y-m-d H:i', strtotime($order['date_completed'])) : 'N/A' ?></td>
                    <td><?= $order['archived_at'] ? date('Y-m-d H:i', strtotime($order['archived_at'])) : 'N/A' ?></td>
                    <td>

					<a href="/mes/completed_work_order_details?id=<?= (int)$order['maintenance_checklist_id'] ?>">
						View Details
					</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1,$current_page-2); $i <= min($total_pages,$current_page+2); $i++): ?>
                    <?= $i == $current_page
                        ? "<span class='current'>{$i}</span>"
                        : "<a href='?".http_build_query(array_merge($_GET,['page'=>$i]))."'>$i</a>"
                    ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>

        <div class="no-records">No completed work orders found. Try adjusting your filters.</div>

    <?php endif; ?>

</div>

</body>
</html>
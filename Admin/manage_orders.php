<?php
$page_title = 'Manage Orders';
require_once 'partials/header.php';

$status_filter = $_GET['status'] ?? '';
$orders = get_all_orders($pdo, $status_filter);

$order_statuses = ['Pending','Processing','Documents Requested','Ready for Pickup','Completed','Cancelled'];
?>

<div class="filters">
    <a href="manage_orders.php" class="<?= empty($status_filter) ? 'active' : '' ?>">All Orders</a>
    <?php foreach ($order_statuses as $status): ?>
        <a href="manage_orders.php?status=<?= urlencode($status) ?>" class="<?= $status_filter == $status ? 'active' : '' ?>"><?= _e($status) ?></a>
    <?php endforeach; ?>
</div>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client</th>
                <th>Car</th>
                <th>Date Placed</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No orders found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= _e($order['id']) ?></td>
                        <td><?= _e($order['user_name']) ?></td>
                        <td><?= _e($order['brand'] . ' ' . $order['model']) ?></td>
                        <td><?= date("F j, Y", strtotime($order['created_at'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>"><?= _e($order['order_status']) ?></span></td>
                        <td class="actions">
                            <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.filters { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
.filters a { padding: 8px 15px; text-decoration: none; color: #333; background-color: #fff; border-radius: 20px; font-size: 0.9rem; border: 1px solid #ddd; }
.filters a.active { background-color: #2c5282; color: #fff; border-color: #2c5282; }
.status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: #fff; background-color: #6c757d; }
.status-pending { background-color: #ffc107; color: #333; }
.status-processing { background-color: #17a2b8; }
.status-documents-requested { background-color: #fd7e14; }
.status-ready-for-pickup { background-color: #28a745; }
.status-completed { background-color: #28a745; }
.status-cancelled { background-color: #dc3545; }
</style>

<?php require_once 'partials/footer.php'; ?>

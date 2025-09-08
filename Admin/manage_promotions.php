<?php
$page_title = 'Manage Promotions';
require_once 'partials/header.php';

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (is_admin_logged_in()) { // Only admins can delete
        delete_entity($pdo, 'promotions', (int)$_GET['id']);
        redirect('manage_promotions.php?status=deleted');
    }
}

$promotions = get_all_promotions($pdo);
?>

<a href="edit_promotion.php" class="btn" style="margin-bottom: 20px;">Add New Promotion</a>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Discount (%)</th>
                <th>Associated Car</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($promotions)): ?>
                <tr><td colspan="5" style="text-align:center;">No promotions found.</td></tr>
            <?php else: ?>
                <?php foreach ($promotions as $promo): ?>
                    <tr>
                        <td><?= _e($promo['title']) ?></td>
                        <td><?= _e($promo['discount_percent']) ?>%</td>
                        <td><?= $promo['car_id'] ? _e($promo['brand'] . ' ' . $promo['model']) : 'N/A' ?></td>
                        <td><?= date("F j, Y", strtotime($promo['end_date'])) ?></td>
                        <td class="actions">
                            <a href="edit_promotion.php?id=<?= $promo['id'] ?>" class="btn btn-sm">Edit</a>
                            <?php if (is_admin_logged_in()): ?>
                                <a href="manage_promotions.php?action=delete&id=<?= $promo['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this promotion?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'partials/footer.php'; ?>

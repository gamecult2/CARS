<?php
$page_title = 'Manage Cars';
require_once 'partials/header.php';

// --- Handle Delete Request ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $car_id_to_delete = (int)$_GET['id'];
    if ($car_id_to_delete > 0) {
        // First, get the image filenames to delete them from the server
        $stmt = $pdo->prepare("SELECT images FROM cars WHERE id = :id");
        $stmt->execute([':id' => $car_id_to_delete]);
        $car = $stmt->fetch();

        if ($car) {
            $images_to_delete = !empty($car['images']) ? explode(',', $car['images']) : [];
            foreach ($images_to_delete as $image) {
                $image_path = __DIR__ . '/../images/' . trim($image);
                if (file_exists($image_path)) {
                    @unlink($image_path); // Use @ to suppress errors if file not found
                }
            }
        }

        // Now, delete the car record from the database
        $delete_stmt = $pdo->prepare("DELETE FROM cars WHERE id = :id");
        $delete_stmt->execute([':id' => $car_id_to_delete]);

        // Redirect to the same page to see the updated list
        redirect('manage_cars.php?status=deleted');
    }
}

// Fetch all cars to display
$cars = get_cars($pdo);
?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div class="alert alert-success">Car has been successfully deleted.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
     <div class="alert alert-success">Car has been successfully updated.</div>
<?php endif; ?>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Brand & Model</th>
                <th>Year</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cars)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No cars found. <a href="add_car.php">Add one now</a>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td><?= _e($car['id']) ?></td>
                        <td>
                            <?php
                                $images = !empty($car['images']) ? explode(',', $car['images']) : [];
                                $first_image = !empty($images) ? '../images/' . trim($images[0]) : '../assets/placeholder.png';
                            ?>
                            <img src="<?= _e($first_image) ?>" alt="Car image" width="100">
                        </td>
                        <td><?= _e($car['brand'] . ' ' . $car['model']) ?></td>
                        <td><?= _e($car['year']) ?></td>
                        <td>$<?= number_format($car['price']) ?></td>
                        <td class="actions">
                            <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="manage_cars.php?action=delete&id=<?= $car['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this car? This action cannot be undone.');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Add some specific styles for alerts and small buttons
echo <<<HTML
<style>
.alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
.alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
.btn-sm { padding: 5px 10px; font-size: 0.8rem; }
.table-container { overflow-x: auto; }
</style>
HTML;

require_once 'partials/footer.php';
?>

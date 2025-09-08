<?php
$page_title = 'Edit Promotion';
require_once 'partials/header.php';

$promo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$promo = ['title' => '', 'description' => '', 'discount_percent' => '', 'car_id' => null, 'start_date' => '', 'end_date' => ''];

if ($promo_id) {
    $promo = get_promotion($pdo, $promo_id);
    if (!$promo) {
        redirect('manage_promotions.php');
    }
} else {
    $page_title = 'Add New Promotion';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $discount = filter_input(INPUT_POST, 'discount_percent', FILTER_VALIDATE_FLOAT);
    $car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT) ?: null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        // Handle error
    } else {
        if ($promo_id) {
            $stmt = $pdo->prepare("UPDATE promotions SET title = :title, description = :description, discount_percent = :discount, car_id = :car_id, start_date = :start, end_date = :end WHERE id = :id");
            $params = [':title' => $title, ':description' => $description, ':discount' => $discount, ':car_id' => $car_id, ':start' => $start_date, ':end' => $end_date, ':id' => $promo_id];
        } else {
            $stmt = $pdo->prepare("INSERT INTO promotions (title, description, discount_percent, car_id, start_date, end_date) VALUES (:title, :description, :discount, :car_id, :start, :end)");
            $params = [':title' => $title, ':description' => $description, ':discount' => $discount, ':car_id' => $car_id, ':start' => $start_date, ':end' => $end_date];
        }
        $stmt->execute($params);
        redirect('manage_promotions.php?status=success');
    }
}
$cars = get_cars($pdo);
?>

<form class="admin-form" action="edit_promotion.php<?= $promo_id ? '?id='.$promo_id : '' ?>" method="POST">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= _e($promo['title']) ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" required><?= _e($promo['description']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="discount_percent">Discount (%)</label>
        <input type="number" step="0.01" id="discount_percent" name="discount_percent" value="<?= _e($promo['discount_percent']) ?>">
    </div>
    <div class="form-group">
        <label for="car_id">Associate with Car (Optional)</label>
        <select name="car_id" id="car_id">
            <option value="">None</option>
            <?php foreach($cars as $car): ?>
                <option value="<?= $car['id'] ?>" <?= ($promo['car_id'] == $car['id']) ? 'selected' : '' ?>>
                    <?= _e($car['brand'] . ' ' . $car['model']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="start_date">Start Date</label>
        <input type="datetime-local" id="start_date" name="start_date" value="<?= !empty($promo['start_date']) ? date('Y-m-d\TH:i', strtotime($promo['start_date'])) : '' ?>" required>
    </div>
    <div class="form-group">
        <label for="end_date">End Date</label>
        <input type="datetime-local" id="end_date" name="end_date" value="<?= !empty($promo['end_date']) ? date('Y-m-d\TH:i', strtotime($promo['end_date'])) : '' ?>" required>
    </div>
    <button type="submit" class="btn"><?= $promo_id ? 'Update' : 'Create' ?> Promotion</button>
</form>

<?php require_once 'partials/footer.php'; ?>

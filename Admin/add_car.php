<?php
$page_title = 'Add New Car';
require_once 'partials/header.php';

$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Sanitize and Validate Inputs ---
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $mileage = filter_input(INPUT_POST, 'mileage', FILTER_VALIDATE_INT);
    $description = trim($_POST['description'] ?? '');
    $fuel_type = trim($_POST['fuel_type'] ?? '');
    $transmission = trim($_POST['transmission'] ?? '');
    $drivetrain = trim($_POST['drivetrain'] ?? '');
    $body_type = trim($_POST['body_type'] ?? '');
    $accessories = trim($_POST['accessories'] ?? '');
    $exterior_color = trim($_POST['exterior_color'] ?? '');
    $seats = filter_input(INPUT_POST, 'seats', FILTER_VALIDATE_INT);
    $dimensions = trim($_POST['dimensions'] ?? '');
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_INT);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($brand)) $errors[] = 'Brand is required.';
    if (empty($model)) $errors[] = 'Model is required.';
    if ($year === false || $year < 1900 || $year > date('Y') + 1) $errors[] = 'A valid year is required.';
    if ($price === false || $price <= 0) $errors[] = 'A valid price is required.';
    if ($mileage === false || $mileage < 0) $errors[] = 'A valid mileage is required.';
    if (empty($description)) $errors[] = 'Description is required.';

    // --- Image Upload Handling ---
    $image_filenames = [];
    if (isset($_FILES['images']) && !empty(array_filter($_FILES['images']['name']))) {
        $image_files = $_FILES['images'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        $upload_dir = __DIR__ . '/../images/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($image_files['name'] as $key => $name) {
            if ($image_files['error'][$key] === UPLOAD_ERR_OK) {
                // Check file type and size
                if (!in_array($image_files['type'][$key], $allowed_types)) {
                    $errors[] = "Invalid file type for {$name}. Only JPG, PNG, and GIF are allowed.";
                    continue;
                }
                if ($image_files['size'][$key] > $max_size) {
                    $errors[] = "File {$name} is too large. Maximum size is 5MB.";
                    continue;
                }

                // Generate a unique filename to prevent overwriting
                $filename = uniqid() . '-' . basename($name);
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($image_files['tmp_name'][$key], $destination)) {
                    $image_filenames[] = $filename;
                } else {
                    $errors[] = "Failed to upload {$name}.";
                }
            }
        }
    }

    // --- Insert into Database ---
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO cars (brand, model, year, price, mileage, fuel_type, transmission, drivetrain, body_type, exterior_color, seats, dimensions, weight, description, accessories, images, is_featured)
                    VALUES (:brand, :model, :year, :price, :mileage, :fuel_type, :transmission, :drivetrain, :body_type, :exterior_color, :seats, :dimensions, :weight, :description, :accessories, :images, :is_featured)";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':brand' => $brand,
                ':model' => $model,
                ':year' => $year,
                ':price' => $price,
                ':mileage' => $mileage,
                ':fuel_type' => $fuel_type,
                ':transmission' => $transmission,
                ':drivetrain' => $drivetrain,
                ':body_type' => $body_type,
                ':exterior_color' => $exterior_color,
                ':seats' => $seats,
                ':dimensions' => $dimensions,
                ':weight' => $weight,
                ':description' => $description,
                ':accessories' => $accessories,
                ':images' => implode(',', $image_filenames),
                ':is_featured' => $is_featured
            ]);

            $success_message = 'Car added successfully! <a href="manage_cars.php">View Cars</a>';
            $_POST = []; // Clear form
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= _e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-form" action="add_car.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="brand">Brand</label>
        <input type="text" id="brand" name="brand" value="<?= _e($_POST['brand'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="model">Model</label>
        <input type="text" id="model" name="model" value="<?= _e($_POST['model'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="year">Year</label>
        <input type="number" id="year" name="year" value="<?= _e($_POST['year'] ?? '') ?>" required oninput="updateCondition()">
    </div>
    <div class="form-group">
        <label>Condition</label>
        <input type="text" id="condition-display" value="New" readonly style="background: #eee;">
    </div>
    <div class="form-group">
        <label for="price">Price ($)</label>
        <input type="number" id="price" name="price" step="0.01" value="<?= _e($_POST['price'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="mileage">Mileage (km)</label>
        <input type="number" id="mileage" name="mileage" value="<?= _e($_POST['mileage'] ?? '') ?>" required>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="fuel_type">Fuel Type</label>
            <select id="fuel_type" name="fuel_type">
                <option value="Petrol">Petrol</option>
                <option value="Diesel">Diesel</option>
                <option value="Electric">Electric</option>
                <option value="Hybrid">Hybrid</option>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="transmission">Transmission</label>
            <select id="transmission" name="transmission">
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
                <option value="CVT">CVT</option>
            </select>
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="drivetrain">Drivetrain</label>
            <select id="drivetrain" name="drivetrain">
                <option value="FWD">FWD</option>
                <option value="RWD">RWD</option>
                <option value="AWD">AWD</option>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="body_type">Body Type</label>
            <input type="text" id="body_type" name="body_type" value="<?= _e($_POST['body_type'] ?? '') ?>" placeholder="e.g., Sedan, SUV, Coupe">
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="exterior_color">Exterior Color</label>
            <input type="text" id="exterior_color" name="exterior_color" value="<?= _e($_POST['exterior_color'] ?? '') ?>">
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="seats">Seats</label>
            <input type="number" id="seats" name="seats" value="<?= _e($_POST['seats'] ?? '') ?>">
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="dimensions">Dimensions (LxWxH mm)</label>
            <input type="text" id="dimensions" name="dimensions" value="<?= _e($_POST['dimensions'] ?? '') ?>" placeholder="e.g., 4885x1840x1445">
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="weight">Weight (kg)</label>
            <input type="number" id="weight" name="weight" value="<?= _e($_POST['weight'] ?? '') ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= _e($_POST['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="accessories">Accessories</label>
        <input type="text" id="accessories" name="accessories" value="<?= _e($_POST['accessories'] ?? '') ?>" placeholder="Comma-separated, e.g., ABS,Airbags">
    </div>
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
            Feature this car on the homepage
        </label>
    </div>
    <div class="form-group">
        <label for="images">Car Images</label>
        <input type="file" id="images" name="images[]" multiple accept="image/*">
        <small>You can select multiple images. Max size 5MB each.</small>
    </div>
    <button type="submit" class="btn">Add Car</button>
</form>

<?php
// Add some specific styles for alerts
echo <<<HTML
<style>
.alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
.alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
.alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
.alert-danger ul { margin: 0; padding-left: 20px; }
</style>
<script>
function updateCondition() {
    const yearInput = document.getElementById('year');
    const conditionDisplay = document.getElementById('condition-display');
    const currentYear = new Date().getFullYear();
    if (yearInput.value == currentYear) {
        conditionDisplay.value = 'New';
    } else {
        conditionDisplay.value = 'Used';
    }
}
// Run on page load in case of re-submission with values
document.addEventListener('DOMContentLoaded', updateCondition);
</script>
HTML;

require_once 'partials/footer.php';
?>

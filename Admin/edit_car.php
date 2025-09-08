<?php
$page_title = 'Edit Car';
require_once 'partials/header.php';

$errors = [];
$success_message = '';
$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$car_id) {
    redirect('manage_cars.php');
}

// Get distinct values for dropdowns
$car_attributes = get_distinct_car_attributes($pdo);

// Fetch the existing car data
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id");
$stmt->execute([':id' => $car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('manage_cars.php');
}

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
    // ... (Add all other validations as in add_car.php)

    // --- Image Handling ---
    $existing_images = !empty($car['images']) ? explode(',', $car['images']) : [];

    // Handle deletion of existing images
    $images_to_keep = $_POST['existing_images'] ?? [];
    $images_to_delete = array_diff($existing_images, $images_to_keep);

    foreach ($images_to_delete as $img) {
        $path = __DIR__ . '/../images/' . trim($img);
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    $image_filenames = $images_to_keep;

    // Handle new image uploads
    if (isset($_FILES['images']) && !empty(array_filter($_FILES['images']['name']))) {
        $image_files = $_FILES['images'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        $upload_dir = __DIR__ . '/../images/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $newly_uploaded_filenames = [];
        foreach ($image_files['name'] as $key => $name) {
            if ($image_files['error'][$key] === UPLOAD_ERR_OK) {
                if (!in_array($image_files['type'][$key], $allowed_types)) {
                    $errors[] = "Invalid file type for {$name}. Only JPG, PNG, and GIF are allowed.";
                    continue;
                }
                if ($image_files['size'][$key] > $max_size) {
                    $errors[] = "File {$name} is too large. Maximum size is 5MB.";
                    continue;
                }

                $filename = uniqid() . '-' . basename($name);
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($image_files['tmp_name'][$key], $destination)) {
                    $newly_uploaded_filenames[] = $filename;
                } else {
                    $errors[] = "Failed to upload {$name}.";
                }
            }
        }
        $image_filenames = array_merge($image_filenames, $newly_uploaded_filenames);
    }


    // --- Update Database ---
    if (empty($errors)) {
        try {
            $sql = "UPDATE cars SET
                        brand = :brand,
                        model = :model,
                        year = :year,
                        price = :price,
                        mileage = :mileage,
                        fuel_type = :fuel_type,
                        transmission = :transmission,
                        drivetrain = :drivetrain,
                        body_type = :body_type,
                        exterior_color = :exterior_color,
                        seats = :seats,
                        dimensions = :dimensions,
                        weight = :weight,
                        description = :description,
                        accessories = :accessories,
                        images = :images,
                        is_featured = :is_featured
                    WHERE id = :id";
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
                ':is_featured' => $is_featured,
                ':id' => $car_id
            ]);

            redirect('manage_cars.php?status=updated');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= _e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-form" action="edit_car.php?id=<?= $car_id ?>" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="brand">Brand</label>
        <input type="text" id="brand" name="brand" value="<?= _e($car['brand']) ?>" required>
    </div>
    <div class="form-group">
        <label for="model">Model</label>
        <input type="text" id="model" name="model" value="<?= _e($car['model']) ?>" required>
    </div>
    <!-- ... other form fields pre-filled with $car data ... -->
    <div class="form-group">
        <label for="year">Year</label>
        <input type="number" id="year" name="year" value="<?= _e($car['year']) ?>" required oninput="updateCondition()">
    </div>
    <div class="form-group">
        <label>Condition</label>
        <input type="text" id="condition-display" value="" readonly style="background: #eee;">
    </div>
    <div class="form-group">
        <label for="price">Price ($)</label>
        <input type="number" id="price" name="price" step="0.01" value="<?= _e($car['price']) ?>" required>
    </div>
    <div class="form-group">
        <label for="mileage">Mileage (km)</label>
        <input type="number" id="mileage" name="mileage" value="<?= _e($car['mileage']) ?>" required>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="fuel_type">Fuel Type</label>
            <select id="fuel_type" name="fuel_type" required>
                <option value="">Select Fuel Type</option>
                <?php foreach ($car_attributes['fuel_type'] as $value): ?>
                    <option value="<?= _e($value) ?>" <?= (($car['fuel_type'] ?? '') == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="transmission">Transmission</label>
            <select id="transmission" name="transmission" required>
                <option value="">Select Transmission</option>
                <?php foreach ($car_attributes['transmission'] as $value): ?>
                    <option value="<?= _e($value) ?>" <?= (($car['transmission'] ?? '') == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="drivetrain">Drivetrain</label>
            <select id="drivetrain" name="drivetrain">
                <option value="FWD" <?= ($car['drivetrain'] ?? '') == 'FWD' ? 'selected' : '' ?>>FWD</option>
                <option value="RWD" <?= ($car['drivetrain'] ?? '') == 'RWD' ? 'selected' : '' ?>>RWD</option>
                <option value="AWD" <?= ($car['drivetrain'] ?? '') == 'AWD' ? 'selected' : '' ?>>AWD</option>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="body_type">Body Type</label>
            <select id="body_type" name="body_type" required>
                <option value="">Select Body Type</option>
                <?php foreach ($car_attributes['body_type'] as $value): ?>
                    <option value="<?= _e($value) ?>" <?= (($car['body_type'] ?? '') == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="exterior_color">Exterior Color</label>
            <input type="text" id="exterior_color" name="exterior_color" value="<?= _e($car['exterior_color'] ?? '') ?>">
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="seats">Seats</label>
            <input type="number" id="seats" name="seats" value="<?= _e($car['seats'] ?? '') ?>">
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="form-group" style="flex: 1;">
            <label for="dimensions">Dimensions (LxWxH mm)</label>
            <input type="text" id="dimensions" name="dimensions" value="<?= _e($car['dimensions'] ?? '') ?>" placeholder="e.g., 4885x1840x1445">
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="weight">Weight (kg)</label>
            <input type="number" id="weight" name="weight" value="<?= _e($car['weight'] ?? '') ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= _e($car['description']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="accessories">Accessories</label>
        <input type="text" id="accessories" name="accessories" value="<?= _e($car['accessories'] ?? '') ?>" placeholder="Comma-separated, e.g., ABS,Airbags">
    </div>
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_featured" value="1" <?= !empty($car['is_featured']) ? 'checked' : '' ?>>
            Feature this car on the homepage
        </label>
    </div>

    <div class="form-group">
        <label>Current Images</label>
        <div class="current-images">
            <?php $images = !empty($car['images']) ? explode(',', $car['images']) : []; ?>
            <?php if (empty($images)): ?>
                <p>No images uploaded for this car.</p>
            <?php else: ?>
                <?php foreach ($images as $img): ?>
                    <div class="img-checkbox">
                        <img src="../images/<?= _e(trim($img)) ?>" width="100">
                        <label>
                            <input type="checkbox" name="existing_images[]" value="<?= _e(trim($img)) ?>" checked> Keep
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="images">Upload New Images</label>
        <input type="file" id="images" name="images[]" multiple accept="image/*">
        <small>Add new images here. Unchecking 'Keep' on current images will delete them upon saving.</small>
    </div>

    <button type="submit" class="btn">Update Car</button>
    <a href="manage_cars.php" class="btn btn-secondary">Cancel</a>
</form>
<style>
/* Basic styling for the image management section */
.current-images { display: flex; flex-wrap: wrap; gap: 15px; }
.img-checkbox { display: flex; flex-direction: column; align-items: center; }
.img-checkbox img { margin-bottom: 5px; border: 1px solid #ccc; border-radius: 4px; }
.alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
.alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
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
// Run on page load to set initial state
document.addEventListener('DOMContentLoaded', updateCondition);

const editCarForm = document.querySelector('form.admin-form');
editCarForm.addEventListener('submit', function(event) {
    const errors = [];
    const fields = {
        brand: { required: true, element: document.getElementById('brand') },
        model: { required: true, element: document.getElementById('model') },
        year: { required: true, element: document.getElementById('year'), isNumeric: true, min: 1900, max: new Date().getFullYear() + 1 },
        price: { required: true, element: document.getElementById('price'), isNumeric: true, min: 1 },
        mileage: { required: true, element: document.getElementById('mileage'), isNumeric: true, min: 0 },
        description: { required: true, element: document.getElementById('description') },
        fuel_type: { required: true, element: document.getElementById('fuel_type') },
        transmission: { required: true, element: document.getElementById('transmission') },
        body_type: { required: true, element: document.getElementById('body_type') },
    };

    for (const fieldName in fields) {
        const field = fields[fieldName];
        const value = field.element.value.trim();

        if (field.required && value === '') {
            errors.push(`${fieldName.replace('_', ' ')} is required.`);
            continue;
        }

        if (field.isNumeric && value !== '') {
            const numValue = parseFloat(value);
            if (isNaN(numValue)) {
                errors.push(`${fieldName.replace('_', ' ')} must be a number.`);
            }
            if (field.min !== undefined && numValue < field.min) {
                errors.push(`${fieldName.replace('_', ' ')} must be at least ${field.min}.`);
            }
            if (field.max !== undefined && numValue > field.max) {
                errors.push(`${fieldName.replace('_', ' ')} must be no more than ${field.max}.`);
            }
        }
    }

    if (errors.length > 0) {
        event.preventDefault();
        alert('Please fix the following errors:\n\n- ' + errors.join('\n- '));
    }
});
</script>

<?php require_once 'partials/footer.php'; ?>

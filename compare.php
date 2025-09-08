<?php
require_once 'functions.php';

$car_ids_str = $_GET['ids'] ?? '';

$car_ids = [];
if (!empty($car_ids_str)) {
    // Explode the string by comma and convert each part to an integer
    $car_ids = array_map('intval', explode(',', $car_ids_str));
    // Filter out any IDs that became 0 (e.g., from non-numeric input)
    $car_ids = array_filter($car_ids, fn($id) => $id > 0);
}

$cars_to_compare = [];
if (!empty($car_ids)) {
    foreach ($car_ids as $id) {
        $car = get_car_by_id($pdo, $id);
        if ($car) {
            $cars_to_compare[] = $car;
        }
    }
}

// Define the attributes to display in the comparison table
$attributes_to_compare = [
    'brand' => 'Brand',
    'model' => 'Model',
    'year' => 'Year',
    'price' => 'Price',
    'body_type' => 'Body Type',
    'mileage' => 'Mileage',
    'fuel_type' => 'Fuel Type',
    'transmission' => 'Transmission',
    'drivetrain' => 'Drivetrain',
    'exterior_color' => 'Exterior Color',
    'seats' => 'Seats',
    'dimensions' => 'Dimensions (L x W x H)',
    'weight' => 'Weight (kg)',
    'accessories' => 'Accessories',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Cars - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="compare.css">
    <style>
        /* Specific styles for the comparison page */
        .compare-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .compare-table th, .compare-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .compare-table th {
            background-color: #f8f9fa;
        }
        .compare-table .header-row th {
            text-align: center;
        }
        .compare-table .attribute-label {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .compare-table img {
            max-width: 200px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <h1>Compare Cars</h1>

        <?php if (empty($cars_to_compare)): ?>
            <p>You haven't selected any cars to compare. Please go back to the listings and select at least two cars.</p>
            <a href="index.php" class="btn">Back to Listings</a>
        <?php else: ?>
            <table class="compare-table">
                <thead>
                    <tr class="header-row">
                        <th>Feature</th>
                        <?php foreach ($cars_to_compare as $car): ?>
                            <th>
                                <a href="car_details.php?id=<?= $car['id'] ?>">
                                    <?php
                                        $images = !empty($car['images']) ? explode(',', $car['images']) : [];
                                        $first_image = !empty($images) ? 'images/' . trim($images[0]) : 'assets/placeholder.png';
                                    ?>
                                    <img src="<?= _e($first_image) ?>" alt="<?= _e($car['brand'] . ' ' . $car['model']) ?>">
                                    <?= _e($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?>
                                </a>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attributes_to_compare as $key => $label): ?>
                        <tr>
                            <td class="attribute-label"><?= _e($label) ?></td>
                            <?php foreach ($cars_to_compare as $car): ?>
                                <td>
                                    <?php
                                    $value = $car[$key] ?? 'N/A';
                                    if ($key === 'price') {
                                        echo '$' . number_format((float)$value);
                                    } elseif ($key === 'mileage') {
                                        echo number_format((int)$value) . ' km';
                                    } else {
                                        echo _e($value);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>

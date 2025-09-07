<?php
require_once 'functions.php';

// Fetch cars from the database
// We'll get the filter values from the $_GET superglobal
$filters = [
    'brand' => $_GET['brand'] ?? null,
    'model' => $_GET['model'] ?? null,
    'year' => $_GET['year'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
];

// The get_cars function is defined in functions.php
// It sanitizes the input and returns the cars
$cars = get_cars($pdo, array_filter($filters));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Dealership</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Car Dealership</a></h1>
            <nav>
                <ul>
                    <?php if (is_user_logged_in()): ?>
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li><a href="/Admin/index.php">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="search-filter">
            <h2>Find Your Perfect Car</h2>
            <form action="index.php" method="GET">
                <div class="form-group">
                    <input type="text" name="brand" placeholder="Brand (e.g., Toyota)" value="<?= _e($filters['brand']) ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="model" placeholder="Model (e.g., Camry)" value="<?= _e($filters['model']) ?>">
                </div>
                <div class="form-group">
                    <input type="number" name="year" placeholder="Year (e.g., 2022)" value="<?= _e($filters['year']) ?>">
                </div>
                <div class="form-group">
                    <input type="number" name="max_price" placeholder="Max Price" value="<?= _e($filters['max_price']) ?>">
                </div>
                <button type="submit" class="btn">Search</button>
                <a href="index.php" class="btn-secondary">Reset</a>
            </form>
        </section>

        <section class="car-listings">
            <h2>Available Cars</h2>
            <div class="car-grid">
                <?php if (empty($cars)): ?>
                    <p>No cars found matching your criteria. Try adjusting your search.</p>
                <?php else: ?>
                    <?php foreach ($cars as $car): ?>
                        <div class="car-card">
                            <a href="car_details.php?id=<?= $car['id'] ?>">
                                <?php
                                    // Display the first image, or a placeholder if none exist
                                    $images = !empty($car['images']) ? explode(',', $car['images']) : [];
                                    $first_image = !empty($images) ? 'images/' . trim($images[0]) : 'assets/placeholder.png';
                                ?>
                                <img src="<?= _e($first_image) ?>" alt="<?= _e($car['brand'] . ' ' . $car['model']) ?>">
                                <div class="car-card-content">
                                    <h3><?= _e($car['brand'] . ' ' . $car['model']) ?></h3>
                                    <p class="price">$<?= number_format($car['price']) ?></p>
                                    <ul>
                                        <li><strong>Year:</strong> <?= _e($car['year']) ?></li>
                                        <li><strong>Mileage:</strong> <?= number_format($car['mileage']) ?> km</li>
                                    </ul>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
require_once 'functions.php';

// --- Get Car ID and Fetch Car Data ---
$car_id = (int)($_GET['id'] ?? 0);
if ($car_id <= 0) {
    // Or redirect to a 404 page
    die('Invalid car ID.');
}

$car = get_car_by_id($pdo, $car_id);

if (!$car) {
    // Or redirect to a 404 page
    die('Car not found.');
}

$errors = [];
$success_message = '';

// --- Handle Inquiry Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_user_logged_in()) {
    $message = trim($_POST['message'] ?? '');

    if (empty($message)) {
        $errors[] = 'Message cannot be empty.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, car_id, message) VALUES (:user_id, :car_id, :message)");
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':car_id' => $car_id,
                ':message' => $message,
            ]);
            $success_message = "Your inquiry has been sent successfully!";
        } catch (PDOException $e) {
            $errors[] = "An error occurred. Please try again.";
            // Log error: error_log($e->getMessage());
        }
    }
}

// Explode images string into an array
$images = !empty($car['images']) ? explode(',', $car['images']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="car_details.css"> <!-- Specific styles for this page -->
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
        <div class="car-details-layout">
            <!-- Car Images Section -->
            <div class="car-gallery">
                <?php if (!empty($images)): ?>
                    <img src="images/<?= _e(trim($images[0])) ?>" alt="Main car image" class="main-image">
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnail-images">
                            <?php foreach ($images as $img): ?>
                                <img src="images/<?= _e(trim($img)) ?>" alt="Car thumbnail">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="assets/placeholder.png" alt="No image available" class="main-image">
                <?php endif; ?>
            </div>

            <!-- Car Information Section -->
            <div class="car-info">
                <h1><?= _e($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?></h1>
                <p class="price">$<?= number_format($car['price']) ?></p>
                <h3>Key Details</h3>
                <ul>
                    <li><strong>Brand:</strong> <?= _e($car['brand']) ?></li>
                    <li><strong>Model:</strong> <?= _e($car['model']) ?></li>
                    <li><strong>Year:</strong> <?= _e($car['year']) ?></li>
                    <li><strong>Mileage:</strong> <?= number_format($car['mileage']) ?> km</li>
                </ul>
                <h3>Description</h3>
                <p><?= nl2br(_e($car['description'])) ?></p>
            </div>

            <!-- Inquiry Form Section -->
            <div class="car-inquiry">
                <h3>Interested? Send a Message</h3>
                <?php if (is_user_logged_in()): ?>
                    <?php if ($success_message): ?>
                        <div class="form-success"><?= _e($success_message) ?></div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="form-errors">
                                <?php foreach ($errors as $error): ?><p><?= _e($error) ?></p><?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form action="car_details.php?id=<?= $car_id ?>" method="POST">
                            <div class="form-group">
                                <label for="message">Your Message</label>
                                <textarea id="message" name="message" rows="6" required></textarea>
                            </div>
                            <button type="submit" class="btn">Send Inquiry</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Please <a href="login.php?redirect=car_details.php?id=<?= $car_id ?>">log in</a> or <a href="register.php">register</a> to send a message.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

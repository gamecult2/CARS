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
        // Get or create a conversation for this car and user
        $conversation_id = get_or_create_conversation_for_car($pdo, $_SESSION['user_id'], $car_id);

        // Send the message
        send_chat_message($pdo, $conversation_id, $_SESSION['user_id'], 'user', $message);

        // Redirect to the chat view
        redirect("chat_view.php?id=$conversation_id");
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
        <div class="breadcrumb">
            <a href="index.php">Home</a> &gt; <a href="index.php?brand=<?= _e($car['brand']) ?>"><?= _e($car['brand']) ?></a> &gt; <?= _e($car['model']) ?>
        </div>

        <h1 class="car-title"><?= _e($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?></h1>

        <div class="details-layout">
            <div class="details-main">
                <!-- Image Gallery -->
                <div class="gallery">
                    <div class="main-image">
                        <img src="images/<?= _e(!empty($images) ? trim($images[0]) : 'placeholder.png') ?>" alt="Main car image" id="main-car-image">
                    </div>
                    <div class="thumbnails">
                        <?php foreach ($images as $img): ?>
                            <img src="images/<?= _e(trim($img)) ?>" alt="Car thumbnail" class="thumbnail-item" onclick="changeImage('images/<?= _e(trim($img)) ?>')">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Description -->
                <div class="description-section card">
                    <h3>Description</h3>
                    <p><?= nl2br(_e($car['description'])) ?></p>
                </div>

                <!-- Vehicle Details -->
                <div class="specs-section card">
                    <h3>Vehicle Details</h3>
                    <ul class="specs-list">
                        <li><span>Brand</span><strong><?= _e($car['brand']) ?></strong></li>
                        <li><span>Model</span><strong><?= _e($car['model']) ?></strong></li>
                        <li><span>Year</span><strong><?= _e($car['year']) ?></strong></li>
                        <li><span>Body Type</span><strong><?= _e($car['body_type']) ?></strong></li>
                        <li><span>Mileage</span><strong><?= number_format($car['mileage']) ?> km</strong></li>
                        <li><span>Fuel Type</span><strong><?= _e($car['fuel_type']) ?></strong></li>
                        <li><span>Transmission</span><strong><?= _e($car['transmission']) ?></strong></li>
                        <li><span>Drivetrain</span><strong><?= _e($car['drivetrain']) ?></strong></li>
                    </ul>
                </div>

                <!-- Accessories Section -->
                <?php if (!empty($car['accessories'])): ?>
                <div class="accessories-section card">
                    <h3>Accessories</h3>
                    <ul class="accessories-list">
                        <?php
                            $accessories = explode(',', $car['accessories']);
                            foreach ($accessories as $acc) {
                                echo '<li>' . _e(trim($acc)) . '</li>';
                            }
                        ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <aside class="details-sidebar">
                <div class="price-card card">
                    <h4>Price Breakdown</h4>
                    <ul class="price-breakdown">
                        <li><span>Vehicle Price:</span><strong>$<?= number_format($car['price']) ?></strong></li>
                        <li><span>Inspection Fee:</span><span>$65</span></li>
                        <li><span>Export Handling Fee:</span><span>$400</span></li>
                        <li><span>Service Fee:</span><span>$300</span></li>
                        <li><span>Banking Transfer Fee:</span><span>$50</span></li>
                    </ul>
                    <div class="total-price">
                        <span>EXW Total:</span>
                        <strong>$<?= number_format($car['price'] + 65 + 400 + 300 + 50) ?></strong>
                    </div>
                    <small class="price-info">This is an estimated price. Does not include shipping (FOB, CFR, CIF). Please contact us for a full quote.</small>
                </div>

                <div class="inquiry-card card">
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
                                    <textarea id="message" name="message" rows="5" required placeholder="I'm interested in this car..."></textarea>
                                </div>
                                <button type="submit" class="btn">Send Inquiry</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Please <a href="login.php?redirect=car_details.php?id=<?= $car_id ?>">log in</a> or <a href="register.php">register</a> to send a message.</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>
    <script>
        function changeImage(newSrc) {
            document.getElementById('main-car-image').src = newSrc;
        }
    </script>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

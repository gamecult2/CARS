<?php
require_once 'functions.php';

// Protect this page
if (!is_user_logged_in()) {
    redirect('login.php');
}

$page_title = 'My Wishlist';
$session_user = get_session_user();
$user_id = $session_user['id'];

$wishlist_cars = get_wishlist_for_user($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <h1><?= _e($page_title) ?></h1>
        <p>Cars you have saved for later.</p>

        <div class="car-grid">
            <?php if (empty($wishlist_cars)): ?>
                <p>Your wishlist is empty. Browse our cars to find something you like!</p>
            <?php else: ?>
                <?php foreach ($wishlist_cars as $car): ?>
                    <?php include 'partials/car_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>

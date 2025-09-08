<?php
require_once 'functions.php';
$page_title = 'Special Offers & Promotions';
// Fetch only active promotions for the public page
$promotions = get_all_promotions($pdo, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="promotions.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1>Special Offers</h1>
            <p>Check out our latest deals and promotions.</p>
        </div>

        <div class="promotions-layout">
            <?php if (empty($promotions)): ?>
                <p>There are no active promotions at the moment. Please check back later!</p>
            <?php else: ?>
                <?php foreach ($promotions as $promo): ?>
                    <div class="promo-card">
                        <h3><?= _e($promo['title']) ?></h3>
                        <?php if ($promo['discount_percent'] > 0): ?>
                            <div class="discount-badge"><?= _e($promo['discount_percent']) ?>% OFF</div>
                        <?php endif; ?>
                        <p class="promo-description"><?= _e($promo['description']) ?></p>
                        <div class="promo-meta">
                            <span>Valid until: <?= date("F j, Y", strtotime($promo['end_date'])) ?></span>
                        </div>
                        <?php if ($promo['car_id']): ?>
                            <a href="car_details.php?id=<?= $promo['car_id'] ?>" class="btn">View Associated Car</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>

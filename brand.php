<?php
require_once 'functions.php';

$page_title = 'All Car Brands';
$brands = get_brands_with_count($pdo);
$total_cars = array_sum(array_column($brands, 'car_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="brand.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a> &gt; <span>All Brands</span>
        </div>
        <h1 class="page-title">Used Cars For Sale By Brand</h1>
        <h3 class="page-subtitle">We provide you with the largest selection of used cars from our dealership with <strong><?= number_format($total_cars) ?></strong> cars available.</h3>

        <div class="brand-grid">
            <?php foreach ($brands as $brand): ?>
                <a href="index.php?brand=<?= urlencode($brand['brand']) ?>" class="brand-card">
                    <div class="brand-logo-placeholder"><?= _e(strtoupper(substr($brand['brand'], 0, 1))) ?></div>
                    <p class="name"><?= _e($brand['brand']) ?></p>
                    <p class="num">(<?= number_format($brand['car_count']) ?>)</p>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>

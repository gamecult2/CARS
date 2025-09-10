<?php
require_once 'functions.php';

$featured_brands = get_featured_brands($pdo);
$all_brands_grouped = get_all_brands_with_counts($pdo);
$alphabet = array_keys($all_brands_grouped);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Brands - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="brands.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <div class="brands-page">

            <section class="featured-brands">
                <h2>Featured Brands</h2>
                <div class="brand-grid featured-grid">
                    <?php foreach ($featured_brands as $brand): ?>
                        <a href="index.php?brand=<?= _e($brand['name']) ?>" class="brand-item">
                            <img src="<?= _e($brand['logo_url']) ?>" alt="<?= _e($brand['name']) ?> Logo" class="brand-logo" onerror="this.style.display='none'">
                            <span class="brand-name"><?= _e($brand['name']) ?></span>
                            <span class="brand-count">(<?= $brand['car_count'] ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="extra-links">
                 <a href="#" class="extra-link-item">New Car For Export</a>
                 <a href="#" class="extra-link-item">Original Paint Cars</a>
                 <a href="#" class="extra-link-item">Car Specs</a>
                 <a href="#" class="extra-link-item">Market Intel</a>
            </section>

            <nav class="alphabet-index">
                <h2>All Brands</h2>
                <ul>
                    <?php foreach ($alphabet as $letter): ?>
                        <li><a href="#brand-<?= $letter ?>"><?= $letter ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <section class="all-brands-list">
                <?php foreach ($all_brands_grouped as $letter => $brands_in_group): ?>
                    <div id="brand-<?= $letter ?>" class="brand-group">
                        <h3 class="letter-heading"><?= $letter ?></h3>
                        <div class="brand-grid">
                            <?php foreach ($brands_in_group as $brand): ?>
                                <a href="index.php?brand=<?= _e($brand['name']) ?>" class="brand-item">
                                    <img src="<?= _e($brand['logo_url']) ?>" alt="<?= _e($brand['name']) ?> Logo" class="brand-logo" onerror="this.style.display='none'">
                                    <div class="brand-info">
                                        <span class="brand-name"><?= _e($brand['name']) ?></span>
                                        <span class="brand-count">(<?= $brand['car_count'] ?>)</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

        </div>
    </main>

    <?php require 'partials/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.alphabet-index a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>

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
    <?php
    require 'partials/header.php';

    // Fetch data for homepage sections
    $featured_cars = $pdo->query("SELECT * FROM cars WHERE is_featured = 1 LIMIT 3")->fetchAll();
    $recent_promos = array_slice(get_all_promotions($pdo, true), 0, 3);
    $recent_posts = array_slice(get_all_blog_posts($pdo), 0, 3);
    ?>

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

        <!-- Only show dashboard sections if not filtering -->
        <?php if (empty(array_filter($filters))): ?>
            <section class="featured-cars">
                <h2>Featured Cars</h2>
                <div class="car-grid">
                    <?php foreach($featured_cars as $car): ?>
                        <?php include 'partials/car_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="home-columns">
                <section class="recent-promotions">
                    <h3>Latest Promotions</h3>
                    <?php foreach($recent_promos as $promo): ?>
                        <div class="promo-item-home">
                            <a href="promotions.php"><strong><?= _e($promo['title']) ?></strong></a>
                            <p><?= _e(substr($promo['description'], 0, 100)) ?>...</p>
                        </div>
                    <?php endforeach; ?>
                </section>
                <section class="recent-posts">
                    <h3>From Our Blog</h3>
                    <?php foreach($recent_posts as $post): ?>
                        <div class="post-item-home">
                            <a href="post.php?id=<?= $post['id'] ?>"><strong><?= _e($post['title']) ?></strong></a>
                            <p><?= _e(substr(strip_tags($post['content']), 0, 100)) ?>...</p>
                        </div>
                    <?php endforeach; ?>
                </section>
            </div>
        <?php endif; ?>

        <section class="car-listings">
            <h2><?= empty(array_filter($filters)) ? "All Cars" : "Search Results" ?></h2>
            <div class="car-grid">
                <?php if (empty($cars)): ?>
                    <p>No cars found matching your criteria. Try adjusting your search.</p>
                <?php else: ?>
                    <?php foreach ($cars as $car): ?>
                        <?php include 'partials/car_card.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>

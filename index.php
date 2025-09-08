<?php
require_once 'functions.php';

// Fetch cars from the database
// We'll get the filter values from the $_GET superglobal
$filters = [
    'brand' => $_GET['brand'] ?? null,
    'model' => $_GET['model'] ?? null,
    'year' => $_GET['year'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
    'body_type' => $_GET['body_type'] ?? null,
    'transmission' => $_GET['transmission'] ?? null,
    'fuel_type' => $_GET['fuel_type'] ?? null,
];

// The get_cars function is defined in functions.php
// It sanitizes the input and returns the cars
$filters['condition'] = $_GET['condition'] ?? null;
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
    $featured_cars_query = "
        SELECT c.*, COALESCE(r.avg_rating, 0) as avg_rating, COALESCE(r.review_count, 0) as review_count
        FROM cars c
        LEFT JOIN (
            SELECT car_id, AVG(rating) as avg_rating, COUNT(*) as review_count
            FROM reviews
            GROUP BY car_id
        ) r ON c.id = r.car_id
        WHERE c.is_featured = 1
        LIMIT 3
    ";
    $featured_cars = $pdo->query($featured_cars_query)->fetchAll();
    $recent_promos = array_slice(get_all_promotions($pdo, true), 0, 3);
    $recent_posts = array_slice(get_all_blog_posts($pdo), 0, 3);

    // Fetch distinct attributes for filter dropdowns
    $car_attributes = get_distinct_car_attributes($pdo);
    ?>

    <main class="container">
        <section class="search-filter">
            <h2>Find Your Perfect Car</h2>
            <form action="index.php" method="GET" id="search-form">
                <div class="form-row">
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
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <select name="body_type">
                            <option value="">Any Body Type</option>
                            <?php foreach ($car_attributes['body_type'] as $value): ?>
                                <option value="<?= _e($value) ?>" <?= ($filters['body_type'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="transmission">
                            <option value="">Any Transmission</option>
                            <?php foreach ($car_attributes['transmission'] as $value): ?>
                                <option value="<?= _e($value) ?>" <?= ($filters['transmission'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="fuel_type">
                            <option value="">Any Fuel Type</option>
                            <?php foreach ($car_attributes['fuel_type'] as $value): ?>
                                <option value="<?= _e($value) ?>" <?= ($filters['fuel_type'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row form-actions">
                    <button type="submit" class="btn">Search</button>
                    <a href="index.php" class="btn-secondary">Reset</a>
                </div>
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

        <div class="category-filters">
            <a href="index.php" class="<?= empty($filters['condition']) ? 'active' : '' ?>">All Cars</a>
            <a href="index.php?condition=new" class="<?= ($filters['condition'] ?? '') === 'new' ? 'active' : '' ?>">New Cars</a>
            <a href="index.php?condition=used" class="<?= ($filters['condition'] ?? '') === 'used' ? 'active' : '' ?>">Used Cars</a>
        </div>

        <section class="car-listings">
            <h2 id="car-listings-title"><?= empty(array_filter($filters)) ? "All Cars" : "Search Results" ?></h2>
            <div class="car-grid" id="car-grid">
                <?php if (empty($cars)): ?>
                    <p id="no-cars-message">No cars found matching your criteria. Try adjusting your search.</p>
                <?php else: ?>
                    <?php foreach ($cars as $car): ?>
                        <?php include 'partials/car_card.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require 'partials/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('search-form');
        const carGrid = document.getElementById('car-grid');
        const listingsTitle = document.getElementById('car-listings-title');

        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            performSearch();
        });

        // Also perform search on change of select dropdowns for immediate feedback
        searchForm.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', performSearch);
        });

        async function performSearch() {
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);
            const url = `api_search.php?${params.toString()}`;

            // Update URL in browser
            history.pushState(null, '', `index.php?${params.toString()}`);

            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const cars = await response.json();
                updateCarGrid(cars);
            } catch (error) {
                console.error('Error fetching search results:', error);
                carGrid.innerHTML = '<p class="error-message">Failed to load search results. Please try again.</p>';
            }
        }

        function updateCarGrid(cars) {
            carGrid.innerHTML = ''; // Clear existing results
            listingsTitle.textContent = 'Search Results';

            if (cars.length === 0) {
                carGrid.innerHTML = '<p id="no-cars-message">No cars found matching your criteria. Try adjusting your search.</p>';
                return;
            }

            cars.forEach(car => {
                // This function replicates the structure of partials/car_card.php
                const carCard = createCarCard(car);
                carGrid.appendChild(carCard);
            });
        }

        function createCarCard(car) {
            const carCard = document.createElement('div');
            carCard.className = 'car-card';

            const firstImage = car.images && car.images.split(',').length > 0
                ? `images/${car.images.split(',')[0].trim()}`
                : 'assets/placeholder.png';

            // Note: Wishlist functionality for dynamically loaded cards would require
            // re-attaching event listeners or using event delegation. For now,
            // the wishlist button is omitted from AJAX-loaded cards for simplicity.
            // A full implementation would require a global wishlist handler.

            carCard.innerHTML = `
                <a href="car_details.php?id=${car.id}">
                    <img src="${escapeHTML(firstImage)}" alt="${escapeHTML(car.brand)} ${escapeHTML(car.model)}">
                    <div class="car-card-content">
                        <h3>${escapeHTML(car.brand)} ${escapeHTML(car.model)}</h3>
                        <div class="card-rating">
                            <div class="star-rating" style="--rating: ${car.avg_rating};"></div>
                            <span>(${car.review_count})</span>
                        </div>
                        <p class="price">$${Number(car.price).toLocaleString()}</p>
                        <ul>
                            <li><strong>Year:</strong> ${escapeHTML(car.year)}</li>
                            <li><strong>Mileage:</strong> ${Number(car.mileage).toLocaleString()} km</li>
                        </ul>
                    </div>
                </a>
            `;
            return carCard;
        }

        function escapeHTML(str) {
            if (str === null || str === undefined) {
                return '';
            }
            return str.toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    });
    </script>
</body>
</html>

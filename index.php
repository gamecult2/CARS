<?php
require_once 'functions.php';

// Fetch cars from the database
// We'll get the filter values from the $_GET superglobal
$filters = [
    'brand' => $_GET['brand'] ?? null,
    'model' => $_GET['model'] ?? null,
    'year_min' => $_GET['year_min'] ?? null,
    'year_max' => $_GET['year_max'] ?? null,
    'price_min' => $_GET['price_min'] ?? null,
    'price_max' => $_GET['price_max'] ?? null,
    'mileage_min' => $_GET['mileage_min'] ?? null,
    'mileage_max' => $_GET['mileage_max'] ?? null,
    'body_type' => $_GET['body_type'] ?? null,
    'transmission' => $_GET['transmission'] ?? null,
    'fuel_type' => $_GET['fuel_type'] ?? null,
    'steering' => $_GET['steering'] ?? null,
    'color_hex' => $_GET['color_hex'] ?? null,
    'accessories' => $_GET['accessories'] ?? [],
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
    <link rel="stylesheet" href="compare.css">
    <link rel="stylesheet" href="search-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.1/nouislider.min.css">
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
    $advanced_options = get_advanced_filter_options($pdo);
    ?>

    <main class="container">
        <section class="search-filter-v2">
            <h3>ADVANCED SEARCH</h3>
            <form action="index.php" method="GET" id="search-form">
                <div class="filter-grid">
                    <!-- Basic Filters -->
                    <div class="filter-group">
                        <label>Make</label>
                        <select name="brand">
                            <option value="">All Makes</option>
                            <?php
                            // Assuming get_brands_with_count exists and is suitable
                            $brands = get_brands_with_count($pdo);
                            foreach ($brands as $brand_item) {
                                echo '<option value="'._e($brand_item['brand']).'" '.($filters['brand'] == $brand_item['brand'] ? 'selected' : '').'>'._e($brand_item['brand']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Model</label>
                        <input type="text" name="model" placeholder="Any Model" value="<?= _e($filters['model']) ?>">
                    </div>
                    <div class="filter-group">
                        <label>Body Type</label>
                        <select name="body_type">
                            <option value="">Any Body Type</option>
                            <?php foreach ($car_attributes['body_type'] as $value): ?>
                                <option value="<?= _e($value) ?>" <?= ($filters['body_type'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group filter-group-range">
                        <label>Year</label>
                        <div id="year-slider"></div>
                        <div class="slider-values" id="year-slider-values"></div>
                        <input type="hidden" name="year_min" id="year_min">
                        <input type="hidden" name="year_max" id="year_max">
                    </div>
                    <div class="filter-group filter-group-range">
                        <label>Price</label>
                        <div id="price-slider"></div>
                        <div class="slider-values" id="price-slider-values"></div>
                        <input type="hidden" name="price_min" id="price_min">
                        <input type="hidden" name="price_max" id="price_max">
                    </div>
                </div>

                <div class="advanced-filter-toggle" id="advanced-filter-toggle">
                    MORE OPTIONS <span>&#9660;</span>
                </div>

                <div class="advanced-filters" id="advanced-filters">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Transmission</label>
                            <select name="transmission">
                                <option value="">Any</option>
                                <?php foreach ($car_attributes['transmission'] as $value): ?>
                                    <option value="<?= _e($value) ?>" <?= ($filters['transmission'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Fuel Type</label>
                            <select name="fuel_type">
                                <option value="">Any</option>
                                <?php foreach ($car_attributes['fuel_type'] as $value): ?>
                                    <option value="<?= _e($value) ?>" <?= ($filters['fuel_type'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Steering</label>
                            <select name="steering">
                                <option value="">Any</option>
                                 <?php foreach ($car_attributes['steering'] as $value): ?>
                                    <option value="<?= _e($value) ?>" <?= ($filters['steering'] == $value) ? 'selected' : '' ?>><?= _e($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group filter-group-full filter-group-range">
                            <label>Mileage</label>
                            <div id="mileage-slider"></div>
                            <div class="slider-values" id="mileage-slider-values"></div>
                            <input type="hidden" name="mileage_min" id="mileage_min">
                            <input type="hidden" name="mileage_max" id="mileage_max">
                        </div>
                         <div class="filter-group filter-group-full">
                            <label>Color</label>
                            <div class="color-swatches" id="color-swatches">
                                <?php foreach ($advanced_options['colors'] as $color): ?>
                                    <span class="color-swatch" data-color-hex="<?= _e($color['color_hex']) ?>" style="background-color: <?= _e($color['color_hex']) ?>;" title="<?= _e($color['exterior_color']) ?>"></span>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="color_hex" id="color_hex_input">
                        </div>
                        <div class="filter-group filter-group-full">
                            <label>Accessories</label>
                            <div class="accessories-grid">
                                <?php foreach ($advanced_options['accessories'] as $accessory): ?>
                                    <label class="accessory-checkbox">
                                        <input type="checkbox" name="accessories[]" value="<?= _e($accessory) ?>"> <?= _e($accessory) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Search</button>
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

    <div class="comparison-tray" id="comparison-tray">
        <div class="comparison-items" id="comparison-items">
            <!-- Items will be added here by JavaScript -->
        </div>
        <div class="comparison-actions">
            <a href="#" id="compare-button" class="btn-compare disabled">Compare</a>
            <button class="btn-clear" id="clear-compare">Clear</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.1/nouislider.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Compare Tray Logic
        const MAX_COMPARE = 4;
        let compareItems = JSON.parse(sessionStorage.getItem('compareItems')) || [];
        const tray = document.getElementById('comparison-tray');
        const trayItemsContainer = document.getElementById('comparison-items');
        const compareButton = document.getElementById('compare-button');
        const clearButton = document.getElementById('clear-compare');

        function updateTray() {
            trayItemsContainer.innerHTML = '';
            if (compareItems.length > 0) {
                tray.classList.add('visible');
            } else {
                tray.classList.remove('visible');
            }

            compareItems.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'comparison-item';
                itemEl.innerHTML = `<span>${escapeHTML(item.name)}</span><span class="remove-compare" data-id="${item.id}">&times;</span>`;
                trayItemsContainer.appendChild(itemEl);
            });

            // Update compare button state
            if (compareItems.length > 1) {
                compareButton.classList.remove('disabled');
                const ids = compareItems.map(item => item.id).join(',');
                compareButton.href = `compare.php?ids=${ids}`;
            } else {
                compareButton.classList.add('disabled');
                compareButton.href = '#';
            }

            // Sync checkboxes
            document.querySelectorAll('.compare-checkbox').forEach(checkbox => {
                const carId = checkbox.dataset.carId;
                checkbox.checked = compareItems.some(item => item.id == carId);
            });
        }

        function handleCompareChange(event) {
            const checkbox = event.target;
            if (!checkbox.classList.contains('compare-checkbox')) return;

            const carId = checkbox.dataset.carId;
            const carName = checkbox.dataset.carName;

            if (checkbox.checked) {
                if (compareItems.length >= MAX_COMPARE) {
                    alert(`You can only compare up to ${MAX_COMPARE} cars.`);
                    checkbox.checked = false;
                    return;
                }
                if (!compareItems.some(item => item.id == carId)) {
                    compareItems.push({ id: carId, name: carName });
                }
            } else {
                compareItems = compareItems.filter(item => item.id != carId);
            }

            sessionStorage.setItem('compareItems', JSON.stringify(compareItems));
            updateTray();
        }

        clearButton.addEventListener('click', () => {
            compareItems = [];
            sessionStorage.removeItem('compareItems');
            updateTray();
        });

        document.body.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-compare')) {
                const carId = event.target.dataset.id;
                compareItems = compareItems.filter(item => item.id != carId);
                sessionStorage.setItem('compareItems', JSON.stringify(compareItems));
                updateTray();
            }
        });

        // Initial tray setup
        updateTray();

        // Add master event listener for compare checkboxes
        document.body.addEventListener('change', handleCompareChange);

        // Slider Initialization
        function createSlider(sliderId, valuesId, minInputId, maxInputId, start, end, step, format) {
            const slider = document.getElementById(sliderId);
            const valuesDiv = document.getElementById(valuesId);
            const minInput = document.getElementById(minInputId);
            const maxInput = document.getElementById(maxInputId);

            noUiSlider.create(slider, {
                start: [start, end],
                connect: true,
                step: step,
                range: {
                    'min': start,
                    'max': end
                },
                format: {
                    to: function (value) {
                        return Math.round(value);
                    },
                    from: function (value) {
                        return Number(value);
                    }
                }
            });

            slider.noUiSlider.on('update', function (values) {
                const formattedValues = values.map(v => format(v));
                valuesDiv.innerHTML = `${formattedValues[0]} - ${formattedValues[1]}`;
                minInput.value = values[0];
                maxInput.value = values[1];
            });

            slider.noUiSlider.on('change', performSearch); // Trigger search on slider change
        }

        createSlider('year-slider', 'year-slider-values', 'year_min', 'year_max', 2000, new Date().getFullYear(), 1, v => v);
        createSlider('price-slider', 'price-slider-values', 'price_min', 'price_max', 0, 100000, 1000, v => `$${Number(v).toLocaleString()}`);
        createSlider('mileage-slider', 'mileage-slider-values', 'mileage_min', 'mileage_max', 0, 300000, 5000, v => `${Number(v).toLocaleString()} km`);

        // Color Swatch Logic
        const colorSwatches = document.getElementById('color-swatches');
        const colorHexInput = document.getElementById('color_hex_input');
        colorSwatches.addEventListener('click', function(event) {
            if (event.target.classList.contains('color-swatch')) {
                // Clear previous selection
                document.querySelectorAll('.color-swatch.selected').forEach(swatch => swatch.classList.remove('selected'));

                const selectedHex = event.target.dataset.colorHex;

                if (colorHexInput.value === selectedHex) {
                    // Deselect if clicking the same color
                    colorHexInput.value = '';
                } else {
                    // Select new color
                    event.target.classList.add('selected');
                    colorHexInput.value = selectedHex;
                }
                performSearch();
            }
        });

        // Accessory Checkbox Logic
        document.querySelectorAll('.accessory-checkbox input').forEach(checkbox => {
            checkbox.addEventListener('change', performSearch);
        });

        // Advanced Search Toggle
        const advancedToggle = document.getElementById('advanced-filter-toggle');
        const advancedFilters = document.getElementById('advanced-filters');
        advancedToggle.addEventListener('click', () => {
            const isVisible = advancedFilters.style.display === 'block';
            advancedFilters.style.display = isVisible ? 'none' : 'block';
            advancedToggle.querySelector('span').innerHTML = isVisible ? '&#9660;' : '&#9650;';
        });

        // Search Form Logic
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
                <div class="car-card-actions">
                    <label>
                        <input type="checkbox" class="compare-checkbox" data-car-id="${car.id}" data-car-name="${escapeHTML(car.brand)} ${escapeHTML(car.model)}">
                        Compare
                    </label>
                </div>
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

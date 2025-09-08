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

// --- Handle Place Order Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (is_user_logged_in()) {
        $user_id = $_SESSION['user_id'];

        // Create the order
        $order_id = create_order($pdo, $user_id, $car_id);

        // Redirect to the new order details page
        redirect("order_details.php?id=$order_id");
    } else {
        // Should not happen if button is hidden, but as a fallback
        redirect('login.php');
    }
}

// Explode images string into an array
$images = !empty($car['images']) ? explode(',', $car['images']) : [];

// Prepare for social sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$share_text = "Check out this " . $car['year'] . " " . $car['brand'] . " " . $car['model'];

// --- Handle Review Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (is_user_logged_in()) {
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment']);
        if ($rating >= 1 && $rating <= 5) {
            submit_review($pdo, $car_id, $session_user['id'], $rating, $comment);
            redirect("car_details.php?id=$car_id&review=success");
        }
    }
}

// Fetch reviews for this car
$reviews = get_reviews_for_car($pdo, $car_id);
$average_rating_data = get_average_rating_for_car($pdo, $car_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="car_details.css"> <!-- Specific styles for this page -->
    <link rel="stylesheet" href="reviews.css"> <!-- Styles for reviews -->
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

        <div class="title-bar">
            <h1 class="car-title"><?= _e($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?></h1>
            <div class="title-actions">
                <?php if (is_user_logged_in()):
                    $in_wishlist = is_car_in_wishlist($pdo, $session_user['id'], $car['id']);
                ?>
                    <button class="wishlist-btn <?= $in_wishlist ? 'active' : '' ?>" data-car-id="<?= $car['id'] ?>" onclick="toggleWishlist(this)">
                        <span class="heart-icon"><?= $in_wishlist ? '♥' : '♡' ?></span>
                        <span class="wishlist-text"><?= $in_wishlist ? 'Saved' : 'Save' ?></span>
                    </button>
                <?php endif; ?>
                <?php if (is_admin_logged_in()): ?>
                    <a href="/Admin/edit_car.php?id=<?= $car['id'] ?>" class="btn admin-edit-btn" target="_blank">Edit Car</a>
                <?php endif; ?>
            </div>
        </div>

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
                        <li><span>Exterior Color</span><strong><?= _e($car['exterior_color']) ?></strong></li>
                        <li><span>Seats</span><strong><?= _e($car['seats']) ?></strong></li>
                        <li><span>Dimensions</span><strong><?= _e($car['dimensions']) ?></strong></li>
                        <li><span>Weight (kg)</span><strong><?= _e($car['weight']) ?></strong></li>
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

                <!-- Reviews Section -->
                <div class="reviews-section card">
                    <h3>Customer Reviews & Ratings</h3>
                    <div class="rating-summary">
                        <div class="average-rating"><?= number_format($average_rating_data['average'], 1) ?></div>
                        <div class="star-rating" style="--rating: <?= $average_rating_data['average'] ?>;"></div>
                        <div class="review-count">(<?= $average_rating_data['count'] ?> reviews)</div>
                    </div>

                    <div class="review-list">
                        <?php if (empty($reviews)): ?>
                            <p>No reviews yet. Be the first to write one!</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-author"><strong><?= _e($review['user_name']) ?></strong></div>
                                    <div class="star-rating" style="--rating: <?= $review['rating'] ?>;"></div>
                                    <p class="review-comment"><?= nl2br(_e($review['comment'])) ?></p>
                                    <div class="review-date"><?= date("F j, Y", strtotime($review['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (is_user_logged_in()): ?>
                        <div class="review-form">
                            <h4>Write a Review</h4>
                            <form action="car_details.php?id=<?= $car_id ?>" method="POST">
                                <div class="form-group">
                                    <label>Your Rating</label>
                                    <div class="star-input">
                                        <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars"></label>
                                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"></label>
                                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"></label>
                                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"></label>
                                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment">Your Comment</label>
                                    <textarea name="comment" id="comment" rows="4"></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn">Submit Review</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="details-sidebar">
                <div class="price-calculator card">
                    <div class="box-title">Total Price Calculator</div>
                    <div class="price-tab">
                        <span class="tab cur-tab" onclick="updatePrice('EXW', this)">EXW</span>
                        <span class="tab" onclick="updatePrice('FOB', this)">FOB</span>
                        <span class="tab" onclick="updatePrice('CFR', this)">CFR</span>
                        <span class="tab" onclick="updatePrice('CIF', this)">CIF</span>
                    </div>
                    <div class="price-detail" id="price-breakdown-container">
                        <!-- JS will populate this -->
                    </div>
                    <div class="total-price">
                        = Total Price($): <span class="price" id="total-price-display"></span>
                    </div>
                    <div class="price-desp" id="price-term-description">
                        <!-- JS will populate this -->
                    </div>
                </div>

                <div class="inquiry-card card">
                    <h3>Purchase This Car</h3>
                    <?php if (is_user_logged_in()): ?>
                        <form action="car_details.php?id=<?= $car_id ?>" method="POST">
                            <p>Click the button below to start the purchasing process. Our team will contact you shortly.</p>
                            <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="login.php?redirect=car_details.php?id=<?= $car_id ?>">log in</a> or <a href="register.php">register</a> to place an order.</p>
                    <?php endif; ?>
                </div>

                <div class="social-share card">
                    <h4>Share This Car</h4>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($current_url) ?>" target="_blank" class="share-btn facebook">Facebook</a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($current_url) ?>&text=<?= urlencode($share_text) ?>" target="_blank" class="share-btn twitter">X (Twitter)</a>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($share_text . ' ' . $current_url) ?>" target="_blank" class="share-btn whatsapp">WhatsApp</a>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    <script>
        function changeImage(newSrc) {
            document.getElementById('main-car-image').src = newSrc;
        }

        const vehiclePrice = <?= $car['price'] ?>;
        const fees = {
            inspection: 65,
            handling: 400,
            service: 300,
            banking: 50,
            transport: 250, // Example fee
            freight: 1200,   // Example fee
            insurance: 350   // Example fee
        };

        const terms = {
            EXW: {
                desc: 'The price at the vehicle\'s location, without domestic logistics or international freight. The buyer is responsible for all costs from the location to the final destination.',
                included: ['inspection', 'handling', 'service', 'banking']
            },
            FOB: {
                desc: 'Includes the cost of delivering the vehicle to the port and loading it onto the ship. Does not include international freight or insurance.',
                included: ['inspection', 'handling', 'service', 'banking', 'transport']
            },
            CFR: {
                desc: 'Includes the cost of the vehicle, all export fees, and international freight to the destination port. Does not include insurance.',
                included: ['inspection', 'handling', 'service', 'banking', 'transport', 'freight']
            },
            CIF: {
                desc: 'Includes the cost of the vehicle, all export fees, international freight, and insurance to the destination port.',
                included: ['inspection', 'handling', 'service', 'banking', 'transport', 'freight', 'insurance']
            }
        };

        const breakdownContainer = document.getElementById('price-breakdown-container');
        const totalPriceDisplay = document.getElementById('total-price-display');
        const termDescription = document.getElementById('price-term-description');

        function formatCurrency(value) {
            return new Intl.NumberFormat('en-US').format(value);
        }

        function updatePrice(term, clickedTab) {
            // Update active tab style
            document.querySelectorAll('.price-tab .tab').forEach(tab => tab.classList.remove('cur-tab'));
            clickedTab.classList.add('cur-tab');

            const termData = terms[term];
            let totalPrice = vehiclePrice;
            let breakdownHtml = `<p>+ Vehicle Price ($)：${formatCurrency(vehiclePrice)}</p>`;

            termData.included.forEach(feeKey => {
                const feeValue = fees[feeKey];
                totalPrice += feeValue;
                const feeName = feeKey.charAt(0).toUpperCase() + feeKey.slice(1);
                breakdownHtml += `<p>+ ${feeName} Fee ($)：${formatCurrency(feeValue)}</p>`;
            });

            breakdownContainer.innerHTML = breakdownHtml;
            totalPriceDisplay.innerText = `${term} ${formatCurrency(totalPrice)}`;
            termDescription.innerText = termData.desc;
        }

        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            updatePrice('EXW', document.querySelector('.price-tab .tab'));
        });

        async function toggleWishlist(button, isCard = false) {
            const carId = button.dataset.carId;
            const formData = new FormData();
            formData.append('car_id', carId);

            try {
                const response = await fetch('api_wishlist.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const heartIcon = button.querySelector('.heart-icon');
                    const wishlistText = button.querySelector('.wishlist-text');

                    button.classList.toggle('active', result.in_wishlist);
                    heartIcon.innerText = result.in_wishlist ? '♥' : '♡';
                    if (wishlistText) {
                        wishlistText.innerText = result.in_wishlist ? 'Saved' : 'Save';
                    }

                    // Update header count
                    document.querySelector('.wishlist-badge').innerText = result.count;
                }
            } catch (error) {
                console.error('Error toggling wishlist:', error);
            }
        }
    </script>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

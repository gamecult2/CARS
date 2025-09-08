<div class="car-card">
    <a href="car_details.php?id=<?= $car['id'] ?>">
        <?php
            // Display the first image, or a placeholder if none exist
            $images = !empty($car['images']) ? explode(',', $car['images']) : [];
            $first_image = !empty($images) ? 'images/' . trim($images[0]) : 'assets/placeholder.png';
        ?>
        <img src="<?= _e($first_image) ?>" alt="<?= _e($car['brand'] . ' ' . $car['model']) ?>">
        <div class="car-card-content">
            <h3><?= _e($car['brand'] . ' ' . $car['model']) ?></h3>
            <p class="price">$<?= number_format($car['price']) ?></p>
            <ul>
                <li><strong>Year:</strong> <?= _e($car['year']) ?></li>
                <li><strong>Mileage:</strong> <?= number_format($car['mileage']) ?> km</li>
            </ul>
        </div>
    </a>
</div>

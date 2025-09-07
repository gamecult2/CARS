<?php
require_once 'functions.php';

// Protect this page
if (!is_user_logged_in()) {
    redirect('login.php');
}

$page_title = 'My Profile';
$session_user = get_session_user();
$user_id = $session_user['id'];

// Fetch all orders for the current user
$orders = get_orders_for_user($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <?php require_once 'partials/header.php'; ?>

    <main class="container">
        <h1>Welcome, <?= _e($session_user['name']) ?>!</h1>
        <p>This is your profile page. Here you can view your orders and manage your account details.</p>

        <div class="profile-layout">
            <div class="profile-main">
                <h2>My Orders</h2>
                <?php if (empty($orders)): ?>
                    <p>You have not placed any orders yet.</p>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Car</th>
                                <th>Date Placed</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= _e($order['id']) ?></td>
                                    <td><?= _e($order['year'] . ' ' . $order['brand'] . ' ' . $order['model']) ?></td>
                                    <td><?= date("F j, Y", strtotime($order['created_at'])) ?></td>
                                    <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>"><?= _e($order['order_status']) ?></span></td>
                                    <td><a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm">View Details</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <aside class="profile-sidebar">
                <div class="account-card">
                    <h3>Account Details</h3>
                    <p><strong>Name:</strong> <?= _e($session_user['name']) ?></p>
                    <p><strong>Email:</strong> <?= _e($_SESSION['user_email']) // Fetched at login ?></p>
                    <a href="#" class="btn btn-secondary">Edit Account</a>
                </div>
            </aside>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>
</body>
</html>

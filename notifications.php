<?php
require_once 'functions.php';

// Protect this page
if (!($session_user = get_session_user())) {
    redirect('login.php');
}

$page_title = 'My Notifications';
$user_id = $session_user['id'];
$is_admin_or_mod = ($session_user['role'] === 'admin' || $session_user['role'] === 'moderator');
$user_id_for_notif = $is_admin_or_mod ? 1 : $user_id;

// Fetch all notifications for the user
$notifications = get_notifications_for_user($pdo, $user_id_for_notif);

// Mark notifications as read now that the user has seen them
mark_notifications_as_read($pdo, $user_id_for_notif);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="notifications.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <h1><?= _e($page_title) ?></h1>

        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
                <p>You have no notifications.</p>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>">
                        <a href="<?= _e($notif['link']) ?>">
                            <p class="message"><?= _e($notif['message']) ?></p>
                            <span class="time"><?= date("F j, Y, g:i a", strtotime($notif['created_at'])) ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>

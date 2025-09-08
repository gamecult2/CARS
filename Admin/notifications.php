<?php
$page_title = 'Notifications';
require_once 'partials/header.php';

// For simplicity, all admin/mod notifications are sent to a single user ID (e.g., 1)
// In a real system, you might have a more complex notification routing system.
$admin_notif_user_id = 1;

// Fetch all notifications for the admin user
$notifications = get_notifications_for_user($pdo, $admin_notif_user_id);

// Mark notifications as read now that they have been seen
mark_notifications_as_read($pdo, $admin_notif_user_id);

?>

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

<?php
// Re-use client-side CSS for notifications
echo '<link rel="stylesheet" href="../notifications.css">';
require_once 'partials/footer.php';
?>

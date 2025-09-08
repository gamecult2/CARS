<header>
    <div class="container">
        <h1><a href="index.php">Car Dealership</a></h1>
        <nav>
            <ul>
                <?php
                $session_user = get_session_user();
                if ($session_user):
                    $is_admin_or_mod = ($session_user['role'] === 'admin' || $session_user['role'] === 'moderator');
                    $user_id_for_notif = $is_admin_or_mod ? 1 : $session_user['id']; // Admin/mod notifications are sent to user ID 1
                    $notif_count = get_unread_notification_count($pdo, $user_id_for_notif);
                ?>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="messages.php">My Messages</a></li>
                    <li><a href="notifications.php">Notifications <?php if($notif_count > 0) echo "<span class='notif-badge'>{$notif_count}</span>"; ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="promotions.php">Promotions</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>

                <?php if ($session_user && $is_admin_or_mod): ?>
                    <li><a href="/Admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<style>.notif-badge { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; vertical-align: top; }</style>

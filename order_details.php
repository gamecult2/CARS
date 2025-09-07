<?php
require_once 'functions.php';

// Protect this page
if (!is_user_logged_in()) {
    redirect('login.php');
}

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$session_user = get_session_user();
$user_id = $session_user['id'];

if (!$order_id) {
    redirect('profile.php');
}

// Fetch order details. This function should also validate that the user owns this order.
// Let's assume get_order_details will be modified to do this check. For now, we check manually.
$order = get_order_details($pdo, $order_id);

if (!$order || $order['user_id'] != $user_id) {
    redirect('profile.php');
}

// --- Handle Message Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        add_order_message($pdo, $order_id, $user_id, 'user', $message);
        add_order_history($pdo, $order_id, 'Message Sent', 'User sent a message.', $user_id, 'user');
        // Notify admin
        create_notification($pdo, 1, "New message on order #{$order_id}", "Admin/view_order.php?id={$order_id}");
        redirect("order_details.php?id=$order_id");
    }
}

// --- Handle File Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['order_file'])) {
    $file = $_FILES['order_file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "/uploads/orders/user_{$user_id}/order_{$order_id}/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = basename($file['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $db_path = "uploads/orders/user_{$user_id}/order_{$order_id}/" . $file_name;
            add_order_file($pdo, $order_id, $user_id, 'user', $file_name, $db_path);
            add_order_history($pdo, $order_id, 'File Uploaded', "User uploaded: {$file_name}", $user_id, 'user');
            // Notify admin
            create_notification($pdo, 1, "New file uploaded to order #{$order_id}", "Admin/view_order.php?id={$order_id}");
            redirect("order_details.php?id=$order_id");
        } else {
            $errors[] = "Failed to upload file.";
        }
    }
}

$page_title = "Order Details #" . $order['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="order_details.css">
</head>
<body>
    <?php require_once 'partials/header.php'; ?>

    <main class="container">
        <a href="profile.php">&larr; Back to My Orders</a>
        <h1><?= _e($page_title) ?></h1>

        <div class="order-summary card">
            <h2>For Car: <?= _e($order['year'] . ' ' . $order['brand'] . ' ' . $order['model']) ?></h2>
            <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>"><?= _e($order['order_status']) ?></span></p>
            <p><strong>Order Placed:</strong> <?= date("F j, Y", strtotime($order['created_at'])) ?></p>
        </div>

        <div class="order-layout">
            <div class="order-main">
                <h3>Order History & Timeline</h3>
                <div class="timeline">
                    <?php if (empty($order['history'])): ?>
                        <p>No history found for this order.</p>
                    <?php else: ?>
                        <?php foreach ($order['history'] as $event): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <span class="timeline-time"><?= date("M j, Y, g:i a", strtotime($event['created_at'])) ?></span>
                                    <h4 class="timeline-title"><?= _e($event['action']) ?></h4>
                                    <p class="timeline-body"><?= _e($event['description']) ?> (by <?= _e($event['actor_type']) ?>)</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <aside class="order-sidebar">
                <div class="communication-card card">
                    <h4>Messages</h4>
                    <div class="message-thread">
                        <?php if (empty($order['messages'])): ?>
                            <p>No messages yet.</p>
                        <?php else: ?>
                            <?php foreach ($order['messages'] as $msg): ?>
                                <div class="message-item <?= $msg['sender_type'] === 'user' ? 'sent' : 'received' ?>">
                                    <strong><?= _e($msg['sender_type']) ?>:</strong>
                                    <p><?= nl2br(_e($msg['message'])) ?></p>
                                    <small><?= date("M j, Y, g:i a", strtotime($msg['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <form action="order_details.php?id=<?= $order_id ?>" method="POST" class="message-form">
                        <textarea name="message" placeholder="Type a message..." rows="3" required></textarea>
                        <button type="submit" name="send_message" class="btn btn-sm">Send</button>
                    </form>
                </div>
                <div class="files-card card">
                    <h4>Documents</h4>
                    <ul class="file-list">
                        <?php if (empty($order['files'])): ?>
                            <li>No documents uploaded yet.</li>
                        <?php else: ?>
                            <?php foreach ($order['files'] as $file): ?>
                                <li>
                                    <a href="<?= _e($file['file_path']) ?>" target="_blank"><?= _e($file['file_name']) ?></a>
                                    <small>(by <?= _e($file['uploader_type']) ?> on <?= date("M j", strtotime($file['created_at'])) ?>)</small>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <form action="order_details.php?id=<?= $order_id ?>" method="POST" enctype="multipart/form-data" class="upload-form">
                        <label for="order_file">Upload Document</label>
                        <input type="file" name="order_file" id="order_file" required>
                        <button type="submit" class="btn btn-sm">Upload</button>
                    </form>
                </div>
            </aside>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; ?>
</body>
</html>

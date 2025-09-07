<?php
$page_title = 'View Order';
require_once 'partials/header.php';

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id) {
    redirect('manage_orders.php');
}

$order = get_order_details($pdo, $order_id);
if (!$order) {
    redirect('manage_orders.php');
}

$session_user = get_session_user();
$actor_id = $session_user['id'];
$actor_type = $session_user['role']; // 'admin' or 'moderator'

// --- Handle Status Update ---
if (isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    // In a real app, you'd validate this status against a list
    $stmt = $pdo->prepare("UPDATE orders SET order_status = :status WHERE id = :id");
    $stmt->execute([':status' => $new_status, ':id' => $order_id]);

    add_order_history($pdo, $order_id, 'Status Updated', "Status changed to {$new_status}", $actor_id, $actor_type);
    create_notification($pdo, $order['user_id'], "The status of your order #{$order_id} was updated to '{$new_status}'.", "order_details.php?id={$order_id}");

    // Send email for important updates
    if (in_array($new_status, ['Completed', 'Cancelled', 'Ready for Pickup'])) {
        send_order_email($order['user_email'], "Update on your Order #{$order_id}", "The status of your order has been updated to: {$new_status}.");
    }

    redirect("view_order.php?id=$order_id");
}

// --- Handle Message Submission ---
if (isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        add_order_message($pdo, $order_id, $actor_id, $actor_type, $message);
        add_order_history($pdo, $order_id, 'Message Sent', "{$actor_type} sent a message.", $actor_id, $actor_type);
        create_notification($pdo, $order['user_id'], "You have a new message regarding order #{$order_id}", "order_details.php?id={$order_id}");
        redirect("view_order.php?id=$order_id");
    }
}

// --- Handle File Upload ---
if (isset($_FILES['order_file'])) {
    $file = $_FILES['order_file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "/../uploads/orders/user_{$order['user_id']}/order_{$order_id}/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = basename($file['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $db_path = "uploads/orders/user_{$order['user_id']}/order_{$order_id}/" . $file_name;
            add_order_file($pdo, $order_id, $actor_id, $actor_type, $file_name, $db_path);
            add_order_history($pdo, $order_id, 'File Uploaded', "{$actor_type} uploaded: {$file_name}", $actor_id, $actor_type);
            create_notification($pdo, $order['user_id'], "A new file has been uploaded for order #{$order_id}", "order_details.php?id={$order_id}");
            redirect("view_order.php?id=$order_id");
        }
    }
}

?>
<a href="manage_orders.php">&larr; Back to All Orders</a>
<h1>Order #<?= _e($order['id']) ?></h1>

<div class="order-summary card">
    <p><strong>Client:</strong> <?= _e($order['user_name']) ?> (<?= _e($order['user_email']) ?>)</p>
    <p><strong>Car:</strong> <?= _e($order['year'] . ' ' . $order['brand'] . ' ' . $order['model']) ?></p>
    <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>"><?= _e($order['order_status']) ?></span></p>
</div>

<div class="order-layout">
    <div class="order-main">
        <h3>Order History & Timeline</h3>
        <div class="timeline">
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
        </div>
    </div>
    <aside class="order-sidebar">
        <div class="management-card card">
            <h4>Manage Order</h4>
            <form action="view_order.php?id=<?= $order_id ?>" method="POST">
                <label for="order_status">Update Status</label>
                <select name="order_status" id="order_status">
                    <?php
                    $statuses = ['Pending','Processing','Documents Requested','Ready for Pickup','Completed','Cancelled'];
                    foreach ($statuses as $status) {
                        $selected = ($order['order_status'] == $status) ? 'selected' : '';
                        echo "<option value='{$status}' {$selected}>{$status}</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="update_status" class="btn">Update</button>
            </form>
        </div>

        <div class="communication-card card">
            <h4>Messages</h4>
            <div class="message-thread">
                <?php if (empty($order['messages'])): ?>
                    <p>No messages yet.</p>
                <?php else: ?>
                    <?php foreach ($order['messages'] as $msg): ?>
                        <div class="message-item <?= $msg['sender_type'] !== 'user' ? 'sent' : 'received' ?>">
                            <strong><?= _e($msg['sender_type']) ?>:</strong>
                            <p><?= nl2br(_e($msg['message'])) ?></p>
                            <small><?= date("M j, Y, g:i a", strtotime($msg['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form action="view_order.php?id=<?= $order_id ?>" method="POST" class="message-form">
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
                            <a href="../<?= _e($file['file_path']) ?>" target="_blank"><?= _e($file['file_name']) ?></a>
                            <small>(by <?= _e($file['uploader_type']) ?> on <?= date("M j", strtotime($file['created_at'])) ?>)</small>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <form action="view_order.php?id=<?= $order_id ?>" method="POST" enctype="multipart/form-data" class="upload-form">
                <label for="order_file">Upload Document</label>
                <input type="file" name="order_file" id="order_file" required>
                <button type="submit" class="btn btn-sm">Upload</button>
            </form>
        </div>
    </aside>
</div>

<?php
// Re-use client-side CSS for timeline and badges
echo '<link rel="stylesheet" href="../order_details.css">';
echo '<style>.management-card select { width: 100%; padding: 8px; margin-bottom: 10px; } .management-card .btn { width: 100%; }</style>';
require_once 'partials/footer.php';
?>

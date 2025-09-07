<?php
$page_title = 'View Inquiries';
require_once 'partials/header.php';

// --- Handle Delete Request ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $message_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($message_id_to_delete) {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute([':id' => $message_id_to_delete]);
        redirect('messages.php?status=deleted');
    }
}

// Fetch all messages with user and car information
$sql = "SELECT
            m.id,
            m.message,
            m.date_sent,
            u.name as user_name,
            u.email as user_email,
            c.brand as car_brand,
            c.model as car_model,
            c.id as car_id
        FROM messages m
        JOIN users u ON m.user_id = u.id
        JOIN cars c ON m.car_id = c.id
        ORDER BY m.date_sent DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();
?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div class="alert alert-success">Message has been successfully deleted.</div>
<?php endif; ?>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>From</th>
                <th>Car</th>
                <th>Message</th>
                <th>Date Sent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">There are no inquiries.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td>
                            <strong><?= _e($message['user_name']) ?></strong><br>
                            <small><?= _e($message['user_email']) ?></small>
                        </td>
                        <td>
                            <a href="../car_details.php?id=<?= _e($message['car_id']) ?>" target="_blank">
                                <?= _e($message['car_brand'] . ' ' . $message['car_model']) ?>
                            </a>
                        </td>
                        <td><?= nl2br(_e($message['message'])) ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($message['date_sent'])) ?></td>
                        <td class="actions">
                            <a href="messages.php?action=delete&id=<?= $message['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Add some specific styles for alerts
echo <<<HTML
<style>
.alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
.alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
.btn-sm { padding: 5px 10px; font-size: 0.8rem; }
.table-container { overflow-x: auto; }
td { vertical-align: top; }
</style>
HTML;

require_once 'partials/footer.php';
?>

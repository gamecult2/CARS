<?php
// This is a simple API endpoint for the ADMIN to fetch new messages.
header('Content-Type: application/json');
// We are in the /Admin directory
require_once '../functions.php';

if (!is_admin_logged_in()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$conversation_id = filter_input(INPUT_GET, 'conversation_id', FILTER_VALIDATE_INT);
$last_message_id = filter_input(INPUT_GET, 'last_message_id', FILTER_VALIDATE_INT) ?: 0;

if (!$conversation_id) {
    echo json_encode(['error' => 'Invalid conversation ID']);
    exit;
}

// Admin has access to all conversations, so no need for an access check here,
// but in a more complex system with multiple admin roles, we would add one.

// Fetch messages newer than the last one the client has
$stmt = $pdo->prepare("SELECT cm.*, u.name as user_name, a.username as admin_name
                       FROM chat_messages cm
                       LEFT JOIN users u ON cm.sender_id = u.id AND cm.sender_type = 'user'
                       LEFT JOIN admin a ON cm.sender_id = a.id AND cm.sender_type = 'admin'
                       WHERE cm.conversation_id = :conv_id AND cm.id > :last_id
                       ORDER BY cm.created_at ASC");
$stmt->execute([':conv_id' => $conversation_id, ':last_id' => $last_message_id]);

$messages = $stmt->fetchAll();

echo json_encode($messages);
?>

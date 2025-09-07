<?php
// This is a simple API endpoint to fetch new messages for a conversation.
header('Content-Type: application/json');
require_once 'functions.php';

if (!is_user_logged_in()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$conversation_id = filter_input(INPUT_GET, 'conversation_id', FILTER_VALIDATE_INT);
$last_message_id = filter_input(INPUT_GET, 'last_message_id', FILTER_VALIDATE_INT) ?: 0;
$user_id = $_SESSION['user_id'];

if (!$conversation_id) {
    echo json_encode(['error' => 'Invalid conversation ID']);
    exit;
}

// Validate that the user has access to this conversation
$conversation = get_conversation_by_id($pdo, $conversation_id, $user_id);
if (!$conversation) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

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

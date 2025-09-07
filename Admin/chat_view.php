<?php
$page_title = 'View Conversation';
require_once 'partials/header.php';

$conversation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$admin_id = $_SESSION['admin_id'];

if (!$conversation_id) {
    redirect('messages.php');
}

// get_conversation_by_id validates access for admin by default
$conversation = get_conversation_by_id($pdo, $conversation_id, null, true);

if (!$conversation) {
    redirect('messages.php');
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        send_chat_message($pdo, $conversation_id, $admin_id, 'admin', $message);
        redirect("chat_view.php?id=$conversation_id");
    }
}

$messages = get_messages_for_conversation($pdo, $conversation_id);
?>

<a href="messages.php">&larr; Back to all conversations</a>
<h2 class="chat-subject">Chat with <?= _e($conversation['user_name']) ?></h2>
<h3 class="chat-topic">Subject: <?= _e($conversation['subject']) ?></h3>

<div class="chat-container">
    <div class="chat-window" id="chat-window">
        <!-- Messages will be loaded here -->
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= ($msg['sender_type'] === 'admin') ? 'sent' : 'received' ?>">
                <div class="message-bubble">
                    <div class="message-sender">
                        <?= _e(($msg['sender_type'] === 'admin') ? 'You (Admin)' : $msg['user_name']) ?>
                    </div>
                    <div class="message-content">
                        <?= nl2br(_e($msg['message'])) ?>
                    </div>
                    <div class="message-time">
                        <?= date("M j, g:i a", strtotime($msg['created_at'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div id="chat-anchor"></div>
    </div>
    <form class="chat-form" action="chat_view.php?id=<?= $conversation_id ?>" method="POST">
        <textarea name="message" placeholder="Type your message..." rows="3" required></textarea>
        <button type="submit" class="btn">Send</button>
    </form>
</div>

<?php
// We can reuse the user-facing chat.css by linking to it
echo '<link rel="stylesheet" href="../chat.css">';
echo '<style>.chat-subject {font-size: 1.5rem; margin-top: 0;} .chat-topic {font-size: 1.1rem; color: #555; margin-bottom: 20px;}</style>';
?>

<script>
    // This script will be very similar to the user-facing one
    const chatWindow = document.getElementById('chat-window');
    const chatAnchor = document.getElementById('chat-anchor');
    let lastMessageId = <?= $messages[count($messages) - 1]['id'] ?? 0 ?>;

    chatAnchor.scrollIntoView();

    const fetchNewMessages = async () => {
        try {
            const response = await fetch(`api_admin_get_messages.php?conversation_id=<?= $conversation_id ?>&last_message_id=${lastMessageId}`);
            if (!response.ok) return;

            const newMessages = await response.json();
            if (newMessages.length > 0) {
                newMessages.forEach(msg => {
                    appendMessage(msg);
                    lastMessageId = msg.id;
                });
                chatAnchor.scrollIntoView({ behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    };

    const appendMessage = (msg) => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${msg.sender_type === 'admin' ? 'sent' : 'received'}`;

        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'message-bubble';

        const senderDiv = document.createElement('div');
        senderDiv.className = 'message-sender';
        senderDiv.innerText = msg.sender_type === 'admin' ? 'You (Admin)' : msg.user_name;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerText = msg.message;

        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.innerText = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        bubbleDiv.appendChild(senderDiv);
        bubbleDiv.appendChild(contentDiv);
        bubbleDiv.appendChild(timeDiv);
        messageDiv.appendChild(bubbleDiv);

        chatWindow.insertBefore(messageDiv, chatAnchor);
    };

    // Poll for new messages every 3 seconds
    setInterval(fetchNewMessages, 3000);
</script>

<?php require_once 'partials/footer.php'; ?>

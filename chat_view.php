<?php
require_once 'functions.php';

if (!is_user_logged_in()) {
    redirect('login.php');
}

$conversation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$conversation_id) {
    redirect('messages.php');
}

// get_conversation_by_id function also validates that the user has access to this conversation
$conversation = get_conversation_by_id($pdo, $conversation_id, $user_id);

if (!$conversation) {
    // If conversation doesn't exist or user doesn't have access
    redirect('messages.php');
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        send_chat_message($pdo, $conversation_id, $user_id, 'user', $message);
        // Redirect to the same page to show the new message and prevent form resubmission
        redirect("chat_view.php?id=$conversation_id");
    }
}

$messages = get_messages_for_conversation($pdo, $conversation_id);
$page_title = 'Viewing Conversation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="chat.css">
</head>
<body>
    <?php require_once 'partials/header.php'; ?>

    <main class="container">
        <a href="messages.php">&larr; Back to all messages</a>
        <h1 class="chat-subject"><?= _e($conversation['subject']) ?></h1>

        <div class="chat-container">
            <div class="chat-window" id="chat-window">
                <!-- Messages will be loaded here -->
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= ($msg['sender_type'] === 'user') ? 'sent' : 'received' ?>">
                        <div class="message-bubble">
                            <div class="message-sender">
                                <?= _e(($msg['sender_type'] === 'user') ? 'You' : 'Admin') ?>
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
    </main>

    <?php require_once 'partials/footer.php'; ?>

    <script>
        const chatWindow = document.getElementById('chat-window');
        const chatAnchor = document.getElementById('chat-anchor');
        let lastMessageId = <?= $messages[count($messages) - 1]['id'] ?? 0 ?>;

        // Scroll to the bottom of the chat window on page load
        chatAnchor.scrollIntoView();

        const fetchNewMessages = async () => {
            try {
                const response = await fetch(`api_get_messages.php?conversation_id=<?= $conversation_id ?>&last_message_id=${lastMessageId}`);
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
            messageDiv.className = `message ${msg.sender_type === 'user' ? 'sent' : 'received'}`;

            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = 'message-bubble';

            const senderDiv = document.createElement('div');
            senderDiv.className = 'message-sender';
            senderDiv.innerText = msg.sender_type === 'user' ? 'You' : 'Admin';

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerText = msg.message; // Using innerText to prevent XSS

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
</body>
</html>

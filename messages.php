<?php
require_once 'functions.php';

// Protect this page
if (!is_user_logged_in()) {
    redirect('login.php');
}

$page_title = 'My Messages';
$user_id = $_SESSION['user_id'];

// Fetch all conversations for the current user
$conversations = get_conversations_for_user($pdo, $user_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .conversations-list { list-style: none; padding: 0; }
        .conversations-list li { background: #fff; margin-bottom: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .conversations-list a { display: block; padding: 20px; color: #333; text-decoration: none; }
        .conversations-list a:hover { background: #f9f9f9; }
        .conversations-list .subject { font-weight: bold; font-size: 1.1rem; }
        .conversations-list .meta { font-size: 0.9rem; color: #777; margin-top: 5px; }
    </style>
</head>
<body>
    <?php require_once 'partials/header.php'; // I will create this partial later to avoid code duplication ?>

    <main class="container">
        <h1><?= _e($page_title) ?></h1>

        <div class="conversations-container">
            <?php if (empty($conversations)): ?>
                <p>You have no messages.</p>
            <?php else: ?>
                <ul class="conversations-list">
                    <?php foreach ($conversations as $convo): ?>
                        <li>
                            <a href="chat_view.php?id=<?= _e($convo['id']) ?>">
                                <div class="subject"><?= _e($convo['subject']) ?></div>
                                <div class="meta">Last updated: <?= date("F j, Y, g:i a", strtotime($convo['updated_at'])) ?></div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'partials/footer.php'; // I will create this partial later ?>
</body>
</html>

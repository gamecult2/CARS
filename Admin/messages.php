<?php
$page_title = 'Manage Conversations';
require_once 'partials/header.php';

// Fetch all conversations
$conversations = get_all_conversations($pdo);
?>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>User</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($conversations)): ?>
                <tr>
                    <td colspan="4" style="text-align:center;">There are no conversations.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($conversations as $convo): ?>
                    <tr>
                        <td><?= _e($convo['subject']) ?></td>
                        <td><?= _e($convo['user_name']) ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($convo['updated_at'])) ?></td>
                        <td class="actions">
                            <a href="chat_view.php?id=<?= $convo['id'] ?>" class="btn btn-sm">View Chat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
echo <<<HTML
<style>
.btn-sm { padding: 5px 10px; font-size: 0.8rem; }
.table-container { overflow-x: auto; }
</style>
HTML;

require_once 'partials/footer.php';
?>

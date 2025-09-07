<?php
$page_title = 'Manage Users';
require_once 'partials/header.php';

// --- Handle Delete Request ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($user_id_to_delete) {
        // The database schema is set up with ON DELETE CASCADE for messages,
        // so we only need to delete the user.
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id_to_delete]);
        redirect('manage_users.php?status=deleted');
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, name, email, registration_date FROM users ORDER BY registration_date DESC");
$users = $stmt->fetchAll();
?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div class="alert alert-success">User has been successfully deleted.</div>
<?php endif; ?>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registration Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No registered users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= _e($user['id']) ?></td>
                        <td><?= _e($user['name']) ?></td>
                        <td><?= _e($user['email']) ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($user['registration_date'])) ?></td>
                        <td class="actions">
                            <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This will also delete all their messages.');">Delete</a>
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
</style>
HTML;

require_once 'partials/footer.php';
?>

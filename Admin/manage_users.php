<?php
$page_title = 'Manage Users';
require_once 'partials/header.php';

// --- Handle Role Update ---
if (is_admin_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id_to_update = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $new_role = $_POST['role'];

    if ($user_id_to_update && ($new_role === 'user' || $new_role === 'moderator')) {
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([':role' => $new_role, ':id' => $user_id_to_update]);
        redirect('manage_users.php?status=role_updated');
    }
}

// --- Handle Delete Request ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    // You can't delete yourself. This check is for both admin and moderator.
    if ($user_id_to_delete && $user_id_to_delete != $session_user['id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id_to_delete]);
        redirect('manage_users.php?status=deleted');
    }
}

// Fetch all users (Admins don't appear in the users table)
$stmt = $pdo->query("SELECT id, name, email, role, registration_date FROM users ORDER BY registration_date DESC");
$users = $stmt->fetchAll();
?>

<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['status'] == 'deleted') echo 'User has been successfully deleted.';
            if ($_GET['status'] == 'updated') echo 'User has been successfully updated.';
            if ($_GET['status'] == 'role_updated') echo 'User role has been successfully updated.';
        ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registration Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No registered users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= _e($user['id']) ?></td>
                        <td><?= _e($user['name']) ?></td>
                        <td><?= _e($user['email']) ?></td>
                        <td>
                            <?php if (is_admin_logged_in()): ?>
                                <form action="manage_users.php" method="POST" class="role-form">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role" class="role-select">
                                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="moderator" <?= $user['role'] == 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                    </select>
                                    <button type="submit" name="update_role" class="btn btn-sm">Save</button>
                                </form>
                            <?php else: ?>
                                <?= _e(ucfirst($user['role'])) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date("F j, Y, g:i a", strtotime($user['registration_date'])) ?></td>
                        <td class="actions">
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm">Edit</a>
                            <?php // Prevent users from deleting themselves ?>
                            <?php if ($session_user['id'] != $user['id']): ?>
                                <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This will also delete all their messages.');">Delete</a>
                            <?php endif; ?>
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
.role-form { display: flex; align-items: center; gap: 10px; }
.role-form select { padding: 5px; }
</style>
HTML;

require_once 'partials/footer.php';
?>

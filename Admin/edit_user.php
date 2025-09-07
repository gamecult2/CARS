<?php
$page_title = 'Edit User';
require_once 'partials/header.php';

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) {
    redirect('manage_users.php');
}

// Fetch the user data
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('manage_users.php');
}

$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // --- Validation ---
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    // Check if email is already taken by another user
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'This email address is already in use by another account.';
        }
    }

    // --- Update Database ---
    if (empty($errors)) {
        try {
            $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
            $update_stmt = $pdo->prepare($sql);
            $update_stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':id' => $user_id
            ]);

            // To show the success message on the manage page after redirect
            redirect('manage_users.php?status=updated');
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= _e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-form" action="edit_user.php?id=<?= $user_id ?>" method="POST">
    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?= _e($user['name']) ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?= _e($user['email']) ?>" required>
    </div>
    <div class="form-group">
        <p><em>Changing user passwords is not supported from this interface.</em></p>
    </div>

    <button type="submit" class="btn">Update User</button>
    <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
// Add some specific styles for alerts
echo <<<HTML
<style>
.alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
.alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
.alert-danger ul { margin: 0; padding-left: 20px; }
</style>
HTML;

require_once 'partials/footer.php';
?>

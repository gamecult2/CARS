<?php
require_once '../functions.php';

$errors = [];

// --- Handle Login Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('index.php');
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}

// --- Handle Logout Request ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    session_destroy();
    redirect('index.php');
}

$is_logged_in = is_admin_logged_in();

// If not logged in, show the login form.
if (!$is_logged_in) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" action="index.php" method="POST">
            <h2>Admin Login</h2>
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?><p><?= _e($error) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
            <p class="back-to-site"><a href="../index.php">← Back to Main Site</a></p>
        </form>
    </div>
</body>
</html>
<?php
    exit(); // Stop script execution for the login page
}

// --- If we reach here, the admin is logged in. Show the dashboard. ---
$page_title = 'Dashboard';
require_once 'partials/header.php';
?>

<h3>Dashboard Overview</h3>
<p>Welcome to the admin panel. Here you can manage car listings, users, and inquiries.</p>
<p>Select an option from the navigation menu on the left to get started.</p>

<!-- Example Stats (can be made dynamic) -->
<div class="dashboard-stats">
    <div class="stat-card">
        <h4>Total Cars</h4>
        <p>15</p>
    </div>
    <div class="stat-card">
        <h4>Total Users</h4>
        <p>8</p>
    </div>
    <div class="stat-card">
        <h4>New Inquiries</h4>
        <p>4</p>
    </div>
</div>


<?php
require_once 'partials/footer.php';
?>

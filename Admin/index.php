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

// Fetch stats for the dashboard
$total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_conversations = $pdo->query("SELECT COUNT(*) FROM conversations")->fetchColumn();


require_once 'partials/header.php';
?>

<h3>Dashboard Overview</h3>
<p>Welcome to the admin panel. Here you can manage car listings, users, and conversations.</p>
<p>Select an option from the navigation menu on the left to get started.</p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h4>Total Cars</h4>
        <p><?= $total_cars ?></p>
    </div>
    <div class="stat-card">
        <h4>Total Users</h4>
        <p><?= $total_users ?></p>
    </div>
    <div class="stat-card">
        <h4>Total Conversations</h4>
        <p><?= $total_conversations ?></p>
    </div>
</div>

<style>
.dashboard-stats { display: flex; gap: 20px; margin-top: 30px; }
.stat-card { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
.stat-card h4 { margin-top: 0; font-size: 1rem; color: #4a5568; }
.stat-card p { font-size: 2.5rem; font-weight: bold; color: #2c5282; margin-bottom: 0; }
</style>


<?php
require_once 'partials/footer.php';
?>

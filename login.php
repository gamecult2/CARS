<?php
require_once 'functions.php';

// If user is already logged in, redirect to profile page
if (is_user_logged_in()) {
    redirect('profile.php');
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Validation ---
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // --- If no validation errors, try to log in ---
    if (empty($errors)) {
        // Fetch user from the database
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, so start a new session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email; // Optional: store email for easy access

            // Redirect to a protected page
            redirect('profile.php');
        } else {
            // Bad credentials
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Car Dealership</a></h1>
            <nav>
                <ul>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="index.php">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container auth-container">
        <div class="auth-form">
            <h2>Login to Your Account</h2>

            <?php if (!empty($errors)): ?>
                <div class="form-errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?= _e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p class="auth-switch">Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

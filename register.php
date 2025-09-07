<?php
require_once 'functions.php';

$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // --- Validation ---
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    // --- If no errors, create user ---
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed_password,
            ]);

            // Set success message and clear form data
            $success_message = 'Registration successful! You can now <a href="login.php">log in</a>.';
            $_POST = []; // Clear post data to prevent re-submission
        } catch (PDOException $e) {
            // In a real app, log this error. For now, show a generic message.
            $errors[] = 'An error occurred during registration. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css"> <!-- Specific styles for auth forms -->
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Car Dealership</a></h1>
            <nav>
                <ul>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="index.php">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container auth-container">
        <div class="auth-form">
            <h2>Create Your Account</h2>

            <?php if (!empty($errors)): ?>
                <div class="form-errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?= _e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="form-success">
                    <p><?= $success_message ?></p>
                </div>
            <?php else: ?>
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= _e($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= _e($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <p class="auth-switch">Already have an account? <a href="login.php">Log in here</a>.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

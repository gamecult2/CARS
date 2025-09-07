<?php
require_once 'functions.php';

// Protect this page - redirect if user is not logged in
if (!is_user_logged_in()) {
    redirect('login.php');
}

// Fetch user data from the database
$stmt = $pdo->prepare("SELECT name, email, registration_date FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// If for some reason the user is not found in the DB, log them out.
if (!$user) {
    redirect('logout.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Car Dealership</a></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="profile-page">
            <h2>My Profile</h2>
            <div class="profile-details">
                <p><strong>Name:</strong> <?= _e($user['name']) ?></p>
                <p><strong>Email:</strong> <?= _e($user['email']) ?></p>
                <p><strong>Member Since:</strong> <?= date("F j, Y", strtotime($user['registration_date'])) ?></p>
            </div>

            <h3>My Inquiries</h3>
            <p>This is where your messages to sellers would appear.</p>
            <!-- Inquiry listing can be added here in the future -->
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Car Dealership. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

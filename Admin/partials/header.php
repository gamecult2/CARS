<?php
// We are in the /Admin directory, so functions.php is one level up.
require_once __DIR__ . '/../../functions.php';

// --- Admin Authentication Check ---
// All pages in the admin section, except for the login page itself, should require the user to be logged in.
// We can get the script name to conditionally apply this check.
$current_page = basename($_SERVER['PHP_SELF']);

if (!is_admin_logged_in() && $current_page !== 'index.php') {
    // If not logged in and not on the login page, redirect to login
    redirect('index.php');
}

// Check if the user is trying to access the login page while already logged in
if (is_admin_logged_in() && $current_page === 'index.php' && !isset($_GET['action'])) {
    // The main index.php handles both login and dashboard.
    // This logic is mostly for other potential auth pages.
    // We'll let index.php's internal logic handle the view.
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be set on each page -->
    <title><?= isset($page_title) ? _e($page_title) : 'Admin Panel' ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <h1>Admin Panel</h1>
            <div class="header-right">
                <span>Welcome, <strong><?= _e($_SESSION['admin_username'] ?? '') ?></strong></span>
                <a href="index.php?action=logout" class="btn btn-secondary">Logout</a>
            </div>
        </header>
        <div class="admin-main">
            <?php require_once 'navigation.php'; ?>
            <section class="admin-content">
                <!-- Page-specific content starts here -->

                <!-- We will set the h2 title on each page -->
                <?php if (isset($page_title)): ?>
                    <h2><?= _e($page_title) ?></h2>
                <?php endif; ?>

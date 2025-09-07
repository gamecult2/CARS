<?php
// We are in the /Admin directory, so functions.php is one level up.
require_once __DIR__ . '/../../functions.php';

// --- Admin Authentication Check ---
if (!is_admin_or_moderator()) {
    redirect('index.php');
}

$session_user = get_session_user();

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
                <span>Welcome, <strong><?= _e($session_user['name']) ?></strong> (<?= _e($session_user['role']) ?>)</span>
                <a href="index.php?action=logout" class="btn btn-secondary">Logout</a>
            </div>
        </header>
        <div class="admin-main">
            <?php require_once __DIR__ . '/navigation.php'; ?>
            <section class="admin-content">
                <!-- Page-specific content starts here -->

                <!-- We will set the h2 title on each page -->
                <?php if (isset($page_title)): ?>
                    <h2><?= _e($page_title) ?></h2>
                <?php endif; ?>

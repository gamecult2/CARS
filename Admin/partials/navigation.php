<?php
// Determine the active page to highlight it in the navigation
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="admin-nav">
    <ul>
        <li class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">
            <a href="index.php">Dashboard</a>
        </li>
        <li class="<?= ($current_page === 'add_car.php' || $current_page === 'edit_car.php') ? 'active' : '' ?>">
            <a href="add_car.php">Add New Car</a>
        </li>
        <li class="<?= ($current_page === 'manage_cars.php') ? 'active' : '' ?>">
            <a href="manage_cars.php">Manage Cars</a>
        </li>
        <li class="<?= ($current_page === 'manage_users.php') ? 'active' : '' ?>">
            <a href="manage_users.php">Manage Users</a>
        </li>
        <li class="<?= ($current_page === 'messages.php') ? 'active' : '' ?>">
            <a href="messages.php">View Inquiries</a>
        </li>
    </ul>
</nav>

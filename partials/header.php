<header>
    <div class="container">
        <h1><a href="index.php">Car Dealership</a></h1>
        <nav>
            <ul>
                <?php if (is_user_logged_in()): ?>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="messages.php">My Messages</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
                <li><a href="/Admin/index.php">Admin</a></li>
            </ul>
        </nav>
    </div>
</header>

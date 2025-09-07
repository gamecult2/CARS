<?php
// A simple, single-file installer for the Car Dealership website.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Security Check ---
// If the config file already exists, it means the site is already installed.
// Die with a message to prevent accidental re-installation.
if (file_exists('config.php')) {
    header('Content-Type: text/html; charset=utf-8');
    die("<h1>Already Installed</h1><p>The website appears to be already installed because the <code>config.php</code> file already exists.</p><p><strong>To reinstall, you must first delete the <code>config.php</code> file.</strong></p>");
}

$errors = [];
$success = false;

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Get and Validate Inputs ---
    $db_host = trim($_POST['db_host']);
    $db_name = trim($_POST['db_name']);
    $db_user = trim($_POST['db_user']);
    $db_pass = $_POST['db_pass'];

    $admin_user = trim($_POST['admin_user']);
    $admin_pass = $_POST['admin_pass'];
    $admin_pass_confirm = $_POST['admin_pass_confirm'];

    if (empty($db_host)) $errors[] = 'Database host is required.';
    if (empty($db_name)) $errors[] = 'Database name is required.';
    if (empty($db_user)) $errors[] = 'Database user is required.';
    if (empty($admin_user)) $errors[] = 'Admin username is required.';
    if (empty($admin_pass)) $errors[] = 'Admin password is required.';
    if ($admin_pass !== $admin_pass_confirm) $errors[] = 'Admin passwords do not match.';

    if (empty($errors)) {
        try {
            // 1. Connect to MySQL Server
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 2. Create Database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            $pdo->exec("USE `$db_name`");

            // 3. Import SQL file
            $sql_script = file_get_contents('database.sql');
            if ($sql_script === false) {
                throw new Exception("Could not read database.sql file.");
            }
            $pdo->exec($sql_script);

            // 4. Update Admin Credentials
            $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE `admin` SET `username` = :username, `password` = :password WHERE `id` = 1");
            $stmt->execute([':username' => $admin_user, ':password' => $hashed_password]);

            // 5. Create config.php file
            $config_content = "<?php\n"
                            . "define('DB_HOST', '" . addslashes($db_host) . "');\n"
                            . "define('DB_NAME', '" . addslashes($db_name) . "');\n"
                            . "define('DB_USER', '" . addslashes($db_user) . "');\n"
                            . "define('DB_PASS', '" . addslashes($db_pass) . "');\n"
                            . "define('DB_CHARSET', 'utf8mb4');\n\n"
                            . "\$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;\n"
                            . "\$options = [\n"
                            . "    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n"
                            . "    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
                            . "    PDO::ATTR_EMULATE_PREPARES   => false,\n"
                            . "];\n"
                            . "try {\n"
                            . "    \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);\n"
                            . "} catch (\\PDOException \$e) {\n"
                            . "    throw new \\PDOException(\$e->getMessage(), (int)\$e->getCode());\n"
                            . "}\n";

            file_put_contents('config.php', $config_content);

            $success = true;

        } catch (Exception $e) {
            $errors[] = 'Installation failed: ' . $e->getMessage();
        }
    }
} else {
    // Default values for the form
    $db_host = '127.0.0.1';
    $db_name = 'car_dealership';
    $db_user = 'root';
    $admin_user = 'admin';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Dealership - Website Installation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { display: block; width: 100%; padding: 12px; border: none; border-radius: 4px; background-color: #007bff; color: white; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-danger ul { margin: 0; padding-left: 20px; }
        .info { background-color: #e2e3e5; color: #383d41; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Website Installation</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <h2>Installation Complete!</h2>
                <p>The website has been installed successfully.</p>
                <p>Your admin username is: <strong><?= htmlspecialchars($admin_user) ?></strong></p>
            </div>
            <div class="alert alert-danger">
                <h3>Security Warning!</h3>
                <p>For your security, please <strong>delete the <code>install.php</code> file</strong> from your server immediately.</p>
            </div>
            <div class="links">
                <a href="index.php" class="btn">Go to Homepage</a>
                <a href="Admin/index.php" class="btn btn-secondary">Go to Admin Login</a>
            </div>
            <style>.links{margin-top:20px; display: flex; gap: 15px;} .btn-secondary{background-color: #6c757d;}</style>

        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>The following errors occurred:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="info">
                <p>Welcome! This installer will guide you through setting up the website. Please fill in the details below.</p>
                <p>You must have a running MySQL server.</p>
            </div>

            <form action="install.php" method="POST">
                <fieldset>
                    <legend><h3>Database Settings</h3></legend>
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($db_host) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($db_name) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="db_user">Database User</label>
                        <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($db_user) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass">
                    </div>
                </fieldset>

                <fieldset>
                    <legend><h3>Admin Account Setup</h3></legend>
                    <div class="form-group">
                        <label for="admin_user">Admin Username</label>
                        <input type="text" id="admin_user" name="admin_user" value="<?= htmlspecialchars($admin_user) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_pass">Admin Password</label>
                        <input type="password" id="admin_pass" name="admin_pass" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_pass_confirm">Confirm Admin Password</label>
                        <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" required>
                    </div>
                </fieldset>

                <button type="submit" class="btn">Install Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

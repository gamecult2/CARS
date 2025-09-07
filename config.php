<?php
/*
 * Database Configuration
 *
 * This file defines the database connection constants and establishes a PDO connection.
 */

// --- Database Credentials ---
// Replace with your actual database credentials.
define('DB_HOST', '127.0.0.1');       // The hostname of your database server.
define('DB_NAME', 'car_dealership'); // The name of the database.
define('DB_USER', 'root');           // The username for database access.
define('DB_PASS', '');               // The password for the database user.
define('DB_CHARSET', 'utf8mb4');     // The character set for the database connection.

// --- PDO Connection ---
// Establishes a connection to the database using PDO.
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // In a real application, you would log this error and show a user-friendly message.
    // For this project, we'll just kill the script and show the error.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// The $pdo object can now be used throughout the application to interact with the database.
?>

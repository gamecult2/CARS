<?php
/*
 * Common Functions
 *
 * This file contains common PHP functions used throughout the website.
 */

// Start the session if it's not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Includes the database configuration.
 * This ensures that the $pdo object is available.
 */
require_once 'config.php';

/**
 * Escapes a string for safe HTML output.
 *
 * @param string|null $string The string to escape.
 * @return string The escaped string.
 */
function _e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirects the user to a specified URL.
 *
 * @param string $url The URL to redirect to.
 * @return void
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

/**
 * Checks if a user is logged in.
 *
 * @return bool True if the user is logged in, false otherwise.
 */
function is_user_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if an admin is logged in.
 *
 * @return bool True if the admin is logged in, false otherwise.
 */
function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

/**
 * Fetches all cars from the database with optional filters.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param array $filters An associative array of filters (e.g., ['brand' => 'Toyota']).
 * @return array An array of car records.
 */
function get_cars(PDO $pdo, array $filters = []): array {
    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];

    if (!empty($filters['brand'])) {
        $sql .= " AND brand LIKE :brand";
        $params[':brand'] = '%' . $filters['brand'] . '%';
    }
    if (!empty($filters['model'])) {
        $sql .= " AND model LIKE :model";
        $params[':model'] = '%' . $filters['model'] . '%';
    }
    if (!empty($filters['year'])) {
        $sql .= " AND year = :year";
        $params[':year'] = $filters['year'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND price <= :max_price";
        $params[':max_price'] = $filters['max_price'];
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Fetches a single car by its ID.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param int $id The ID of the car to fetch.
 * @return array|null The car record as an array, or null if not found.
 */
function get_car_by_id(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $car = $stmt->fetch();
    return $car ?: null;
}
?>

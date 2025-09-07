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
/**
 * Gets the current session's user data and role.
 *
 * @return array|null An array with user data and role, or null if not logged in.
 */
function get_session_user(): ?array {
    if (isset($_SESSION['user_id'])) {
        return ['id' => $_SESSION['user_id'], 'role' => $_SESSION['user_role'] ?? 'user', 'name' => $_SESSION['user_name'] ?? ''];
    }
    if (isset($_SESSION['admin_id'])) {
        return ['id' => $_SESSION['admin_id'], 'role' => 'admin', 'name' => $_SESSION['admin_username'] ?? ''];
    }
    return null;
}

/**
 * Checks if a regular user is logged in.
 *
 * @return bool
 */
function is_user_logged_in(): bool {
    $user = get_session_user();
    return $user && $user['role'] === 'user';
}

/**
 * Checks if an admin is logged in.
 *
 * @return bool
 */
function is_admin_logged_in(): bool {
    $user = get_session_user();
    return $user && $user['role'] === 'admin';
}

/**
 * Checks if a moderator is logged in.
 *
 * @return bool
 */
function is_moderator_logged_in(): bool {
    $user = get_session_user();
    return $user && $user['role'] === 'moderator';
}

/**
 * Checks if an admin or a moderator is logged in.
 *
 * @return bool
 */
function is_admin_or_moderator(): bool {
    $user = get_session_user();
    return $user && ($user['role'] === 'admin' || $user['role'] === 'moderator');
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

// --- Chat System Functions ---

/**
 * Creates a new conversation or finds an existing one for a specific car and user.
 *
 * @param PDO $pdo
 * @param integer $user_id
 * @param integer $car_id
 * @return integer The ID of the conversation.
 */
function get_or_create_conversation_for_car(PDO $pdo, int $user_id, int $car_id): int {
    // Check if a conversation already exists for this user and car
    $stmt = $pdo->prepare("SELECT id FROM conversations WHERE user_id = :user_id AND car_id = :car_id");
    $stmt->execute([':user_id' => $user_id, ':car_id' => $car_id]);
    $conversation = $stmt->fetch();

    if ($conversation) {
        return (int)$conversation['id'];
    } else {
        // Fetch car details to create a subject
        $car = get_car_by_id($pdo, $car_id);
        $subject = "Inquiry about " . $car['year'] . " " . $car['brand'] . " " . $car['model'];

        $insert_stmt = $pdo->prepare("INSERT INTO conversations (user_id, car_id, subject) VALUES (:user_id, :car_id, :subject)");
        $insert_stmt->execute([
            ':user_id' => $user_id,
            ':car_id' => $car_id,
            ':subject' => $subject
        ]);
        return (int)$pdo->lastInsertId();
    }
}

/**
 * Sends a chat message.
 *
 * @param PDO $pdo
 * @param integer $conversation_id
 * @param integer $sender_id
 * @param string $sender_type
 * @param string $message
 * @return boolean
 */
function send_chat_message(PDO $pdo, int $conversation_id, int $sender_id, string $sender_type, string $message): bool {
    $stmt = $pdo->prepare(
        "INSERT INTO chat_messages (conversation_id, sender_id, sender_type, message) VALUES (:conv_id, :sender_id, :sender_type, :message)"
    );
    return $stmt->execute([
        ':conv_id' => $conversation_id,
        ':sender_id' => $sender_id,
        ':sender_type' => $sender_type,
        ':message' => $message,
    ]);
}

/**
 * Gets all messages for a given conversation.
 *
 * @param PDO $pdo
 * @param integer $conversation_id
 * @return array
 */
function get_messages_for_conversation(PDO $pdo, int $conversation_id): array {
    $stmt = $pdo->prepare("SELECT cm.*, u.name as user_name, a.username as admin_name
                           FROM chat_messages cm
                           LEFT JOIN users u ON cm.sender_id = u.id AND cm.sender_type = 'user'
                           LEFT JOIN admin a ON cm.sender_id = a.id AND cm.sender_type = 'admin'
                           WHERE cm.conversation_id = :conv_id
                           ORDER BY cm.created_at ASC");
    $stmt->execute([':conv_id' => $conversation_id]);
    return $stmt->fetchAll();
}

/**
 * Gets all conversations for a specific user.
 *
 * @param PDO $pdo
 * @param integer $user_id
 * @return array
 */
function get_conversations_for_user(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("SELECT * FROM conversations WHERE user_id = :user_id ORDER BY updated_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll();
}

/**
 * Gets all conversations for the admin view.
 *
 * @param PDO $pdo
 * @return array
 */
function get_all_conversations(PDO $pdo): array {
    $stmt = $pdo->prepare("SELECT c.*, u.name as user_name
                           FROM conversations c
                           JOIN users u ON c.user_id = u.id
                           ORDER BY c.updated_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Gets a single conversation by its ID and validates access.
 *
 * @param PDO $pdo
 * @param integer $conversation_id
 * @param integer|null $user_id
 * @param boolean $is_admin
 * @return array|null
 */
function get_conversation_by_id(PDO $pdo, int $conversation_id, ?int $user_id, bool $is_admin = false): ?array {
    $stmt = $pdo->prepare("SELECT c.*, u.name as user_name
                           FROM conversations c
                           JOIN users u ON c.user_id = u.id
                           WHERE c.id = :conv_id");
    $stmt->execute([':conv_id' => $conversation_id]);
    $conversation = $stmt->fetch();

    if (!$conversation) {
        return null;
    }

    // If not an admin, check if the user is part of this conversation
    if (!$is_admin && $conversation['user_id'] != $user_id) {
        return null;
    }

    return $conversation;
}

/**
 * Gets all distinct car brands and the count of cars for each.
 *
 * @param PDO $pdo
 * @return array
 */
function get_brands_with_count(PDO $pdo): array {
    $stmt = $pdo->query("SELECT brand, COUNT(*) as car_count FROM cars GROUP BY brand ORDER BY brand ASC");
    return $stmt->fetchAll();
}
?>

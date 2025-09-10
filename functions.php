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
    $current_year = date('Y');
    $sql = "SELECT c.*, COALESCE(r.avg_rating, 0) as avg_rating, COALESCE(r.review_count, 0) as review_count
            FROM cars c
            LEFT JOIN (
                SELECT car_id, AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM reviews
                GROUP BY car_id
            ) r ON c.id = r.car_id
            WHERE c.year >= :min_year";
    $params = [':min_year' => $current_year - 3];

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
    if (!empty($filters['body_type'])) {
        $sql .= " AND body_type = :body_type";
        $params[':body_type'] = $filters['body_type'];
    }
    if (!empty($filters['transmission'])) {
        $sql .= " AND transmission = :transmission";
        $params[':transmission'] = $filters['transmission'];
    }
    if (!empty($filters['fuel_type'])) {
        $sql .= " AND fuel_type = :fuel_type";
        $params[':fuel_type'] = $filters['fuel_type'];
    }
    if (!empty($filters['steering'])) {
        $sql .= " AND steering = :steering";
        $params[':steering'] = $filters['steering'];
    }
    if (!empty($filters['color_hex'])) {
        $sql .= " AND color_hex = :color_hex";
        $params[':color_hex'] = $filters['color_hex'];
    }
    if (!empty($filters['accessories']) && is_array($filters['accessories'])) {
        foreach ($filters['accessories'] as $index => $accessory) {
            $key = ":acc" . $index;
            $sql .= " AND accessories LIKE " . $key;
            $params[$key] = '%' . $accessory . '%';
        }
    }

    // Handle the new condition filter
    if (!empty($filters['condition'])) {
        if ($filters['condition'] === 'new') {
            $sql .= " AND year = :current_year";
            $params[':current_year'] = $current_year;
        } elseif ($filters['condition'] === 'used') {
            $sql .= " AND year < :current_year";
            $params[':current_year'] = $current_year;
        }
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

/**
 * Gets all brands, their logos, and the count of cars for each brand.
 *
 * @param PDO $pdo The PDO database connection object.
 * @return array An array of brand records, grouped by the first letter of the brand name.
 */
function get_all_brands_with_counts(PDO $pdo): array {
    $sql = "
        SELECT
            b.name,
            b.logo_url,
            COUNT(c.id) as car_count
        FROM
            brands b
        LEFT JOIN
            cars c ON b.name = c.brand
        GROUP BY
            b.id, b.name, b.logo_url
        ORDER BY
            b.name ASC
    ";
    $stmt = $pdo->query($sql);
    $all_brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by first letter
    $grouped_brands = [];
    foreach ($all_brands as $brand) {
        $first_letter = strtoupper(substr($brand['name'], 0, 1));
        if (is_numeric($first_letter)) {
            $first_letter = '#';
        }
        $grouped_brands[$first_letter][] = $brand;
    }
    ksort($grouped_brands);
    return $grouped_brands;
}

/**
 * Gets all featured brands and their car counts.
 *
 * @param PDO $pdo The PDO database connection object.
 * @return array An array of featured brand records.
 */
function get_featured_brands(PDO $pdo): array {
    $sql = "
        SELECT
            b.name,
            b.logo_url,
            COUNT(c.id) as car_count
        FROM
            brands b
        LEFT JOIN
            cars c ON b.name = c.brand
        WHERE
            b.is_featured = 1
        GROUP BY
            b.id, b.name, b.logo_url
        ORDER BY
            b.name ASC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// --- Order Management Functions ---

/**
 * Creates a new order and logs the initial history event.
 */
function create_order(PDO $pdo, int $user_id, int $car_id): int {
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, car_id) VALUES (:user_id, :car_id)");
    $stmt->execute([':user_id' => $user_id, ':car_id' => $car_id]);
    $order_id = (int)$pdo->lastInsertId();

    add_order_history($pdo, $order_id, 'Order Placed', 'User placed a new order.', $user_id, 'user');

    // Notify Admins/Mods - for simplicity, we'll notify the admin with ID 1
    create_notification($pdo, 1, "New order #{$order_id} has been placed.", "Admin/view_order.php?id={$order_id}");

    return $order_id;
}

/**
 * Adds a new entry to an order's history log.
 */
function add_order_history(PDO $pdo, int $order_id, string $action, string $description, int $actor_id, string $actor_type): bool {
    $stmt = $pdo->prepare(
        "INSERT INTO order_history (order_id, action, description, actor_id, actor_type) VALUES (:order_id, :action, :description, :actor_id, :actor_type)"
    );
    return $stmt->execute([
        ':order_id' => $order_id,
        ':action' => $action,
        ':description' => $description,
        ':actor_id' => $actor_id,
        ':actor_type' => $actor_type
    ]);
}

/**
 * Fetches a single order with all its related data (history, messages, files).
 */
function get_order_details(PDO $pdo, int $order_id): ?array {
    $order = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email, c.brand, c.model, c.year FROM orders o JOIN users u ON o.user_id = u.id JOIN cars c ON o.car_id = c.id WHERE o.id = :id");
    $order->execute([':id' => $order_id]);
    $result = $order->fetch();

    if (!$result) return null;

    $history = $pdo->prepare("SELECT * FROM order_history WHERE order_id = :id ORDER BY created_at ASC");
    $history->execute([':id' => $order_id]);
    $result['history'] = $history->fetchAll();

    $messages = $pdo->prepare("SELECT * FROM order_messages WHERE order_id = :id ORDER BY created_at ASC");
    $messages->execute([':id' => $order_id]);
    $result['messages'] = $messages->fetchAll();

    $files = $pdo->prepare("SELECT * FROM order_files WHERE order_id = :id ORDER BY created_at DESC");
    $files->execute([':id' => $order_id]);
    $result['files'] = $files->fetchAll();

    return $result;
}

/**
 * Adds a message to a specific order.
 */
function add_order_message(PDO $pdo, int $order_id, int $sender_id, string $sender_type, string $message): bool {
    $stmt = $pdo->prepare("INSERT INTO order_messages (order_id, sender_id, sender_type, message) VALUES (:order_id, :sender_id, :sender_type, :message)");
    return $stmt->execute([
        ':order_id' => $order_id,
        ':sender_id' => $sender_id,
        ':sender_type' => $sender_type,
        ':message' => $message,
    ]);
}

/**
 * Adds a file record to a specific order.
 */
function add_order_file(PDO $pdo, int $order_id, int $uploader_id, string $uploader_type, string $file_name, string $file_path): bool {
    $stmt = $pdo->prepare("INSERT INTO order_files (order_id, uploader_id, uploader_type, file_name, file_path) VALUES (:order_id, :uploader_id, :uploader_type, :file_name, :file_path)");
    return $stmt->execute([
        ':order_id' => $order_id,
        ':uploader_id' => $uploader_id,
        ':uploader_type' => $uploader_type,
        ':file_name' => $file_name,
        ':file_path' => $file_path,
    ]);
}

/**
 * Fetches all orders for a specific user.
 */
function get_orders_for_user(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("SELECT o.*, c.brand, c.model, c.year FROM orders o JOIN cars c ON o.car_id = c.id WHERE o.user_id = :user_id ORDER BY o.updated_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll();
}

/**
 * Fetches all orders for the admin panel.
 */
function get_all_orders(PDO $pdo, string $status_filter = ''): array {
    $sql = "SELECT o.*, u.name as user_name, c.brand, c.model FROM orders o JOIN users u ON o.user_id = u.id JOIN cars c ON o.car_id = c.id";
    if (!empty($status_filter)) {
        $sql .= " WHERE o.order_status = :status";
    }
    $sql .= " ORDER BY o.updated_at DESC";
    $stmt = $pdo->prepare($sql);
    if (!empty($status_filter)) {
        $stmt->execute([':status' => $status_filter]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

/**
 * Creates an in-app notification for a user.
 */
function create_notification(PDO $pdo, int $user_id, string $message, string $link): bool {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:user_id, :message, :link)");
    return $stmt->execute([':user_id' => $user_id, ':message' => $message, ':link' => $link]);
}

/**
 * Placeholder function for sending emails.
 */
function send_order_email(string $to, string $subject, string $message): bool {
    // In a real application, this would use a library like PHPMailer
    // and connect to an SMTP server. For now, it's a placeholder.
    // mail($to, $subject, $message);
    error_log("Email supposed to be sent to {$to} with subject '{$subject}'");
    return true;
}

// --- Notification Functions ---

/**
 * Gets the count of unread notifications for a user.
 */
function get_unread_notification_count(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->execute([':user_id' => $user_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Gets all notifications for a user.
 */
function get_notifications_for_user(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll();
}

/**
 * Marks all unread notifications for a user as read.
 */
function mark_notifications_as_read(PDO $pdo, int $user_id): bool {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
    return $stmt->execute([':user_id' => $user_id]);
}

// --- Blog & Promotion Functions ---

function get_all_blog_posts(PDO $pdo): array {
    return $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
}

function get_blog_post(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
    return $post ?: null;
}

function delete_entity(PDO $pdo, string $table, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

function get_all_promotions(PDO $pdo, bool $only_active = false): array {
    $sql = "SELECT p.*, c.brand, c.model FROM promotions p LEFT JOIN cars c ON p.car_id = c.id";
    if ($only_active) {
        $sql .= " WHERE p.end_date >= NOW()";
    }
    $sql .= " ORDER BY p.end_date DESC";
    return $pdo->query($sql)->fetchAll();
}

function get_promotion(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $promo = $stmt->fetch();
    return $promo ?: null;
}

// --- Wishlist Functions ---

function is_car_in_wishlist(PDO $pdo, int $user_id, int $car_id): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = :user_id AND car_id = :car_id");
    $stmt->execute([':user_id' => $user_id, ':car_id' => $car_id]);
    return (int)$stmt->fetchColumn() > 0;
}

function get_wishlist_count(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    return (int)$stmt->fetchColumn();
}

function get_wishlist_for_user(PDO $pdo, int $user_id): array {
    $sql = "
        SELECT c.*, COALESCE(r.avg_rating, 0) as avg_rating, COALESCE(r.review_count, 0) as review_count
        FROM cars c
        JOIN wishlist w ON c.id = w.car_id
        LEFT JOIN (
            SELECT car_id, AVG(rating) as avg_rating, COUNT(*) as review_count
            FROM reviews
            GROUP BY car_id
        ) r ON c.id = r.car_id
        WHERE w.user_id = :user_id
        ORDER BY w.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll();
}

// --- Review & Rating Functions ---

function submit_review(PDO $pdo, int $car_id, int $user_id, int $rating, string $comment): bool {
    // For simplicity, we allow one review per user per car.
    // A real app might check if a user has already reviewed.
    $stmt = $pdo->prepare("INSERT INTO reviews (car_id, user_id, rating, comment) VALUES (:car_id, :user_id, :rating, :comment)");
    return $stmt->execute([
        ':car_id' => $car_id,
        ':user_id' => $user_id,
        ':rating' => $rating,
        ':comment' => $comment,
    ]);
}

function get_reviews_for_car(PDO $pdo, int $car_id): array {
    $stmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.car_id = :car_id ORDER BY r.created_at DESC");
    $stmt->execute([':car_id' => $car_id]);
    return $stmt->fetchAll();
}

function get_average_rating_for_car(PDO $pdo, int $car_id): array {
    $stmt = $pdo->prepare("SELECT COUNT(*) as review_count, AVG(rating) as avg_rating FROM reviews WHERE car_id = :car_id");
    $stmt->execute([':car_id' => $car_id]);
    $result = $stmt->fetch();
    return [
        'count' => (int)($result['review_count'] ?? 0),
        'average' => (float)($result['avg_rating'] ?? 0),
    ];
}

/**
 * Gets distinct values for car attributes to populate search filters.
 *
 * @param PDO $pdo The PDO database connection object.
 * @return array An array containing arrays of distinct values for each attribute.
 */
function get_distinct_car_attributes(PDO $pdo): array {
    $attributes = ['body_type', 'transmission', 'fuel_type', 'steering'];
    $distinct_values = [];

    foreach ($attributes as $attribute) {
        $stmt = $pdo->query("SELECT DISTINCT $attribute FROM cars WHERE $attribute IS NOT NULL AND $attribute != '' ORDER BY $attribute ASC");
        $distinct_values[$attribute] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    return $distinct_values;
}

/**
 * Gets distinct colors and all unique accessories for advanced search filters.
 *
 * @param PDO $pdo The PDO database connection object.
 * @return array An array containing 'colors' and 'accessories'.
 */
function get_advanced_filter_options(PDO $pdo): array {
    $options = [];

    // Get distinct colors
    $stmt_colors = $pdo->query("SELECT DISTINCT color_hex, exterior_color FROM cars WHERE color_hex IS NOT NULL AND color_hex != ''");
    $options['colors'] = $stmt_colors->fetchAll(PDO::FETCH_ASSOC);

    // Get all accessories, then find unique values
    $stmt_acc = $pdo->query("SELECT accessories FROM cars WHERE accessories IS NOT NULL AND accessories != ''");
    $all_accessories_raw = $stmt_acc->fetchAll(PDO::FETCH_COLUMN, 0);

    $all_accessories = [];
    foreach ($all_accessories_raw as $accessory_list) {
        $accessories = explode(',', $accessory_list);
        foreach ($accessories as $accessory) {
            $trimmed_acc = trim($accessory);
            if (!empty($trimmed_acc)) {
                $all_accessories[$trimmed_acc] = true; // Use a map to handle uniqueness
            }
        }
    }
    $unique_accessories = array_keys($all_accessories);
    sort($unique_accessories);
    $options['accessories'] = $unique_accessories;

    return $options;
}
?>

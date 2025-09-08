<?php
header('Content-Type: application/json');
require_once 'functions.php';

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$session_user = get_session_user();
$user_id = $session_user['id'];
$car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);

if (!$car_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid car ID']);
    exit;
}

try {
    $in_wishlist = is_car_in_wishlist($pdo, $user_id, $car_id);

    if ($in_wishlist) {
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND car_id = :car_id");
        $stmt->execute([':user_id' => $user_id, ':car_id' => $car_id]);
        $new_status = false;
    } else {
        // Add to wishlist
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, car_id) VALUES (:user_id, :car_id)");
        $stmt->execute([':user_id' => $user_id, ':car_id' => $car_id]);
        $new_status = true;
    }

    $wishlist_count = get_wishlist_count($pdo, $user_id);
    echo json_encode(['success' => true, 'in_wishlist' => $new_status, 'count' => $wishlist_count]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>

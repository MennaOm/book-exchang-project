<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php'; 

$user_id = require_login(); 

function get_unread_notifications($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    return $notifications;
}

$notifications = get_unread_notifications($user_id);

// Output the notifications as JSON
echo json_encode($notifications);
?>

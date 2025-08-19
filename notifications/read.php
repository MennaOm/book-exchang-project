<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php'; 
$user_id = require_login();

$notification_id = $_POST['notification_id']; // Get the notification ID from the POST request

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
$stmt->bind_param("i", $notification_id);
$stmt->execute();

echo json_encode(['success' => true]); // Return a JSON response
?>
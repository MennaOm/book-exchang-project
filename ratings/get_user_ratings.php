<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$target_user_id = $_GET['user_id'] ?? $_SESSION['user_id'];

// Get average and count
$stmt = $conn->prepare("SELECT AVG(rating) AS average_rating, COUNT(*) AS count FROM user_ratings WHERE rated_user_id = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$result = $stmt->get_result();
$avg_row = $result->fetch_assoc();
$average = $avg_row['average_rating'] ? round((float)$avg_row['average_rating'], 1) : 0;
$count = (int)$avg_row['count'];

// Get reviews list
$reviews = [];
$stmt = $conn->prepare("
    SELECT r.rating, r.review_text, r.rating_date, u.username AS rater_name 
    FROM user_ratings r 
    JOIN users u ON r.rater_id = u.user_id 
    WHERE r.rated_user_id = ? 
    ORDER BY r.rating_date DESC
");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode([
    'success' => true, 
    'average' => $average, 
    'count' => $count, 
    'reviews' => $reviews
]);

?>

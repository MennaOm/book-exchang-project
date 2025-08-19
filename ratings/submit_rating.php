<?php 
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$data = get_post_json();

$request_id = $data['request_id'] ?? null;
$rating     = $data['rating'] ?? null;
$review     = $data['review'] ?? '';

if (!$request_id || !$rating || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Fetch exchange details
$stmt = $conn->prepare("
    SELECT er.status, er.requester_id, bl.user_id AS owner_id
    FROM exchange_requests er
    JOIN book_listings bl ON er.requested_listing_id = bl.listing_id
    WHERE er.request_id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

$row = $result->fetch_assoc();
if (!in_array($row['status'], ['completed'])) {
    echo json_encode(['success' => false, 'message' => 'Request not completed']);
    exit;
}


$requester_id = $row['requester_id'];
$owner_id     = $row['owner_id'];

// only requester or owner can rate
if ($user_id != $requester_id && $user_id != $owner_id) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Determine who is being rated
$rated_id = ($user_id == $requester_id) ? $owner_id : $requester_id;

// Prevent duplicate rating
$stmt = $conn->prepare("
    SELECT rating_id 
    FROM user_ratings 
    WHERE exchange_request_id = ? AND rater_id = ?
");
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already rated']);
    exit;
}

// Insert rating with rating_date = NOW()
$stmt = $conn->prepare("
    INSERT INTO user_ratings 
    (exchange_request_id, rater_id, rated_user_id, rating, review_text, rating_date) 
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiiis", $request_id, $user_id, $rated_id, $rating, $review);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving rating']);
}
?>
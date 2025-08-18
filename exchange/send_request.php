<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$input = get_post_json();
$requested_listing_id = intval($input['listing_id'] ?? 0);
$request_message = sanitize_text($input['message'] ?? '');

if ($requested_listing_id <= 0) {
    json_response(['success'=>false, 'error'=>'listing_id is required'], 422);
}

// 1) Ensure listing exists and is available; find owner
$stmt = $conn->prepare("SELECT listing_id, user_id, availability_status FROM book_listings WHERE listing_id = ? ");
$stmt->bind_param('i', $requested_listing_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    json_response(['success'=>false, 'error'=>'Listing not found'], 404);
}
$row = $res->fetch_assoc();
if ($row['availability_status'] !== 'available') {
    json_response(['success'=>false, 'error'=>'Listing is not available'], 409);
}
$owner_id = intval($row['user_id']);
if ($owner_id === $user_id) {
    json_response(['success'=>false, 'error'=>'You cannot request your own listing'], 409);
}

// 2) Prevent duplicate pending requests by same requester for same listing
$stmt = $conn->prepare("SELECT request_id FROM exchange_requests WHERE requester_id=? AND requested_listing_id=? AND status='pending'");
$stmt->bind_param('ii', $user_id, $requested_listing_id);
$stmt->execute();
$dup = $stmt->get_result();
if ($dup->num_rows > 0) {
    json_response(['success'=>false, 'error'=>'You already have a pending request for this listing'], 409);
}

// 3) Insert request
$stmt = $conn->prepare("INSERT INTO exchange_requests (requester_id, owner_id, requested_listing_id, status, request_message, request_date) VALUES (?, ?, ?, 'pending', ?, NOW())");
$stmt->bind_param('iiis', $user_id, $owner_id, $requested_listing_id, $request_message);
$stmt->execute();
$request_id = $stmt->insert_id;

json_response(['success'=>true, 'request_id'=>$request_id, 'message'=>'Exchange request sent']);
?>

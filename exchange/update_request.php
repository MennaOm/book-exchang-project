<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$input = get_post_json();
$request_id = intval($input['request_id'] ?? 0);
$action = strtolower(sanitize_text($input['action'] ?? '')); // approve|reject|cancel|complete

if ($request_id <= 0 || !in_array($action, ['approve','c','cancel','complete'])) {
    json_response(['success'=>false, 'error'=>'Invalid input'], 422);
}

// Load request
$stmt = $conn->prepare("SELECT request_id, requester_id, owner_id, requested_listing_id, status FROM exchange_requests WHERE request_id=? LIMIT 1");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) json_response(['success'=>false,'error'=>'Request not found'],404);
$req = $res->fetch_assoc();

// Permission rules
$is_owner = intval($req['owner_id']) === $user_id;
$is_requester = intval($req['requester_id']) === $user_id;

// State machine
$current = $req['status'];
if ($action === 'approve') {
    if (!$is_owner) json_response(['success'=>false,'error'=>'Only owner can approve'],403);
    if ($current !== 'pending') json_response(['success'=>false,'error'=>'Only pending requests can be approved'],409);

    // Mark listing pending_exchange, approve request
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE exchange_requests SET status='approved', response_date=NOW() WHERE request_id=?");
        $stmt->bind_param('i',  $request_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE book_listings SET status='pending_exchange' WHERE listing_id=?");
        $stmt->bind_param('i', $req['requested_listing_id']);
        $stmt->execute();

        $conn->commit();
        json_response(['success'=>true,'message'=>'Request approved']);
    } catch (Throwable $e) {
        $conn->rollback();
        json_response(['success'=>false,'error'=>'Failed to approve']);
    }
} elseif ($action === 'reject') {
    if (!$is_owner) json_response(['success'=>false,'error'=>'Only owner can reject'],403);
    if (!in_array($current, ['pending','approved'])) json_response(['success'=>false,'error'=>'Invalid state'],409);

    $stmt = $conn->prepare("UPDATE exchange_requests SET status='rejected', response_date=NOW() WHERE request_id=?");
    $stmt->bind_param('i',  $request_id);
    $stmt->execute();
    json_response(['success'=>true,'message'=>'Request rejected']);

} elseif ($action === 'cancel') {
    if (!$is_requester) json_response(['success'=>false,'error'=>'Only requester can cancel'],403);
    if ($current !== 'pending') json_response(['success'=>false,'error'=>'Only pending requests can be cancelled'],409);

    $stmt = $conn->prepare("UPDATE exchange_requests SET status='cancelled', response_date=NOW() WHERE request_id=?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    json_response(['success'=>true,'message'=>'Request cancelled']);

} elseif ($action === 'complete') {
    // Either party can mark complete 
    if (!($is_owner || $is_requester)) json_response(['success'=>false,'error'=>'Not allowed'],403);
    if ($current !== 'approved') json_response(['success'=>false,'error'=>'Only approved requests can be completed'],409);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE exchange_requests SET status='completed', completion_date=NOW() WHERE request_id=?");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE book_listings SET status='exchanged' WHERE listing_id=?");
        $stmt->bind_param('i', $req['requested_listing_id']);
        $stmt->execute();


        $stmt = $conn->prepare("INSERT INTO exchange_requests_archive (request_id, requester_id, owner_id, requested_listing_id, offered_listing_id, status, request_message, request_date, response_date, archived_date) SELECT request_id, requester_id, owner_id, requested_listing_id, offered_listing_id, status, request_message, request_date, response_date, NOW() FROM exchange_requests WHERE request_id=?");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();

        $conn->commit();
        json_response(['success'=>true,'message'=>'Exchange completed']);
    } catch (Throwable $e) {
        $conn->rollback();
        json_response(['success'=>false,'error'=>'Failed to complete']);
    }
}
?>

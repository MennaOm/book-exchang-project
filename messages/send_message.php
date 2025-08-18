<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$input = get_post_json();
$exchange_id = intval($input['exchange_request_id'] ?? 0);
$content = sanitize_text($input['message'] ?? '');

if ($exchange_id <= 0 || $content === '') {
    json_response(['success'=>false, 'error'=>'exchange_request_id and message are required'], 422);
}

// Verify user is participant in the exchange
$stmt = $conn->prepare("SELECT requester_id, owner_id, status FROM exchange_requests WHERE request_id=?");
$stmt->bind_param('i', $exchange_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) json_response(['success'=>false, 'error'=>'Exchange not found'], 404);
$er = $res->fetch_assoc();
if (!in_array($er['status'], ['pending','approved','completed'])) {
    json_response(['success'=>false,'error'=>'Messaging not allowed in this state'], 409);
}
if ($user_id !== intval($er['requester_id']) && $user_id !== intval($er['owner_id'])) {
    json_response(['success'=>false,'error'=>'Not a participant'], 403);
}
$receiver_id = ($user_id === intval($er['requester_id'])) ? intval($er['owner_id']) : intval($er['requester_id']);

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, exchange_request_id, message_content, sent_date,is_read)
                         VALUES (?, ?, ?, ?, NOW(), 0)");
$stmt->bind_param('iiis', $user_id, $receiver_id, $exchange_id, $content);
$stmt->execute();

json_response(['success'=>true,'message'=>'Message sent']);
?>

<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$exchange_id = intval($_GET['exchange_request_id'] ?? 0);
if ($exchange_id <= 0) json_response(['success'=>false,'error'=>'exchange_request_id is required'], 422);

// Verify participant
$stmt = $conn->prepare("SELECT requester_id, owner_id FROM exchange_requests WHERE request_id=?");
$stmt->bind_param('i', $exchange_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) json_response(['success'=>false,'error'=>'Exchange not found'],404);
$er = $res->fetch_assoc();
if ($user_id !== intval($er['requester_id']) && $user_id !== intval($er['owner_id'])) {
    json_response(['success'=>false,'error'=>'Not a participant'],403);
}

$stmt = $conn->prepare("SELECT m.message_id, m.sender_id, m.receiver_id, u.username 
                        AS sender_username, m.message_content, m.sent_date, m.is_read
                        FROM messages m
                        JOIN users u ON u.user_id = m.sender_id
                        WHERE m.exchange_request_id = ?
                        ORDER BY m.sent_date ASC, m.message_id ASC");
$stmt->bind_param('i', $exchange_id);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;
echo json_encode($out);
?>

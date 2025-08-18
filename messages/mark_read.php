<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$input = get_post_json();
$exchange_id = intval($input['exchange_request_id'] ?? 0);
if ($exchange_id <= 0) json_response(['success'=>false,'error'=>'exchange_request_id is required'], 422);

// Only mark as read messages where the user is receiver
$stmt = $conn->prepare("UPDATE messages SET is_read=1 WHERE exchange_request_id=? AND receiver_id=?");
$stmt->bind_param('ii', $exchange_id, $user_id);
$stmt->execute();

json_response(['success'=>true,'updated'=>$stmt->affected_rows]);
?>

<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
// All exchanges where user is participant
$stmt = $conn->prepare("
    SELECT er.request_id, er.requester_id, er.owner_id, er.status, b.title AS listing_title,
           (SELECT message_content FROM messages m WHERE m.exchange_request_id = er.request_id 
           ORDER BY m.sent_date DESC, m.message_id DESC LIMIT 1) AS last_message,
           (SELECT sent_date FROM messages m WHERE m.exchange_request_id = er.request_id 
           ORDER BY m.sent_date DESC, m.message_id DESC LIMIT 1) AS last_time,
           (SELECT COUNT(*) FROM messages m WHERE m.exchange_request_id = er.request_id AND m.receiver_id = ? AND m.is_read = 0) AS unread_count
    FROM exchange_requests er
    JOIN book_listings bl ON bl.listing_id = er.requested_listing_id
    JOIN books b ON bl.book_id = b.book_id
    WHERE er.requester_id = ? OR er.owner_id = ?
    ORDER BY last_time DESC 
");
$stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;
echo json_encode($out);
?>

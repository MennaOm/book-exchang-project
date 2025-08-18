<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_inc/db.php';
require_once __DIR__ . '/../_inc/auth.php';
require_once __DIR__ . '/../_inc/helpers.php';

$user_id = require_login();
$role = ($_GET['role'] ?? 'owner'); // owner | requester
$status = $_GET['status'] ?? null;

if ($role === 'requester') {
    $sql = "SELECT er.*, b.title as listing_title
            FROM exchange_requests er
            JOIN book_listings bl ON bl.listing_id = er.requested_listing_id
            JOIN books b ON bl.book_id = b.book_id
            WHERE er.requester_id = ?";
} else { // owner
    $sql = "SELECT er.*, b.title as listing_title
            FROM exchange_requests er
            JOIN book_listings bl ON bl.listing_id = er.requested_listing_id
            JOIN books b ON bl.book_id = b.book_id
            WHERE er.owner_id = ?";
}
if ($status) {
    $sql .= " AND er.status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $status);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;
echo json_encode($out);
?>

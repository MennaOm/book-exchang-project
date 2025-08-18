<?php
// backend/_inc/auth.php
// Very small auth helper based on PHP sessions.
// Assumes your existing login code sets $_SESSION['user_id'].
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    return intval($_SESSION['user_id']);
}
?>

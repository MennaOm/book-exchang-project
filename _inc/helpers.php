<?php
function json_response($data, $code = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data);
    exit;
}
function get_post_json() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (is_array($data)) return $data;
    return $_POST; // fallback for form-encoded
}
function sanitize_text($s) {
    return trim($s ?? '');
}
?>

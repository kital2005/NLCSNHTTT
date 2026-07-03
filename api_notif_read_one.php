<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notif_id = intval($data['id'] ?? 0);
$user_id = intval($_SESSION['user_id']);

if ($notif_id > 0) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $user_id");
    echo json_encode(['status' => 'success']);
}
?>

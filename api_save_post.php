<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

$check = $conn->query("SELECT id FROM saved_posts WHERE post_id = $post_id AND user_id = $user_id");

if ($check->num_rows > 0) {
    // Nếu đã lưu thì Bỏ lưu
    $conn->query("DELETE FROM saved_posts WHERE post_id = $post_id AND user_id = $user_id");
    echo json_encode(['status' => 'success', 'action' => 'unsaved']);
} else {
    // Nếu chưa thì Lưu
    $conn->query("INSERT INTO saved_posts (post_id, user_id) VALUES ($post_id, $user_id)");
    echo json_encode(['status' => 'success', 'action' => 'saved']);
}
?>
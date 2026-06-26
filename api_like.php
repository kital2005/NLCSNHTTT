<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin chủ bài viết
$post_query = $conn->query("SELECT user_id FROM posts WHERE id = $post_id");
$post_owner = $post_query->fetch_assoc()['user_id'];

$check = $conn->query("SELECT id FROM likes WHERE post_id = $post_id AND user_id = $user_id");

if ($check->num_rows > 0) {
    $conn->query("DELETE FROM likes WHERE post_id = $post_id AND user_id = $user_id");
    // Xóa luôn thông báo nếu unlike
    $conn->query("DELETE FROM notifications WHERE user_id = $post_owner AND sender_id = $user_id AND type = 'like' AND post_id = $post_id");
    $action = 'unliked';
} else {
    $conn->query("INSERT INTO likes (post_id, user_id) VALUES ($post_id, $user_id)");
    // Bắn thông báo (nếu không phải tự like bài mình)
    if ($post_owner != $user_id) {
        $conn->query("INSERT INTO notifications (user_id, sender_id, type, post_id) VALUES ($post_owner, $user_id, 'like', $post_id)");
    }
    $action = 'liked';
}

$count = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id = $post_id")->fetch_assoc()['total'];
echo json_encode(['status' => 'success', 'action' => $action, 'likes' => $count]);
?>
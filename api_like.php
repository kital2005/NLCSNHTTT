<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

// Kiểm tra xem đã like bài này chưa
$check = $conn->query("SELECT id FROM likes WHERE post_id = $post_id AND user_id = $user_id");

if ($check->num_rows > 0) {
    // Nếu đã like rồi -> Bấm lần nữa là Hủy like (Unlike)
    $conn->query("DELETE FROM likes WHERE post_id = $post_id AND user_id = $user_id");
    $action = 'unliked';
} else {
    // Nếu chưa like -> Thêm mới (Like)
    $conn->query("INSERT INTO likes (post_id, user_id) VALUES ($post_id, $user_id)");
    $action = 'liked';
}

// Đếm tổng số like hiện tại của bài viết
$count_query = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id = $post_id");
$count = $count_query->fetch_assoc()['total'];

echo json_encode(['status' => 'success', 'action' => $action, 'likes' => $count]);
?>
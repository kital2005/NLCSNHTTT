<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$shared_post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin bài gốc
$check = $conn->query("SELECT user_id FROM posts WHERE id = $shared_post_id");
if ($check->num_rows > 0) {
    $original_owner = $check->fetch_assoc()['user_id'];

    if ($original_owner == $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không thể tự chia sẻ bài của chính mình!']);
        exit;
    }

    // Tạo bài viết mới có đính kèm shared_post_id
    $content = "Đã chia sẻ một bài viết.";
    $conn->query("INSERT INTO posts (user_id, content, privacy, shared_post_id) VALUES ($user_id, '$content', 'public', $shared_post_id)");

    // Bắn thông báo cho chủ nhân bài viết
    $conn->query("INSERT INTO notifications (user_id, sender_id, type, post_id) VALUES ($original_owner, $user_id, 'share', $shared_post_id)");

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bài viết gốc không tồn tại.']);
}
?>
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

$unread = (int)$conn->query("SELECT COUNT(*) as c FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['c'];

$latest = null;
$latest_res = $conn->query("SELECT n.*, u.full_name as sender_name FROM notifications n JOIN users u ON n.sender_id = u.id WHERE n.user_id = $user_id ORDER BY n.id DESC LIMIT 1");
if ($latest_res && $row = $latest_res->fetch_assoc()) {
    $messages = [
        'like' => 'đã thích bài viết của bạn',
        'comment' => 'đã bình luận bài viết',
        'friend_request' => 'đã gửi lời mời kết bạn',
        'friend_accept' => 'đã chấp nhận kết bạn',
        'ai_complete' => 'AI đã xử lý xong'
    ];
    $latest = [
        'id' => (int)$row['id'],
        'sender_name' => $row['sender_name'],
        'type' => $row['type'],
        'message' => $messages[$row['type']] ?? 'có thông báo mới'
    ];
}

echo json_encode([
    'status' => 'success',
    'unread_count' => $unread,
    'latest' => $latest
]);
?>

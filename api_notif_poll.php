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

// Đếm số thông báo chưa đọc
$sql_count = "SELECT COUNT(*) as c FROM THONG_BAO WHERE ND_Ma_Nhan = $user_id AND TB_DaDoc = 0";
$unread = (int)$conn->query($sql_count)->fetch_assoc()['c'];

$latest = null;
// Join bảng THONG_BAO và NGUOI_DUNG
$sql_latest = "SELECT n.TB_Ma as id, n.TB_Loai as type, u.ND_HoTen as sender_name 
               FROM THONG_BAO n 
               JOIN NGUOI_DUNG u ON n.ND_Ma_Gui = u.ND_Ma 
               WHERE n.ND_Ma_Nhan = $user_id 
               ORDER BY n.TB_Ma DESC LIMIT 1";

$latest_res = $conn->query($sql_latest);

if ($latest_res && $row = $latest_res->fetch_assoc()) {
    // Đã cập nhật đầy đủ các tính năng cho Mạng xã hội & Group
    $messages = [
        'like' => 'đã thích bài viết của bạn',
        'comment' => 'đã bình luận bài viết',
        'share' => 'đã chia sẻ bài viết của bạn',
        'friend_request' => 'đã gửi lời mời kết bạn',
        'friend_accept' => 'đã chấp nhận kết bạn',
        'ai_complete' => 'AI đã xử lý xong',
        'group_pending' => 'đã gửi bài viết chờ duyệt',
        'group_approved' => 'đã duyệt bài viết của bạn',
        'group_join' => 'đã xin tham gia nhóm'
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
]);?>
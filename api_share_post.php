<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$shared_post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin bài gốc từ bảng BAI_VIET
$sql_check = "SELECT ND_Ma as user_id FROM BAI_VIET WHERE BV_Ma = $shared_post_id";
$check = $conn->query($sql_check);

if ($check->num_rows > 0) {
    $original_owner = $check->fetch_assoc()['user_id'];

    if ($original_owner == $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không thể tự chia sẻ bài của chính mình!']);
        exit;
    }

    // Tạo bài viết mới có đính kèm shared_post_id (Bảng BAI_VIET)
    $content = "Đã chia sẻ một bài viết.";
    $sql_insert_post = "INSERT INTO BAI_VIET (ND_Ma, BV_NoiDung, BV_QuyenRiengTu, BV_MaChiaSe) 
                        VALUES ($user_id, '$content', 'public', $shared_post_id)";
    $conn->query($sql_insert_post);

    // Bắn thông báo cho chủ nhân bài viết (Bảng THONG_BAO)
    $sql_notif = "INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai, BV_Ma) 
                  VALUES ($original_owner, $user_id, 'share', $shared_post_id)";
    $conn->query($sql_notif);

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bài viết gốc không tồn tại.']);
}
?>
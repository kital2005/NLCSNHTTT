<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$content = $conn->real_escape_string(trim($data['content']));
$user_id = $_SESSION['user_id'];

if (!empty($content)) {
    // Lưu bình luận vào bảng BINH_LUAN
    if ($conn->query("INSERT INTO BINH_LUAN (BV_Ma, ND_Ma, BL_NoiDung) VALUES ($post_id, $user_id, '$content')")) {
        
        // Tìm chủ bài viết từ bảng BAI_VIET
        $post_owner_query = $conn->query("SELECT ND_Ma as user_id FROM BAI_VIET WHERE BV_Ma = $post_id");
        $post_owner = $post_owner_query->fetch_assoc()['user_id'];
        
        // Bắn thông báo vào bảng THONG_BAO
        if ($post_owner != $user_id) {
            $conn->query("INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai, BV_Ma) VALUES ($post_owner, $user_id, 'comment', $post_id)");
        }

        echo json_encode([
            'status' => 'success', 
            'full_name' => $_SESSION['full_name'], 
            'first_letter' => mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"), 
            'content' => htmlspecialchars($content)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL!']);
    }
}
?>
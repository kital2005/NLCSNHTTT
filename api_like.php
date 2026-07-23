<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin chủ bài viết từ bảng BAI_VIET
$post_query = $conn->query("SELECT ND_Ma as user_id FROM BAI_VIET WHERE BV_Ma = $post_id");
$post_owner = $post_query->fetch_assoc()['user_id'];

// Kiểm tra xem đã like chưa từ bảng LUOT_THICH
$sql_check = "SELECT LT_Ma FROM LUOT_THICH WHERE BV_Ma = $post_id AND ND_Ma = $user_id";
$check = $conn->query($sql_check);

if ($check->num_rows > 0) {
    // Unlike
    $conn->query("DELETE FROM LUOT_THICH WHERE BV_Ma = $post_id AND ND_Ma = $user_id");
    
    // Xóa luôn thông báo nếu unlike
    $sql_del_notif = "DELETE FROM THONG_BAO 
                      WHERE ND_Ma_Nhan = $post_owner AND ND_Ma_Gui = $user_id 
                      AND TB_Loai = 'like' AND BV_Ma = $post_id";
    $conn->query($sql_del_notif);
    
    $action = 'unliked';
} else {
    // Like
    $conn->query("INSERT INTO LUOT_THICH (BV_Ma, ND_Ma) VALUES ($post_id, $user_id)");
    
    // Bắn thông báo
    if ($post_owner != $user_id) {
        $sql_notif = "INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai, BV_Ma) 
                      VALUES ($post_owner, $user_id, 'like', $post_id)";
        $conn->query($sql_notif);
    }
    $action = 'liked';
}

$count_query = $conn->query("SELECT COUNT(*) as total FROM LUOT_THICH WHERE BV_Ma = $post_id");
$count = $count_query->fetch_assoc()['total'];

echo json_encode(['status' => 'success', 'action' => $action, 'likes' => $count]);
?>
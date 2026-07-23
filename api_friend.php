<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'];
$target_id = intval($data['target_id']);
$user_id = $_SESSION['user_id'];

if ($action == 'add') {
    // Thêm lời mời kết bạn
    $conn->query("INSERT INTO BAN_BE (ND_Ma_Gui, ND_Ma_Nhan, BB_TrangThai) VALUES ($user_id, $target_id, 'pending')");
    $conn->query("INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai) VALUES ($target_id, $user_id, 'friend_request')");
    echo json_encode(['status' => 'success', 'new_state' => 'pending']);
} 
elseif ($action == 'accept') {
    // Chấp nhận kết bạn
    $conn->query("UPDATE BAN_BE SET BB_TrangThai = 'accepted' WHERE ND_Ma_Gui = $target_id AND ND_Ma_Nhan = $user_id");
    $conn->query("INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai) VALUES ($target_id, $user_id, 'friend_accept')");
    echo json_encode(['status' => 'success', 'new_state' => 'accepted']);
} 
elseif ($action == 'cancel' || $action == 'unfriend') {
    // Hủy kết bạn hoặc xóa lời mời
    $conn->query("DELETE FROM BAN_BE WHERE (ND_Ma_Gui = $user_id AND ND_Ma_Nhan = $target_id) OR (ND_Ma_Gui = $target_id AND ND_Ma_Nhan = $user_id)");
    echo json_encode(['status' => 'success', 'new_state' => 'none']);
}
?>
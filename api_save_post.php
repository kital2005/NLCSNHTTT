<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$user_id = $_SESSION['user_id'];

// Kiểm tra từ bảng BAI_VIET_DA_LUU
$sql_check = "SELECT BVL_Ma FROM BAI_VIET_DA_LUU WHERE BV_Ma = $post_id AND ND_Ma = $user_id";
$check = $conn->query($sql_check);

if ($check->num_rows > 0) {
    // Nếu đã lưu thì Bỏ lưu
    $sql_del = "DELETE FROM BAI_VIET_DA_LUU WHERE BV_Ma = $post_id AND ND_Ma = $user_id";
    $conn->query($sql_del);
    echo json_encode(['status' => 'success', 'action' => 'unsaved']);
} else {
    // Nếu chưa thì Lưu
    $sql_insert = "INSERT INTO BAI_VIET_DA_LUU (BV_Ma, ND_Ma) VALUES ($post_id, $user_id)";
    $conn->query($sql_insert);
    echo json_encode(['status' => 'success', 'action' => 'saved']);
}
?>
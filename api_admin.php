<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

$admin_id = intval($_SESSION['user_id']);
if (!is_admin($conn, $admin_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Không có quyền']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id = intval($data['id'] ?? 0);

if ($action === 'delete_post' && $id > 0) {
    // Cập nhật tên bảng Tiếng Việt
    $conn->query("DELETE FROM LUOT_THICH WHERE BV_Ma = $id");
    $conn->query("DELETE FROM BINH_LUAN WHERE BV_Ma = $id");
    $conn->query("DELETE FROM THONG_BAO WHERE BV_Ma = $id");
    $conn->query("DELETE FROM BAI_VIET_HASHTAG WHERE BV_Ma = $id");
    $conn->query("DELETE FROM BAI_VIET_DA_LUU WHERE BV_Ma = $id");
    $conn->query("DELETE FROM BAI_VIET WHERE BV_Ma = $id");
    
    echo json_encode(['status' => 'success', 'message' => 'Đã xóa bài viết']);
    exit;
}

if ($action === 'toggle_role' && $id > 0 && $id != $admin_id) {
    // Đổi quyền Admin/User
    $res = $conn->query("SELECT ND_VaiTro FROM NGUOI_DUNG WHERE ND_Ma = $id");
    if ($row = $res->fetch_assoc()) {
        $new_role = ($row['ND_VaiTro'] === 'admin') ? 'user' : 'admin';
        $conn->query("UPDATE NGUOI_DUNG SET ND_VaiTro = '$new_role' WHERE ND_Ma = $id");
        echo json_encode(['status' => 'success', 'message' => "Đã đổi role thành $new_role"]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Thao tác không hợp lệ']);?>
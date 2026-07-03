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
    $conn->query("DELETE FROM likes WHERE post_id = $id");
    $conn->query("DELETE FROM comments WHERE post_id = $id");
    $conn->query("DELETE FROM notifications WHERE post_id = $id");
    $conn->query("DELETE FROM posts WHERE id = $id");
    echo json_encode(['status' => 'success', 'message' => 'Đã xóa bài viết']);
    exit;
}

if ($action === 'toggle_role' && $id > 0 && $id != $admin_id) {
    $res = $conn->query("SELECT role FROM users WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        $new_role = ($row['role'] === 'admin') ? 'user' : 'admin';
        $conn->query("UPDATE users SET role = '$new_role' WHERE id = $id");
        echo json_encode(['status' => 'success', 'message' => "Đã đổi role thành $new_role"]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Thao tác không hợp lệ']);
?>

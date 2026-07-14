<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy dữ liệu từ FormData (khi tạo nhóm) hoặc JSON (khi bấm nút)
$action = isset($_POST['action']) ? $_POST['action'] : '';
if(empty($action)) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = isset($data['action']) ? $data['action'] : '';
    $target_id = isset($data['group_id']) ? intval($data['group_id']) : 0;
    $member_id = isset($data['member_id']) ? intval($data['member_id']) : 0;
}

if ($action == 'create') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $desc = $conn->real_escape_string(trim($_POST['description']));
    $privacy = isset($_POST['privacy']) ? $conn->real_escape_string($_POST['privacy']) : 'public';
    $cover_url = 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=random&size=500';

    if (empty($name)) { echo json_encode(['status' => 'error', 'message' => 'Tên nhóm không được trống!']); exit; }

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            $filename = "group_" . time() . "_" . rand(100,999) . "." . $ext;
            $upload_path = "uploads/" . $filename;
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_path)) { $cover_url = $upload_path; }
        }
    }

    $conn->query("INSERT INTO `groups` (name, description, cover_url, privacy, creator_id) VALUES ('$name', '$desc', '$cover_url', '$privacy', $user_id)");
    $group_id = $conn->insert_id;
    $conn->query("INSERT INTO group_members (group_id, user_id, role) VALUES ($group_id, $user_id, 'admin')");
    echo json_encode(['status' => 'success', 'group_id' => $group_id]); exit;
} 
elseif ($action == 'join') {
    // Kiểm tra nhóm là public hay private
    $grp = $conn->query("SELECT privacy FROM `groups` WHERE id = $target_id")->fetch_assoc();
    $role = ($grp['privacy'] == 'private') ? 'pending' : 'member';
    
    $conn->query("INSERT IGNORE INTO group_members (group_id, user_id, role) VALUES ($target_id, $user_id, '$role')");
    
    if ($role == 'pending') {
        echo json_encode(['status' => 'pending', 'message' => 'Đã gửi yêu cầu tham gia. Vui lòng chờ Chủ nhóm phê duyệt!']);
    } else {
        echo json_encode(['status' => 'success']);
    }
    exit;
} 
elseif ($action == 'leave') {
    $check_admin = $conn->query("SELECT role FROM group_members WHERE group_id = $target_id AND user_id = $user_id");
    if ($check_admin->num_rows > 0 && $check_admin->fetch_assoc()['role'] == 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Quản trị viên không thể rời nhóm!']); exit;
    }
    $conn->query("DELETE FROM group_members WHERE group_id = $target_id AND user_id = $user_id");
    echo json_encode(['status' => 'success']); exit;
}
// Chức năng duyệt/từ chối thành viên của Chủ nhóm
elseif ($action == 'approve_member') {
    $conn->query("UPDATE group_members SET role = 'member' WHERE group_id = $target_id AND user_id = $member_id");
    echo json_encode(['status' => 'success']); exit;
}
elseif ($action == 'reject_member') {
    $conn->query("DELETE FROM group_members WHERE group_id = $target_id AND user_id = $member_id AND role = 'pending'");
    echo json_encode(['status' => 'success']); exit;
}
?>
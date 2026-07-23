<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$user_id = $_SESSION['user_id'];

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

    if (empty($name)) { 
        echo json_encode(['status' => 'error', 'message' => 'Tên nhóm không được trống!']); 
        exit; 
    }

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            $filename = "group_" . time() . "_" . rand(100,999) . "." . $ext;
            $upload_path = "uploads/" . $filename;
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_path)) { 
                $cover_url = $upload_path; 
            }
        }
    }

    $sql_create = "INSERT INTO NHOM (N_Ten, N_MoTa, N_AnhBia, N_QuyenRiengTu, ND_Ma_Tao) 
                   VALUES ('$name', '$desc', '$cover_url', '$privacy', $user_id)";
    $conn->query($sql_create);
    
    $group_id = $conn->insert_id;
    $sql_admin = "INSERT INTO THANH_VIEN_NHOM (N_Ma, ND_Ma, TVN_VaiTro) 
                  VALUES ($group_id, $user_id, 'admin')";
    $conn->query($sql_admin);
    
    echo json_encode(['status' => 'success', 'group_id' => $group_id]); 
    exit;
} 
elseif ($action == 'join') {
    $grp = $conn->query("SELECT N_QuyenRiengTu as privacy, ND_Ma_Tao as creator_id FROM NHOM WHERE N_Ma = $target_id")->fetch_assoc();
    $role = ($grp['privacy'] == 'private') ? 'pending' : 'member';
    
    $sql_join = "INSERT IGNORE INTO THANH_VIEN_NHOM (N_Ma, ND_Ma, TVN_VaiTro) 
                 VALUES ($target_id, $user_id, '$role')";
    $conn->query($sql_join);
    
    if ($role == 'pending') {
        $admin_id = $grp['creator_id'];
        $sql_notif = "INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai, BV_Ma) 
                      VALUES ($admin_id, $user_id, 'group_join', $target_id)";
        $conn->query($sql_notif);
        echo json_encode(['status' => 'pending', 'message' => 'Đã gửi yêu cầu tham gia. Vui lòng chờ Chủ nhóm duyệt!']);
    } else {
        echo json_encode(['status' => 'success']);
    }
    exit;
} 
elseif ($action == 'leave') {
    $sql_leave = "DELETE FROM THANH_VIEN_NHOM 
                  WHERE N_Ma = $target_id AND ND_Ma = $user_id AND TVN_VaiTro != 'admin'";
    $conn->query($sql_leave);
    echo json_encode(['status' => 'success']); 
    exit;
}
elseif ($action == 'approve_member') {
    $sql_approve = "UPDATE THANH_VIEN_NHOM SET TVN_VaiTro = 'member' 
                    WHERE N_Ma = $target_id AND ND_Ma = $member_id";
    $conn->query($sql_approve);
    echo json_encode(['status' => 'success']); 
    exit;
}
elseif ($action == 'reject_member') {
    $sql_reject = "DELETE FROM THANH_VIEN_NHOM 
                   WHERE N_Ma = $target_id AND ND_Ma = $member_id AND TVN_VaiTro = 'pending'";
    $conn->query($sql_reject);
    echo json_encode(['status' => 'success']); 
    exit;
}
?>
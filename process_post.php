<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $content = $conn->real_escape_string(trim($_POST['content']));
    $privacy = isset($_POST['privacy']) ? $conn->real_escape_string($_POST['privacy']) : 'public';
    $group_id = isset($_POST['group_id']) && !empty($_POST['group_id']) ? intval($_POST['group_id']) : "NULL";

    $ai_topic = isset($_POST['ai_topic']) ? $conn->real_escape_string($_POST['ai_topic']) : '';
    $ai_sentiment = isset($_POST['ai_sentiment']) ? $conn->real_escape_string($_POST['ai_sentiment']) : '';
    $generated_image_url = isset($_POST['ai_image_url']) ? $conn->real_escape_string($_POST['ai_image_url']) : '';
    $image_url = '';
    $status = 'approved'; 

    if (empty($content)) { header("Location: " . $_SERVER['HTTP_REFERER']); exit(); }

    // XỬ LÝ NHÓM: Kiểm tra Cài đặt phê duyệt (Bảng NHOM, THANH_VIEN_NHOM)
    if ($group_id !== "NULL") {
        $grp_info = $conn->query("SELECT ND_Ma_Tao as creator_id, N_DuyetBai as require_approval FROM NHOM WHERE N_Ma = $group_id")->fetch_assoc();
        $check_role = $conn->query("SELECT TVN_VaiTro as role FROM THANH_VIEN_NHOM WHERE N_Ma = $group_id AND ND_Ma = $user_id");
        if ($check_role->num_rows > 0) {
            $role = $check_role->fetch_assoc()['role'];
            // Nếu là thành viên VÀ nhóm yêu cầu duyệt
            if ($role === 'member' && $grp_info['require_approval'] == 1) {
                $status = 'pending';
            }
        } else {
            die("Bạn không có quyền đăng bài vào nhóm này!");
        }
    }

    // UPLOAD ẢNH
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            $dest = 'uploads/post_' . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $dest)) {
                $image_url = $conn->real_escape_string($dest);
            }
        }
    }

    // LƯU DB (Bảng BAI_VIET)
    $query = "INSERT INTO BAI_VIET (ND_Ma, N_Ma, BV_NoiDung, BV_QuyenRiengTu, BV_TrangThai, BV_ChuDeAI, BV_CamXucAI, BV_HinhAnh, BV_HinhAnhAI)
              VALUES ($user_id, $group_id, '$content', '$privacy', '$status', '$ai_topic', '$ai_sentiment', " .
              ($image_url ? "'$image_url'" : "NULL") . ", " .
              ($generated_image_url ? "'$generated_image_url'" : "NULL") . ")";
              
    if ($conn->query($query)) {
        $post_id = $conn->insert_id;
        
        // Hashtag (Bảng HASHTAG, BAI_VIET_HASHTAG)
        preg_match_all('/#(\w+)/u', $_POST['content'], $matches);
        foreach (array_unique($matches[1]) as $tag) {
            $tag = $conn->real_escape_string($tag);
            $conn->query("INSERT IGNORE INTO HASHTAG (HT_Ten) VALUES ('$tag')");
            $res = $conn->query("SELECT HT_Ma as id FROM HASHTAG WHERE HT_Ten = '$tag'");
            if ($row = $res->fetch_assoc()) {
                $conn->query("INSERT IGNORE INTO BAI_VIET_HASHTAG (BV_Ma, HT_Ma) VALUES ($post_id, {$row['id']})");
            }
        }

        // BẮN THÔNG BÁO CHO CHỦ NHÓM (Bảng THONG_BAO)
        if ($status === 'pending') {
            $admin_id = $grp_info['creator_id'];
            $conn->query("INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai, BV_Ma) VALUES ($admin_id, $user_id, 'group_pending', $post_id)");
            echo "<script>alert('Bài viết của bạn đã được gửi chờ Quản trị viên duyệt!'); window.location.href='group_detail.php?id=$group_id';</script>";
            exit();
        }
    }
    header("Location: " . $_SERVER['HTTP_REFERER']); exit();
}
?>
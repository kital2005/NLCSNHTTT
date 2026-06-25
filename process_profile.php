<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $conn->real_escape_string(trim($_POST['full_name']));
$bio = $conn->real_escape_string(trim($_POST['bio']));
$new_password = $_POST['new_password'];

// Cập nhật Tên và Tiểu sử
$conn->query("UPDATE users SET full_name = '$full_name', bio = '$bio' WHERE id = $user_id");
$_SESSION['full_name'] = $full_name;

// Cập nhật Mật khẩu nếu có nhập
if (!empty($new_password)) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$hashed_password' WHERE id = $user_id");
}

$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

// XỬ LÝ UPLOAD AVATAR
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_extensions)) {
        $new_filename = "avatar_" . $user_id . "_" . time() . "." . $ext;
        $upload_path = "uploads/" . $new_filename;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
            $conn->query("UPDATE users SET avatar_url = '$upload_path' WHERE id = $user_id");
            $_SESSION['avatar_url'] = $upload_path;
        }
    }
}

// XỬ LÝ UPLOAD ẢNH BÌA (COVER)
if (isset($_FILES['cover']) && $_FILES['cover']['error'] === 0) {
    $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_extensions)) {
        $new_filename = "cover_" . $user_id . "_" . time() . "." . $ext;
        $upload_path = "uploads/" . $new_filename;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_path)) {
            $conn->query("UPDATE users SET cover_url = '$upload_path' WHERE id = $user_id");
        }
    }
}

// Xong xuôi thì quay lại trang cá nhân
header("Location: profile.php");
exit();
?>
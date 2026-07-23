<?php
// Bắt đầu bật tính năng Session của PHP
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ép kiểu chống lỗi SQL Injection cơ bản
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    // Lấy thông tin tài khoản dựa trên username từ bảng NGUOI_DUNG
    $query = "SELECT * FROM NGUOI_DUNG WHERE ND_TaiKhoan = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Giải mã và kiểm tra mật khẩu
        if (password_verify($password, $user['ND_MatKhau'])) {
            // Đăng nhập đúng -> Lưu thông tin vào Session hệ thống (Giữ nguyên Key Tiếng Anh)
            $_SESSION['user_id'] = $user['ND_Ma'];
            $_SESSION['username'] = $user['ND_TaiKhoan'];
            $_SESSION['full_name'] = $user['ND_HoTen'];
            $_SESSION['avatar_url'] = $user['ND_AnhDaiDien'];

            // Chuyển hướng thẳng vào trang bảng tin chính
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Sai mật khẩu! Vui lòng thử lại.'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Tài khoản này không tồn tại trên hệ thống!'); window.location.href='login.php';</script>";
    }
} else {
    header("Location: login.php");
    exit();
}
?>
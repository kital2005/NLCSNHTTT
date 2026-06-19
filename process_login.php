<?php
// Bắt đầu bật tính năng Session của PHP
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ép kiểu chống lỗi SQL Injection cơ bản
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    // Lấy thông tin tài khoản dựa trên username
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Giải mã và kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            // Đăng nhập đúng -> Lưu thông tin vào Session hệ thống
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['avatar_url'] = $user['avatar_url'];

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
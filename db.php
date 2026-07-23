<?php
// Thông tin cấu hình mặc định của XAMPP
$host = 'localhost';
$dbname = 'social_ai_db_2'; // Tên database bạn đã tạo trong phpMyAdmin
$username = 'root';       // Tài khoản mặc định
$password = '';           // Mật khẩu mặc định để trống

// Tạo kết nối bằng MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}

// Thiết lập font chữ UTF-8 để gõ tiếng Việt không bị lỗi font
$conn->set_charset("utf8mb4");
?>
<?php
session_start();
require_once 'db.php';

// Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = $conn->real_escape_string(trim($_POST['content']));
    
    // Nếu nội dung trống thì quay lại trang chủ
    if (empty($content)) {
        header("Location: index.php");
        exit();
    }

    // Câu lệnh SQL chèn bài viết mới vào Database
    $query = "INSERT INTO posts (user_id, content) VALUES ('$user_id', '$content')";
    
    if ($conn->query($query) === TRUE) {
        // Lưu thành công thì F5 lại trang chủ
        header("Location: index.php");
        exit();
    } else {
        echo "Lỗi: " . $conn->error;
    }
} else {
    header("Location: index.php");
    exit();
}
?>
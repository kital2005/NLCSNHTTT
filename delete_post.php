<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Bảo mật: Chỉ cho phép xóa nếu bài viết đó thuộc về user đang đăng nhập (Bảng BAI_VIET)
$query = "DELETE FROM BAI_VIET WHERE BV_Ma = $post_id AND ND_Ma = $user_id";
$conn->query($query);

header("Location: index.php");
exit();
?>
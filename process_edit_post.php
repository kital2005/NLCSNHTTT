<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);
$content = $conn->real_escape_string(trim($_POST['content']));
$privacy = $conn->real_escape_string($_POST['privacy']);

if (!empty($content)) {
    // Đảm bảo chỉ người tạo bài viết mới được update (Bảng BAI_VIET)
    $query = "UPDATE BAI_VIET SET BV_NoiDung = '$content', BV_QuyenRiengTu = '$privacy' WHERE BV_Ma = $post_id AND ND_Ma = $user_id";
    $conn->query($query);
}

header("Location: index.php");
exit();
?>
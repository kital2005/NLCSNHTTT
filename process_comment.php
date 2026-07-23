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

if (!empty($content)) {
    // Lưu vào CSDL Tiếng Việt
    $query = "INSERT INTO BINH_LUAN (BV_Ma, ND_Ma, BL_NoiDung) VALUES ($post_id, $user_id, '$content')";
    $conn->query($query);
}

header("Location: index.php");
exit();
?>
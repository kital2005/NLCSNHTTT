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
    $query = "INSERT INTO comments (post_id, user_id, content) VALUES ($post_id, $user_id, '$content')";
    $conn->query($query);
}

header("Location: index.php");
exit();
?>
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = $conn->real_escape_string(trim($_POST['content']));
    $privacy = isset($_POST['privacy']) ? $conn->real_escape_string($_POST['privacy']) : 'public';
    
    $ai_topic = isset($_POST['ai_topic']) ? $conn->real_escape_string($_POST['ai_topic']) : '';
    $generated_image_url = isset($_POST['ai_image_url']) ? $conn->real_escape_string($_POST['ai_image_url']) : '';
    
    if (empty($content)) {
        header("Location: index.php");
        exit();
    }

    // Câu lệnh SQL nay đã có thêm cột privacy
    $query = "INSERT INTO posts (user_id, content, privacy, ai_topic, generated_image_url) 
              VALUES ('$user_id', '$content', '$privacy', '$ai_topic', '$generated_image_url')";
    $conn->query($query);

    header("Location: index.php");
    exit();
}
?>
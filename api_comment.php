<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$content = $conn->real_escape_string(trim($data['content']));
$user_id = $_SESSION['user_id'];

if (!empty($content)) {
    if ($conn->query("INSERT INTO comments (post_id, user_id, content) VALUES ($post_id, $user_id, '$content')")) {
        
        // Bắn thông báo
        $post_owner = $conn->query("SELECT user_id FROM posts WHERE id = $post_id")->fetch_assoc()['user_id'];
        if ($post_owner != $user_id) {
            $conn->query("INSERT INTO notifications (user_id, sender_id, type, post_id) VALUES ($post_owner, $user_id, 'comment', $post_id)");
        }

        echo json_encode(['status' => 'success', 'full_name' => $_SESSION['full_name'], 'first_letter' => mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"), 'content' => htmlspecialchars($content)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL!']);
    }
}
?>
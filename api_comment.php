<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi truy cập']);
    exit;
}

// Lấy dữ liệu JavaScript gửi lên
$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id']);
$content = $conn->real_escape_string(trim($data['content']));
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

if (!empty($content)) {
    // Lưu vào cơ sở dữ liệu
    $query = "INSERT INTO comments (post_id, user_id, content) VALUES ($post_id, $user_id, '$content')";
    
    if ($conn->query($query)) {
        // Lấy chữ cái đầu của tên để làm Avatar hiển thị ngay lập tức
        $first_letter = mb_substr($full_name, 0, 1, "UTF-8");
        
        echo json_encode([
            'status' => 'success',
            'full_name' => $full_name,
            'first_letter' => $first_letter,
            'content' => htmlspecialchars($content)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bình luận không được để trống!']);
}
?>
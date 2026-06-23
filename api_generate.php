<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi truy cập']);
    exit;
}

// Nhận dữ liệu do JavaScript gửi lên
$data = json_decode(file_get_contents('php://input'), true);
$content = isset($data['content']) ? trim($data['content']) : '';

if (empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập nội dung trước khi gọi AI']);
    exit;
}

// Ép UTF-8 và gọi Python
putenv('PYTHONIOENCODING=utf-8');
$content_escaped = escapeshellarg($content);
$command = "python ai_engine.py $content_escaped 2>&1";
$output = shell_exec($command);

$result = json_decode($output, true);

// Trả kết quả về cho trình duyệt (JavaScript)
if ($result === null) {
    echo json_encode(['status' => 'error', 'message' => 'AI đang bận, vui lòng thử lại!', 'raw' => $output]);
} else {
    echo json_encode($result);
}
?>
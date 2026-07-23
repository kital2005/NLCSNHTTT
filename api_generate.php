<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$content = isset($data['content']) ? trim($data['content']) : '';
$provider = isset($data['provider']) ? preg_replace('/[^a-z_]/', '', $data['provider']) : 'pollinations';

if (empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập nội dung trước khi gọi AI']);
    exit;
}

putenv('PYTHONIOENCODING=utf-8');
$content_escaped = escapeshellarg($content);
$provider_escaped = escapeshellarg($provider);
$output = shell_exec("python ai_engine.py $content_escaped $provider_escaped 2>&1");
$result = json_decode($output, true);

if ($result === null) {
    echo json_encode(['status' => 'error', 'message' => 'AI đang bận, vui lòng thử lại!', 'raw' => $output]);
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Lưu vết vào bảng LOG_AI (Tiếng Việt)
if (table_exists($conn, 'LOG_AI')) {
    $topic = $conn->real_escape_string($result['predicted_topic'] ?? '');
    $emotion = $conn->real_escape_string($result['predicted_emotion'] ?? '');
    $content_esc = $conn->real_escape_string(mb_substr($content, 0, 500));
    $img = $conn->real_escape_string($result['image_url'] ?? '');
    $time_ms = intval($result['processing_time_ms'] ?? 0);
    $status = $conn->real_escape_string($result['image_status'] ?? 'success');

    $sql_log = "INSERT INTO LOG_AI (ND_Ma, LA_NoiDung, LA_ChuDe, LA_CamXuc, LA_HinhAnh, LA_ThoiGian_ms, LA_TrangThai) 
                VALUES ($user_id, '$content_esc', '$topic', '$emotion', '$img', $time_ms, '$status')";
    $conn->query($sql_log);
}

echo json_encode($result);?>
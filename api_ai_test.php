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
$mode = isset($data['mode']) ? $data['mode'] : 'classify';
$provider = isset($data['provider']) ? preg_replace('/[^a-z_]/', '', $data['provider']) : 'pollinations';

if (empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Nhập nội dung để test']);
    exit;
}

putenv('PYTHONIOENCODING=utf-8');
$content_escaped = escapeshellarg($content);

if ($mode === 'classify_only') {
    $script = "python -c \"import sys,json,joblib; sys.stdout.reconfigure(encoding='utf-8'); v=joblib.load('tfidf_vectorizer.pkl'); m=joblib.load('social_ai_model.pkl'); p=m.predict(v.transform([sys.argv[1]]))[0]; t,e=p.split(' | ',1) if ' | ' in p else (p,'Bình thường'); print(json.dumps({'topic':t,'emotion':e},ensure_ascii=False))\" $content_escaped 2>&1";
    $output = shell_exec($script);
    $result = json_decode($output, true);
    if ($result) {
        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Model chưa được huấn luyện', 'raw' => $output]);
    }
    exit;
}

$provider_escaped = escapeshellarg($provider);
$output = shell_exec("python ai_engine.py $content_escaped $provider_escaped 2>&1");
$result = json_decode($output, true);

if ($result === null) {
    echo json_encode(['status' => 'error', 'message' => 'Test thất bại', 'raw' => $output]);
    exit;
}

$user_id = intval($_SESSION['user_id']);

if (table_exists($conn, 'ai_logs')) {
    $topic = $conn->real_escape_string($result['predicted_topic'] ?? '');
    $emotion = $conn->real_escape_string($result['predicted_emotion'] ?? '');
    $content_esc = $conn->real_escape_string(mb_substr($content, 0, 500));
    $img = $conn->real_escape_string($result['image_url'] ?? '');
    $time_ms = intval($result['processing_time_ms'] ?? 0);
    $status = $conn->real_escape_string($result['image_status'] ?? 'success');

    $conn->query("INSERT INTO ai_logs (user_id, post_content, predicted_topic, predicted_emotion, image_url, processing_time_ms, status)
                  VALUES ($user_id, '$content_esc', '$topic', '$emotion', '$img', $time_ms, '$status')");
}

echo json_encode(['status' => 'success', 'data' => $result]);
?>

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$stats = [
    'total_ai_calls' => 0,
    'total_ai_posts' => 0,
    'avg_processing_ms' => 0,
    'success_rate' => 0
];

if (table_exists($conn, 'ai_logs')) {
    $stats['total_ai_calls'] = (int)$conn->query("SELECT COUNT(*) as c FROM ai_logs")->fetch_assoc()['c'];
    $avg_res = $conn->query("SELECT AVG(processing_time_ms) as avg_ms FROM ai_logs WHERE processing_time_ms > 0");
    $stats['avg_processing_ms'] = $avg_res ? round((float)$avg_res->fetch_assoc()['avg_ms']) : 0;
    if ($stats['total_ai_calls'] > 0) {
        $success = (int)$conn->query("SELECT COUNT(*) as c FROM ai_logs WHERE status='success'")->fetch_assoc()['c'];
        $stats['success_rate'] = round($success / $stats['total_ai_calls'] * 100, 1);
    }
}

$stats['total_ai_posts'] = (int)$conn->query("SELECT COUNT(*) as c FROM posts WHERE generated_image_url IS NOT NULL AND generated_image_url != ''")->fetch_assoc()['c'];

$model_info = null;
if (file_exists('model_info.json')) {
    $model_info = json_decode(file_get_contents('model_info.json'), true);
}

$topic_chart = [];
$emotion_chart = [];
$recent_logs = [];

if (table_exists($conn, 'ai_logs')) {
    $topic_res = $conn->query("SELECT predicted_topic, COUNT(*) as cnt FROM ai_logs WHERE predicted_topic IS NOT NULL AND predicted_topic != '' GROUP BY predicted_topic ORDER BY cnt DESC LIMIT 8");
    if ($topic_res) {
        while ($row = $topic_res->fetch_assoc()) {
            $topic_chart[] = ['label' => $row['predicted_topic'], 'count' => (int)$row['cnt']];
        }
    }
    $emotion_res = $conn->query("SELECT predicted_emotion, COUNT(*) as cnt FROM ai_logs WHERE predicted_emotion IS NOT NULL AND predicted_emotion != '' GROUP BY predicted_emotion ORDER BY cnt DESC LIMIT 8");
    if ($emotion_res) {
        while ($row = $emotion_res->fetch_assoc()) {
            $emotion_chart[] = ['label' => $row['predicted_emotion'], 'count' => (int)$row['cnt']];
        }
    }
    $logs_res = $conn->query("SELECT l.*, u.full_name FROM ai_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 15");
    if ($logs_res) {
        while ($row = $logs_res->fetch_assoc()) {
            $recent_logs[] = $row;
        }
    }
}

$training_history = [];
if (table_exists($conn, 'ai_training_history')) {
    $hist_res = $conn->query("SELECT * FROM ai_training_history ORDER BY created_at DESC LIMIT 10");
    if ($hist_res) {
        while ($row = $hist_res->fetch_assoc()) {
            $training_history[] = $row;
        }
    }
}

$recent_ai_images = [];
$img_res = $conn->query("SELECT id, content, generated_image_url, ai_topic, created_at FROM posts WHERE generated_image_url IS NOT NULL AND generated_image_url != '' ORDER BY created_at DESC LIMIT 12");
if ($img_res) {
    while ($row = $img_res->fetch_assoc()) {
        $recent_ai_images[] = $row;
    }
}

$dataset_info = ['size' => 0, 'topics' => 0, 'emotions' => 0];
if (file_exists('dataset_20k.csv')) {
    $dataset_info['size'] = max(0, count(file('dataset_20k.csv')) - 1);
}
if ($model_info) {
    $dataset_info['topics'] = count($model_info['topic_distribution'] ?? []);
    $dataset_info['emotions'] = count($model_info['emotion_distribution'] ?? []);
}

echo json_encode([
    'status' => 'success',
    'stats' => $stats,
    'model_info' => $model_info,
    'topic_chart' => $topic_chart,
    'emotion_chart' => $emotion_chart,
    'training_history' => $training_history,
    'recent_logs' => $recent_logs,
    'recent_ai_images' => $recent_ai_images,
    'dataset_info' => $dataset_info,
    'has_confusion_matrix' => file_exists('confusion_matrix.png'),
    'has_model' => file_exists('social_ai_model.pkl')
]);
?>

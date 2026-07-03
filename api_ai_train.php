<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
if (!is_admin($conn, $user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Chỉ admin mới huấn luyện lại model']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

putenv('PYTHONIOENCODING=utf-8');
$output = shell_exec('python train_model.py 2>&1');
$result = json_decode($output, true);

if ($result === null || ($result['status'] ?? '') !== 'success') {
    echo json_encode(['status' => 'error', 'message' => 'Huấn luyện thất bại', 'raw' => $output]);
    exit;
}

if (table_exists($conn, 'ai_training_history')) {
    $model_name = $conn->real_escape_string($result['best_model']);
    $svm = floatval($result['svm_accuracy']);
    $rf = floatval($result['rf_accuracy']);
    $size = intval($result['dataset_size']);
    $dur = intval($result['training_duration_seconds']);

    $conn->query("INSERT INTO ai_training_history (model_name, svm_accuracy, rf_accuracy, dataset_size, training_duration_seconds)
                  VALUES ('$model_name', $svm, $rf, $size, $dur)");
}

echo json_encode(['status' => 'success', 'data' => $result]);
?>

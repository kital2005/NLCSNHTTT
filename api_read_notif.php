<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_SESSION['user_id']);
    // Đã chuyển sang bảng THONG_BAO
    $conn->query("UPDATE THONG_BAO SET TB_DaDoc = 1 WHERE ND_Ma_Nhan = $user_id");
    echo json_encode(['status' => 'success']);
}
// Nếu gọi bằng GET (từ js fetch default) thì dùng khối này
elseif (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $conn->query("UPDATE THONG_BAO SET TB_DaDoc = 1 WHERE ND_Ma_Nhan = $user_id");
    echo json_encode(['status' => 'success']);
}?>
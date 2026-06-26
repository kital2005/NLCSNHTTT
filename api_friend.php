<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'];
$target_id = intval($data['target_id']);
$user_id = $_SESSION['user_id'];

if ($action == 'add') {
    $conn->query("INSERT INTO friends (sender_id, receiver_id, status) VALUES ($user_id, $target_id, 'pending')");
    $conn->query("INSERT INTO notifications (user_id, sender_id, type) VALUES ($target_id, $user_id, 'friend_request')");
    echo json_encode(['status' => 'success', 'new_state' => 'pending']);
} 
elseif ($action == 'accept') {
    $conn->query("UPDATE friends SET status = 'accepted' WHERE sender_id = $target_id AND receiver_id = $user_id");
    $conn->query("INSERT INTO notifications (user_id, sender_id, type) VALUES ($target_id, $user_id, 'friend_accept')");
    echo json_encode(['status' => 'success', 'new_state' => 'accepted']);
} 
elseif ($action == 'cancel' || $action == 'unfriend') {
    $conn->query("DELETE FROM friends WHERE (sender_id = $user_id AND receiver_id = $target_id) OR (sender_id = $target_id AND receiver_id = $user_id)");
    echo json_encode(['status' => 'success', 'new_state' => 'none']);
}
?>
<?php
session_start();
require_once 'db.php';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
}
?>
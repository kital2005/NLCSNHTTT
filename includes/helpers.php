<?php
function avatar_url($name, $url = '') {
    if (!empty($url) && $url !== 'default-avatar.png') {
        return htmlspecialchars($url);
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
}

function time_ago($datetime) {
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    return date('H:i d/m/Y', $ts);
}

function notif_text($type) {
    switch ($type) {
        case 'like': return 'đã <b>thích</b> bài viết của bạn.';
        case 'comment': return 'đã <b>bình luận</b> về bài viết của bạn.';
        case 'friend_request': return 'đã gửi cho bạn một <b>lời mời kết bạn</b>.';
        case 'friend_accept': return 'đã <b>chấp nhận</b> lời mời kết bạn.';
        case 'ai_complete': return 'AI đã <b>phân tích & sinh ảnh</b> xong cho bài viết.';
        default: return 'đã tương tác với bạn.';
    }
}

function notif_link($type, $post_id = null) {
    if ($type === 'friend_request' || $type === 'friend_accept') return 'friends.php';
    if ($post_id) return 'index.php#post-' . intval($post_id);
    return 'notifications.php';
}

function notif_icon_class($type) {
    switch ($type) {
        case 'like': return 'fa-solid fa-heart text-danger';
        case 'comment': return 'fa-solid fa-comment text-primary';
        case 'friend_request': return 'fa-solid fa-user-plus text-info';
        case 'friend_accept': return 'fa-solid fa-user-check text-success';
        case 'ai_complete': return 'fa-solid fa-wand-magic-sparkles text-purple';
        default: return 'fa-solid fa-bell text-secondary';
    }
}

function is_admin($conn, $user_id) {
    $uid = intval($user_id);
    $res = @$conn->query("SELECT role FROM users WHERE id = $uid");
    if ($res && $row = $res->fetch_assoc()) {
        return isset($row['role']) && $row['role'] === 'admin';
    }
    return ($uid === 1);
}

function are_friends($conn, $uid1, $uid2) {
    $a = intval($uid1);
    $b = intval($uid2);
    $sql = "SELECT id FROM friends WHERE status='accepted' AND ((sender_id=$a AND receiver_id=$b) OR (sender_id=$b AND receiver_id=$a))";
    return $conn->query($sql)->num_rows > 0;
}

function table_exists($conn, $table) {
    $t = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    return $res && $res->num_rows > 0;
}
?>

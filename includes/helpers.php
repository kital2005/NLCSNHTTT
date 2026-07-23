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
        case 'share': return 'đã <b>chia sẻ</b> bài viết của bạn.';
        case 'friend_request': return 'đã gửi cho bạn một <b>lời mời kết bạn</b>.';
        case 'friend_accept': return 'đã <b>chấp nhận</b> lời mời kết bạn.';
        case 'ai_complete': return 'AI đã <b>phân tích & sinh ảnh</b> xong cho bài viết.';
        case 'group_pending': return 'đã gửi bài viết <b>chờ duyệt</b> vào nhóm.';
        case 'group_approved': return 'đã <b>duyệt</b> bài viết của bạn trong nhóm.';
        case 'group_join': return 'đã <b>xin tham gia</b> nhóm của bạn.';
        default: return 'đã tương tác với bạn.';
    }
}

function notif_link($type, $post_id = null) {
    if ($type === 'friend_request' || $type === 'friend_accept') return 'friends.php';
    if ($type === 'group_join') return 'groups.php';
    if ($post_id) return 'index.php#post-' . intval($post_id);
    return 'notifications.php';
}

function notif_icon_class($type) {
    switch ($type) {
        case 'like': return 'fa-solid fa-heart text-danger';
        case 'comment': return 'fa-solid fa-comment text-primary';
        case 'share': return 'fa-solid fa-share text-success';
        case 'friend_request': return 'fa-solid fa-user-plus text-info';
        case 'friend_accept': return 'fa-solid fa-user-check text-success';
        case 'ai_complete': return 'fa-solid fa-wand-magic-sparkles text-purple';
        case 'group_pending': return 'fa-solid fa-file-pen text-warning';
        case 'group_approved': return 'fa-solid fa-check-circle text-success';
        case 'group_join': return 'fa-solid fa-user-shield text-info';
        default: return 'fa-solid fa-bell text-secondary';
    }
}

function is_admin($conn, $user_id) {
    $uid = intval($user_id);
    // VIỆT HÓA: Bảng NGUOI_DUNG
    $res = @$conn->query("SELECT ND_VaiTro as role FROM NGUOI_DUNG WHERE ND_Ma = $uid");
    if ($res && $row = $res->fetch_assoc()) {
        return isset($row['role']) && $row['role'] === 'admin';
    }
    return ($uid === 1);
}

function are_friends($conn, $uid1, $uid2) {
    $a = intval($uid1);
    $b = intval($uid2);
    // VIỆT HÓA: Bảng BAN_BE
    $sql = "SELECT BB_Ma as id FROM BAN_BE 
            WHERE BB_TrangThai='accepted' 
            AND ((ND_Ma_Gui=$a AND ND_Ma_Nhan=$b) OR (ND_Ma_Gui=$b AND ND_Ma_Nhan=$a))";
    return $conn->query($sql)->num_rows > 0;
}

function table_exists($conn, $table) {
    $t = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    return $res && $res->num_rows > 0;
}

function format_post_content($content) {
    $content = htmlspecialchars($content);
    // Chuyển đổi #hashtag thành link
    $content = preg_replace('/#(\w+)/u', '<a href="search.php?hashtag=$1" class="hashtag">#$1</a>', $content);
    // Chuyển đổi xuống dòng thành thẻ <br>
    return nl2br($content);
}?>
<?php
require_once __DIR__ . '/helpers.php';

if (!isset($current_user_id)) {
    $current_user_id = $_SESSION['user_id'] ?? 0;
}

$uid = intval($current_user_id);

// VIỆT HÓA: Đếm thông báo từ bảng THONG_BAO
$sql_notif_count = "SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $uid AND TB_DaDoc = 0";
$notif_count_query = $conn->query($sql_notif_count);
$unread_notif_count = $notif_count_query ? (int)$notif_count_query->fetch_assoc()['total'] : 0;

$is_user_admin = is_admin($conn, $uid);
$nav_back_link = $nav_back_link ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
    <div class="container">
        <?php if ($nav_back_link): ?>
            <a class="navbar-brand fw-bold text-primary fs-5" href="<?php echo htmlspecialchars($nav_back_link); ?>">
                <i class="fa-solid fa-arrow-left me-2"></i> <?php echo htmlspecialchars($nav_back_title ?? 'Quay lại'); ?>
            </a>
        <?php else: ?>
            <a class="navbar-brand fw-bold text-gradient fs-4" href="index.php">
                <i class="fa-solid fa-earth-asia me-1"></i> SocialAI
            </a>
        <?php endif; ?>

        <div class="d-none d-md-block w-25">
            <form action="search.php" method="GET" class="w-100 m-0">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-0 rounded-end-pill" placeholder="Tìm kiếm bạn bè, bài viết..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                </div>
            </form>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button id="btn-darkmode" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;" title="Dark mode">
                <i class="fa-solid fa-moon"></i>
            </button>

            <div class="dropdown me-1">
                <button class="btn btn-light rounded-circle shadow-sm position-relative" type="button" data-bs-toggle="dropdown" id="bell-btn" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-bell"></i>
                    <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light <?php echo $unread_notif_count > 0 ? '' : 'd-none'; ?>" style="font-size: 0.6rem;">
                        <?php echo $unread_notif_count; ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" id="notif-dropdown" style="width: 340px; max-height: 420px; overflow-y: auto; border-radius: 16px;">
                    <li class="d-flex justify-content-between align-items-center px-3 mb-2">
                        <h6 class="dropdown-header fw-bold text-dark fs-6 mb-0 p-0">Thông báo</h6>
                        <a href="notifications.php" class="small text-primary text-decoration-none">Xem tất cả</a>
                    </li>
                    <div id="notif-dropdown-list">
                    <?php
                    // VIỆT HÓA: Kéo dữ liệu Dropdown từ bảng THONG_BAO và NGUOI_DUNG
                    $sql_notif = "SELECT n.TB_Ma as id, n.TB_Loai as type, n.BV_Ma as post_id, 
                                         n.TB_DaDoc as is_read, n.TB_NgayTao as created_at, 
                                         u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                  FROM THONG_BAO n 
                                  JOIN NGUOI_DUNG u ON n.ND_Ma_Gui = u.ND_Ma 
                                  WHERE n.ND_Ma_Nhan = $uid 
                                  ORDER BY n.TB_NgayTao DESC LIMIT 8";
                    $res_notif = $conn->query($sql_notif);
                    if ($res_notif && $res_notif->num_rows > 0) {
                        while ($notif = $res_notif->fetch_assoc()) {
                            $unread_class = ($notif['is_read'] == 0) ? 'unread-bg' : '';
                            $link = notif_link($notif['type'], $notif['post_id']);
                            $icon = notif_icon_class($notif['type']);
                    ?>
                        <li>
                            <a class="dropdown-item notif-item d-flex align-items-start <?php echo $unread_class; ?>" href="<?php echo $link; ?>" data-notif-id="<?php echo $notif['id']; ?>" style="white-space: normal;">
                                <div class="position-relative me-3 flex-shrink-0">
                                    <img src="<?php echo avatar_url($notif['full_name'], $notif['avatar_url']); ?>" class="notif-icon shadow-sm" alt="">
                                    <i class="<?php echo $icon; ?> position-absolute bottom-0 end-0 bg-white rounded-circle p-1" style="font-size: 0.65rem;"></i>
                                </div>
                                <div>
                                    <span class="text-dark" style="font-size: 0.88rem;"><?php echo '<b>' . htmlspecialchars($notif['full_name']) . '</b> ' . notif_text($notif['type']); ?></span>
                                    <div class="text-primary small" style="font-size: 0.75rem;"><i class="fa-regular fa-clock me-1"></i><?php echo time_ago($notif['created_at']); ?></div>
                                </div>
                            </a>
                        </li>
                    <?php
                        }
                    } else {
                        echo '<li class="text-center text-muted py-4 small" id="notif-empty">Bạn chưa có thông báo nào.</li>';
                    }
                    ?>
                    </div>
                </ul>
            </div>

            <a href="profile.php" class="text-decoration-none">
                <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm">
                    <img src="<?php echo avatar_url($_SESSION['full_name'], $_SESSION['avatar_url'] ?? ''); ?>" class="rounded-circle me-md-2" style="width: 32px; height: 32px; object-fit: cover;">
                    <span class="fw-bold fs-6 pe-2 text-dark text-nowrap d-none d-md-inline-block text-truncate" style="max-width: 120px;">
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </span>
                </div>
            </a>

            <a href="logout.php" class="btn btn-light rounded-circle text-danger ms-1 shadow-sm" style="width: 40px; height: 40px;" title="Đăng xuất">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</nav>
<div id="toast-container" class="toast-notif"> </div>
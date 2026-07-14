<?php
$active_menu = $active_menu ?? '';
?>
<div class="col-md-3 d-none d-md-block left-menu">
    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo $active_menu === 'home' ? 'active' : ''; ?>" href="index.php">
                <i class="fa-solid fa-house fa-fw me-2"></i> Bảng tin
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo $active_menu === 'profile' ? 'active' : ''; ?>" href="profile.php">
                <i class="fa-solid fa-user fa-fw me-2 text-success"></i> Trang cá nhân
            </a>
        </li>
        <li class="nav-item">
             <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo ($active_menu == 'saved') ? 'active' : ''; ?>" href="saved.php">
                <i class="fa-solid fa-bookmark fa-fw me-2"></i> Đã lưu
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo ($active_menu == 'groups') ? 'active' : ''; ?>" href="groups.php">
        <i class="fa-solid fa-users fa-fw me-2 <?php echo ($active_menu == 'groups') ? '' : 'text-warning'; ?>"></i> Nhóm cộng đồng
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo $active_menu === 'friends' ? 'active' : ''; ?>" href="friends.php">
                <i class="fa-solid fa-user-group fa-fw me-2 text-info"></i> Bạn bè
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo $active_menu === 'notifications' ? 'active' : ''; ?>" href="notifications.php">
                <i class="fa-solid fa-bell fa-fw me-2 text-warning"></i> Thông báo
                <?php if (isset($unread_notif_count) && $unread_notif_count > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo $unread_notif_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <hr class="my-2">
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo $active_menu === 'ai' ? 'active' : ''; ?>" href="ai_dashboard.php">
                <i class="fa-solid fa-robot fa-fw me-2 text-purple"></i> Quản lý AI Model
            </a>
        </li>
        <?php if (!empty($is_user_admin)): ?>
        <li class="nav-item">
            <a class="nav-link text-dark rounded-3 px-3 py-2 sidebar-link <?php echo $active_menu === 'admin' ? 'active' : ''; ?>" href="admin.php">
                <i class="fa-solid fa-shield-halved fa-fw me-2 text-danger"></i> Quản trị
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

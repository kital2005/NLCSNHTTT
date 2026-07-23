<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];
$active_menu = 'friends';

// Đã đổi qua bảng THONG_BAO
$notif_count_query = $conn->query("SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $current_user_id AND TB_DaDoc = 0");
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
$is_user_admin = is_admin($conn, $current_user_id);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bạn bè | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <?php include 'includes/styles.php'; ?>
    <style>
        .friend-row-card { border: none; border-radius: 16px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: all 0.2s ease; margin-bottom: 15px;}
        .friend-row-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: #e2e8f0;}
        [data-bs-theme="dark"] .friend-row-card { background-color: #1e293b; color: #f8fafc; }
        .avatar-compact { width: 65px; height: 65px; object-fit: cover; border-radius: 50%; border: 2px solid #f1f5f9;}
        [data-bs-theme="dark"] .avatar-compact { border-color: #334155; }
        .section-title { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; color: #64748b; }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col-md-9">
                <div class="bg-white p-4 rounded-4 shadow-sm" data-bs-theme="light">
                    
                    <h6 class="fw-bold section-title"><i class="fa-solid fa-bell text-primary me-2"></i> Lời mời kết bạn đang chờ</h6>
                    <div class="row mb-5">
                        <?php
                        // Câu query 1: Lấy danh sách chờ kết bạn
                        $sql_requests = "SELECT u.ND_Ma as id, u.ND_TaiKhoan as username, u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                         FROM NGUOI_DUNG u 
                                         JOIN BAN_BE f ON u.ND_Ma = f.ND_Ma_Gui 
                                         WHERE f.ND_Ma_Nhan = $current_user_id AND f.BB_TrangThai = 'pending'";
                        $res_requests = $conn->query($sql_requests);
                        
                        if ($res_requests && $res_requests->num_rows > 0) {
                            while($req = $res_requests->fetch_assoc()) {
                        ?>
                        <div class="col-md-6 user-card-wrapper" id="user-card-<?php echo $req['id']; ?>">
                            <div class="friend-row-card p-3 border">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo !empty($req['avatar_url']) ? $req['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($req['full_name']).'&background=random'; ?>" 
                                         class="avatar-compact me-3" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($req['full_name']); ?>&background=random';">
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($req['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($req['username']); ?></small>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary rounded-pill px-3 fw-bold btn-sm btn-action" data-action="accept" data-id="<?php echo $req['id']; ?>">Đồng ý</button>
                                        <button class="btn btn-light rounded-pill px-3 fw-bold btn-sm btn-action border text-muted" data-action="cancel" data-id="<?php echo $req['id']; ?>">Xóa</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                            echo '<div class="col-12"><p class="text-muted fst-italic px-2">Bạn không có lời mời kết bạn nào.</p></div>';
                        }
                        ?>
                    </div>

                    <h6 class="fw-bold section-title"><i class="fa-solid fa-wand-magic-sparkles text-success me-2"></i> Có thể bạn biết</h6>
                    <div class="row mb-5">
                        <?php
                        // Câu query 2: Gợi ý kết bạn
                        $sql_suggest = "SELECT ND_Ma as id, ND_TaiKhoan as username, ND_HoTen as full_name, ND_AnhDaiDien as avatar_url 
                                        FROM NGUOI_DUNG 
                                        WHERE ND_Ma != $current_user_id AND ND_Ma NOT IN (
                                            SELECT ND_Ma_Gui FROM BAN_BE WHERE ND_Ma_Nhan = $current_user_id
                                            UNION
                                            SELECT ND_Ma_Nhan FROM BAN_BE WHERE ND_Ma_Gui = $current_user_id
                                        ) LIMIT 6";
                        $res_suggest = $conn->query($sql_suggest);
                        
                        if ($res_suggest && $res_suggest->num_rows > 0) {
                            while($sug = $res_suggest->fetch_assoc()) {
                        ?>
                        <div class="col-md-6 user-card-wrapper" id="user-card-<?php echo $sug['id']; ?>">
                            <div class="friend-row-card p-3 border">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo !empty($sug['avatar_url']) ? $sug['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($sug['full_name']).'&background=random'; ?>" 
                                         class="avatar-compact me-3" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($sug['full_name']); ?>&background=random';">
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($sug['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($sug['username']); ?></small>
                                    </div>
                                    
                                    <div>
                                        <button class="btn btn-outline-primary rounded-pill px-3 fw-bold btn-sm btn-action" id="btn-add-<?php echo $sug['id']; ?>" data-action="add" data-id="<?php echo $sug['id']; ?>">
                                            <i class="fa-solid fa-user-plus me-1"></i> Thêm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                            echo '<div class="col-12"><p class="text-muted fst-italic px-2">Tạm thời chưa có gợi ý mới.</p></div>';
                        }
                        ?>
                    </div>

                    <h6 class="fw-bold section-title"><i class="fa-solid fa-address-book text-info me-2"></i> Danh bạ của bạn</h6>
                    <div class="row">
                        <?php
                        // Câu query 3: Danh sách bạn bè hiện tại
                        $sql_friends = "SELECT u.ND_Ma as id, u.ND_TaiKhoan as username, u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                        FROM NGUOI_DUNG u 
                                        JOIN BAN_BE f ON (u.ND_Ma = f.ND_Ma_Gui OR u.ND_Ma = f.ND_Ma_Nhan) 
                                        WHERE (f.ND_Ma_Gui = $current_user_id OR f.ND_Ma_Nhan = $current_user_id) 
                                        AND u.ND_Ma != $current_user_id AND f.BB_TrangThai = 'accepted'";
                        $res_friends = $conn->query($sql_friends);
                        
                        if ($res_friends && $res_friends->num_rows > 0) {
                            while($fr = $res_friends->fetch_assoc()) {
                        ?>
                        <div class="col-md-6 user-card-wrapper" id="user-card-<?php echo $fr['id']; ?>">
                            <div class="friend-row-card p-3 border bg-light">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo !empty($fr['avatar_url']) ? $fr['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($fr['full_name']).'&background=random'; ?>" 
                                         class="avatar-compact me-3" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($fr['full_name']); ?>&background=random';">
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($fr['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($fr['username']); ?></small>
                                    </div>
                                    
                                    <div>
                                        <button class="btn btn-light rounded-pill px-3 fw-bold btn-sm text-danger border btn-action" data-action="unfriend" data-id="<?php echo $fr['id']; ?>">
                                            Hủy bạn
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                            echo '<div class="col-12"><p class="text-muted fst-italic px-2">Bạn chưa có người bạn nào trên hệ thống.</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer_scripts.php'; ?>
    <script>
        document.querySelectorAll('.btn-action').forEach(button => {
            button.addEventListener('click', async function() {
                const action = this.getAttribute('data-action');
                const targetId = this.getAttribute('data-id');
                const cardWrapper = document.getElementById('user-card-' + targetId);
                const btn = this;

                btn.disabled = true;

                try {
                    const response = await fetch('api_friend.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: action, target_id: targetId })
                    });
                    const data = await response.json();

                    if(data.status === 'success') {
                        if(action === 'add') {
                            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Đã gửi';
                            btn.classList.remove('btn-outline-primary');
                            btn.classList.add('btn-secondary');
                        } 
                        else if (action === 'accept' || action === 'cancel' || action === 'unfriend') {
                            cardWrapper.style.transition = "opacity 0.3s, transform 0.3s";
                            cardWrapper.style.opacity = "0";
                            cardWrapper.style.transform = "scale(0.9)";
                            setTimeout(() => { cardWrapper.remove(); }, 300);
                        }
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                    }
                } catch(e) {
                    console.error(e);
                    alert("Có lỗi kết nối mạng!");
                    btn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
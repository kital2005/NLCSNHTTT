<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: groups.php"); exit(); }
$current_user_id = $_SESSION['user_id'];
$group_id = intval($_GET['id']);
$active_menu = 'groups';

// 1. VIỆT HÓA: Lấy thông tin nhóm từ bảng NHOM và THANH_VIEN_NHOM
$sql_group = "SELECT g.N_Ma as id, g.N_Ten as name, g.N_MoTa as description, 
                     g.N_AnhBia as cover_url, g.N_QuyenRiengTu as privacy, 
                     g.N_DuyetBai as require_approval, g.ND_Ma_Tao as creator_id, 
                     (SELECT COUNT(*) FROM THANH_VIEN_NHOM WHERE N_Ma = g.N_Ma AND TVN_VaiTro != 'pending') as member_count 
              FROM NHOM g WHERE g.N_Ma = $group_id";
$group_query = $conn->query($sql_group);

if ($group_query->num_rows == 0) die("Nhóm không tồn tại!");
$group = $group_query->fetch_assoc();

$role = 'none';
// 2. VIỆT HÓA: Kiểm tra vai trò
$sql_role = "SELECT TVN_VaiTro as role FROM THANH_VIEN_NHOM WHERE N_Ma = $group_id AND ND_Ma = $current_user_id";
$role_query = $conn->query($sql_role);
if ($role_query->num_rows > 0) $role = $role_query->fetch_assoc()['role'];

$is_group_creator = ($group['creator_id'] == $current_user_id);

// 3. VIỆT HÓA: Thông báo
$sql_notif = "SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $current_user_id AND TB_DaDoc = 0";
$notif_count_query = $conn->query($sql_notif);
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
$is_user_admin = is_admin($conn, $current_user_id);

// Xử lý Cập nhật Cài đặt Nhóm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings']) && $is_group_creator) {
    $req_app = isset($_POST['require_approval']) ? 1 : 0;
    $priv = $conn->real_escape_string($_POST['privacy']);
    $conn->query("UPDATE NHOM SET N_DuyetBai = $req_app, N_QuyenRiengTu = '$priv' WHERE N_Ma = $group_id");
    header("Location: group_detail.php?id=$group_id"); exit();
}

// Xử lý Duyệt/Xóa bài viết
if (isset($_GET['action']) && isset($_GET['post_id']) && $is_group_creator) {
    $p_id = intval($_GET['post_id']);
    $post_info = $conn->query("SELECT ND_Ma as user_id FROM BAI_VIET WHERE BV_Ma = $p_id")->fetch_assoc();
    
    if ($_GET['action'] == 'approve') {
        $conn->query("UPDATE BAI_VIET SET BV_TrangThai = 'approved' WHERE BV_Ma = $p_id");
        $conn->query("INSERT INTO THONG_BAO (ND_Ma_Nhan, ND_Ma_Gui, TB_Loai, BV_Ma) 
                      VALUES ({$post_info['user_id']}, $current_user_id, 'group_approved', $p_id)");
    } elseif ($_GET['action'] == 'reject') {
        $conn->query("DELETE FROM BAI_VIET WHERE BV_Ma = $p_id AND BV_TrangThai = 'pending'");
    }
    header("Location: group_detail.php?id=$group_id"); exit();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($group['name']); ?> | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .group-hero { height: 300px; object-fit: cover; width: 100%; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; background-color: #cbd5e1; }
        .group-header-card { margin-top: -50px; position: relative; z-index: 10; border-radius: 16px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .status-input { background-color: #f1f5f9; border-radius: 20px; resize: none; }
        [data-bs-theme="dark"] .status-input { background-color: #0f172a; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4 pb-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <div class="col-md-9">
            <!-- HEADER NHÓM -->
            <img src="<?php echo htmlspecialchars($group['cover_url']); ?>" class="group-hero shadow-sm">
            <div class="card group-header-card p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">
                            <?php echo htmlspecialchars($group['name']); ?>
                            <?php if($group['privacy'] == 'private'): ?><i class="fa-solid fa-lock ms-2 text-muted fs-5" title="Nhóm kín"></i><?php endif; ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="fa-solid fa-earth-americas me-1"></i> <?php echo $group['privacy'] == 'public' ? 'Nhóm Công khai' : 'Nhóm Riêng tư'; ?> • <b><?php echo $group['member_count']; ?></b> thành viên
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if($is_group_creator): ?>
                            <!-- NÚT CÀI ĐẶT NHÓM -->
                            <button class="btn btn-light border rounded-pill px-3 text-muted" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-gear"></i></button>
                        <?php endif; ?>
                        
                        <?php if($role === 'none'): ?>
                            <button class="btn btn-primary rounded-pill px-4 fw-bold btn-join-group" data-id="<?php echo $group_id; ?>"><i class="fa-solid fa-right-to-bracket me-1"></i> Tham gia</button>
                        <?php elseif($role === 'pending'): ?>
                            <button class="btn btn-secondary rounded-pill px-4 fw-bold" disabled><i class="fa-solid fa-clock me-1"></i> Chờ duyệt</button>
                        <?php else: ?>
                            <button class="btn btn-light border rounded-pill px-4 fw-bold text-success"><i class="fa-solid fa-check me-1"></i> Đã tham gia</button>
                        <?php endif; ?>
                    </div>
                </div>
                <hr class="my-3 opacity-25">
                
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item"><a class="nav-link active rounded-pill fw-bold" href="#feed">Thảo luận</a></li>
                    <?php if($is_group_creator): ?>
                        <li class="nav-item"><a class="nav-link bg-warning bg-opacity-10 text-warning rounded-pill fw-bold border" href="#pending-posts"><i class="fa-solid fa-file-pen me-1"></i> Bài chờ duyệt</a></li>
                        <?php if($group['privacy'] == 'private'): ?>
                            <li class="nav-item"><a class="nav-link bg-info bg-opacity-10 text-info rounded-pill fw-bold border" href="#pending-members"><i class="fa-solid fa-user-shield me-1"></i> Duyệt thành viên</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- FORM ĐĂNG BÀI BÊ TỪ TRANG CHỦ SANG -->
            <?php if($role === 'member' || $role === 'admin'): ?>
            <div class="card p-3 shadow-sm mb-4 border-0" style="border-radius: 16px;">
                <form action="process_post.php" method="POST" id="post-form" enctype="multipart/form-data">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <input type="hidden" name="privacy" value="public"> 
                    <div class="d-flex mb-2">
                        <img src="<?php echo avatar_url($_SESSION['full_name'], $_SESSION['avatar_url']); ?>" class="rounded-circle me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        <textarea id="post-content" class="form-control border-0 status-input p-3" name="content" rows="2" placeholder="Bạn muốn chia sẻ điều gì?" required></textarea>
                    </div>

                    <div id="ai-preview-box" class="d-none mb-3 p-2 border rounded-3 bg-light position-relative">
                        <button type="button" id="btn-remove-ai" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle"><i class="fa-solid fa-xmark"></i></button>
                        <div id="ai-loading" class="text-center py-4 text-muted"><div class="spinner-border text-primary mb-2"></div><p class="small">AI đang vẽ ảnh...</p></div>
                        <div id="ai-result" class="d-none">
                            <span id="ai-topic-badge" class="badge bg-primary mb-2"></span>
                            <img id="ai-image-preview" src="" class="img-fluid rounded-3 w-100">
                        </div>
                        <input type="hidden" name="ai_topic" id="hidden_ai_topic">
                        <input type="hidden" name="ai_sentiment" id="hidden_ai_sentiment">
                        <input type="hidden" name="ai_image_url" id="hidden_ai_image_url">
                    </div>

                    <div id="post-image-preview" class="d-none mb-3 position-relative">
                        <button type="button" id="btn-remove-image" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle"><i class="fa-solid fa-xmark"></i></button>
                        <img id="post-image-preview-img" src="" class="img-fluid rounded-3 w-100">
                    </div>
                    <input type="file" name="post_image" id="post-image-input" class="d-none" accept="image/*">
                    
                    <hr class="text-muted opacity-25">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-upload-image" class="btn btn-light text-muted fw-bold rounded-pill px-3"><i class="fa-solid fa-image text-success"></i> Thêm ảnh</button>
                            <button type="button" id="btn-ai-draw" class="btn btn-light text-muted fw-bold rounded-pill px-3"><i class="fa-solid fa-wand-magic-sparkles text-purple"></i> AI Vẽ</button>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Đăng bài</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- CHỨC NĂNG DUYỆT THÀNH VIÊN -->
            <?php if($is_group_creator && $group['privacy'] == 'private'): ?>
            <div id="pending-members" class="mb-4">
                <?php
                // 4. VIỆT HÓA: Thành viên chờ duyệt
                $sql_pend_mem = "SELECT gm.ND_Ma as user_id, u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url, gm.TVN_NgayThamGia as joined_at 
                                 FROM THANH_VIEN_NHOM gm 
                                 JOIN NGUOI_DUNG u ON gm.ND_Ma = u.ND_Ma 
                                 WHERE gm.N_Ma = $group_id AND gm.TVN_VaiTro = 'pending'";
                $res_pend_mem = $conn->query($sql_pend_mem);
                
                if($res_pend_mem && $res_pend_mem->num_rows > 0) {
                    echo '<h5 class="fw-bold text-info mb-3"><i class="fa-solid fa-user-shield me-2"></i> Thành viên chờ duyệt</h5>';
                    echo '<div class="card p-3 shadow-sm border border-info mb-4">';
                    while($pm = $res_pend_mem->fetch_assoc()) {
                ?>
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" id="req-<?php echo $pm['user_id']; ?>">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo avatar_url($pm['full_name'], $pm['avatar_url']); ?>" class="rounded-circle me-3" width="45" height="45" style="object-fit:cover;">
                            <div>
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($pm['full_name']); ?></h6>
                                <small class="text-muted">Gửi yêu cầu lúc: <?php echo date('H:i d/m', strtotime($pm['joined_at'])); ?></small>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold btn-handle-member" data-action="approve_member" data-member="<?php echo $pm['user_id']; ?>">Duyệt</button>
                            <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold border btn-handle-member" data-action="reject_member" data-member="<?php echo $pm['user_id']; ?>">Từ chối</button>
                        </div>
                    </div>
                <?php } echo '</div>'; } ?>
            </div>
            <?php endif; ?>

            <!-- KHU VỰC BÀI CHỜ DUYỆT -->
            <?php if($is_group_creator): ?>
            <div id="pending-posts" class="mb-4">
                <?php
                // 5. VIỆT HÓA: Bài chờ duyệt
                $sql_pending = "SELECT p.BV_Ma as id, p.BV_NoiDung as content, p.BV_HinhAnh as image_url, 
                                       p.BV_HinhAnhAI as generated_image_url, p.BV_NgayDang as created_at, 
                                       u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                FROM BAI_VIET p JOIN NGUOI_DUNG u ON p.ND_Ma = u.ND_Ma 
                                WHERE p.N_Ma = $group_id AND p.BV_TrangThai = 'pending' 
                                ORDER BY p.BV_NgayDang ASC";
                $res_pending = $conn->query($sql_pending);
                if($res_pending && $res_pending->num_rows > 0) {
                    echo '<h5 class="fw-bold text-warning mb-3"><i class="fa-solid fa-clock me-2"></i> Bài viết chờ duyệt</h5>';
                    while($pend = $res_pending->fetch_assoc()) {
                ?>
                    <div class="card p-4 shadow-sm border border-warning mb-3 rounded-4">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo avatar_url($pend['full_name'], $pend['avatar_url']); ?>" class="rounded-circle me-3 shadow-sm" width="50" height="50" style="object-fit:cover;">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark fs-5"><?php echo htmlspecialchars($pend['full_name']); ?></h6>
                                <small class="text-muted"><?php echo date('H:i d/m/Y', strtotime($pend['created_at'])); ?></small>
                            </div>
                        </div>
                        
                        <p class="text-dark fs-5" style="white-space: pre-wrap;"><?php echo format_post_content($pend['content']); ?></p>
                        
                        <?php if (!empty($pend['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($pend['image_url']); ?>" class="img-fluid rounded-3 border mb-3 w-100">
                        <?php endif; ?>
                        <?php if (!empty($pend['generated_image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($pend['generated_image_url']); ?>" class="img-fluid rounded-3 border mb-3 w-100">
                        <?php endif; ?>
                        
                        <div class="d-flex gap-2 border-top pt-3">
                            <a href="group_detail.php?id=<?php echo $group_id; ?>&action=approve&post_id=<?php echo $pend['id']; ?>" class="btn btn-success rounded-pill fw-bold px-4">Phê duyệt</a>
                            <a href="group_detail.php?id=<?php echo $group_id; ?>&action=reject&post_id=<?php echo $pend['id']; ?>" class="btn btn-outline-danger rounded-pill fw-bold px-4" onclick="return confirm('Bạn có muốn từ chối và xóa bài viết này không?');">Từ chối</a>
                        </div>
                    </div>
                <?php }} ?>
            </div>
            <?php endif; ?>

            <!-- HIỂN THỊ BÀI VIẾT NHÓM -->
            <h5 id="feed" class="fw-bold text-dark mb-3"><i class="fa-solid fa-layer-group me-2 text-primary"></i> Thảo luận nhóm</h5>
            <?php
            if ($role == 'none' && $group['privacy'] == 'private' && !$is_group_creator) {
                echo '<div class="card p-5 text-center bg-light border-0"><i class="fa-solid fa-lock fa-3x text-muted opacity-50 mb-3"></i><h5 class="text-muted fw-bold">Đây là nhóm Riêng tư</h5><p class="text-muted mb-0">Bạn cần tham gia nhóm để xem các bài viết thảo luận.</p></div>';
            } else {
                // 6. VIỆT HÓA: Tường bài viết
                $sql_feed = "SELECT p.BV_Ma as id, p.ND_Ma as user_id, p.BV_NoiDung as content, p.BV_HinhAnh as image_url, 
                                    p.BV_HinhAnhAI as generated_image_url, p.BV_NgayDang as created_at, 
                                    u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url, 
                                    (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = p.BV_Ma) as like_count, 
                                    (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = p.BV_Ma AND ND_Ma = $current_user_id) as user_liked, 
                                    (SELECT COUNT(*) FROM BINH_LUAN WHERE BV_Ma = p.BV_Ma) as comment_count 
                             FROM BAI_VIET p JOIN NGUOI_DUNG u ON p.ND_Ma = u.ND_Ma 
                             WHERE p.N_Ma = $group_id AND p.BV_TrangThai = 'approved' 
                             ORDER BY p.BV_NgayDang DESC";
                
                $res_feed = $conn->query($sql_feed);
                if ($res_feed && $res_feed->num_rows > 0) {
                    while($post = $res_feed->fetch_assoc()) {
                        $is_liked_class = ($post['user_liked'] > 0) ? 'liked' : '';
                        $like_icon = ($post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular';
            ?>
                    <div class="card p-3 shadow-sm border-0 mb-4" id="post-<?php echo $post['id']; ?>" style="border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo avatar_url($post['full_name'], $post['avatar_url']); ?>" class="rounded-circle me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                    <div class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?> • <i class="fa-solid fa-users text-primary"></i></div>
                                </div>
                            </div>
                            <?php if ($post['user_id'] == $current_user_id || $is_group_creator): ?>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle text-muted" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <?php if ($post['user_id'] == $current_user_id): ?>
                                    <li><a class="dropdown-item py-2" href="edit_post.php?id=<?php echo $post['id']; ?>"><i class="fa-solid fa-pen me-2"></i> Chỉnh sửa</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item text-danger py-2" href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Xóa bài?');"><i class="fa-solid fa-trash me-2"></i> Xóa</a></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p class="mb-2 mt-2 text-dark" style="white-space: pre-wrap;"><?php echo format_post_content($post['content']); ?></p>
                        <?php if (!empty($post['image_url'])): ?> <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="img-fluid rounded-3 border mb-2 w-100"> <?php endif; ?>
                        <?php if (!empty($post['generated_image_url'])): ?> <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid rounded-3 border mb-2 w-100"> <?php endif; ?>
                        
                        <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                            <span><i class="fa-solid fa-thumbs-up text-primary me-2"></i> <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['like_count']; ?></span> lượt thích</span>
                            <span><span id="comment-count-<?php echo $post['id']; ?>"><?php echo $post['comment_count']; ?></span> bình luận</span>
                        </div>
                        <hr class="my-2 opacity-25">
                        <div class="d-flex justify-content-between px-md-2">
                            <button class="btn btn-light fw-bold flex-grow-1 me-1 text-muted btn-like <?php echo $is_liked_class; ?>" data-post-id="<?php echo $post['id']; ?>"><i class="<?php echo $like_icon; ?> fa-thumbs-up me-1" id="like-icon-<?php echo $post['id']; ?>"></i> Thích</button>
                            <button class="btn btn-light fw-bold flex-grow-1 mx-1 text-muted" onclick="document.getElementById('comment-input-<?php echo $post['id']; ?>').focus();"><i class="fa-regular fa-message me-1"></i> Bình luận</button>
                        </div>
                        <hr class="my-2 opacity-25">
                        <div class="mt-3">
                            <div id="comment-list-<?php echo $post['id']; ?>">
                                <?php
                                // 7. VIỆT HÓA: Load bình luận
                                $sql_cmts = "SELECT c.BL_Ma as id, c.BL_NoiDung as content, c.BL_NgayBinhLuan as created_at, 
                                                    u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                             FROM BINH_LUAN c JOIN NGUOI_DUNG u ON c.ND_Ma = u.ND_Ma 
                                             WHERE c.BV_Ma = {$post['id']} ORDER BY c.BL_NgayBinhLuan ASC";
                                $res_cmts = $conn->query($sql_cmts);
                                if ($res_cmts && $res_cmts->num_rows > 0) {
                                    while($cmt = $res_cmts->fetch_assoc()) {
                                        echo '<div class="d-flex mb-3"><img src="'.avatar_url($cmt['full_name'], $cmt['avatar_url']).'" class="rounded-circle me-2 mt-1" style="width:32px; height:32px; object-fit:cover;"><div class="comment-box"><h6 class="mb-0 fw-bold fs-6 text-dark">'.htmlspecialchars($cmt['full_name']).'</h6><p class="mb-0 text-dark" style="font-size: 0.95rem;">'.htmlspecialchars($cmt['content']).'</p></div></div>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="d-flex mt-2">
                                <img src="<?php echo avatar_url($_SESSION['full_name'], $_SESSION['avatar_url']); ?>" class="rounded-circle me-2 mt-1 shadow-sm" style="width:32px; height:32px; object-fit:cover;">
                                <form class="flex-grow-1 d-flex form-comment" data-post-id="<?php echo $post['id']; ?>">
                                    <input type="text" id="comment-input-<?php echo $post['id']; ?>" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Viết bình luận..." required>
                                    <button type="submit" class="btn btn-primary rounded-circle ms-2" style="width: 40px; height: 40px;"><i class="fa-solid fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php } } else { echo '<div class="card p-5 text-center bg-light border-0"><p class="text-muted fw-bold mb-0">Nhóm này chưa có bài viết nào được duyệt.</p></div>'; } } ?>
        </div>
    </div>
</div>

<!-- MODAL CÀI ĐẶT NHÓM -->
<?php if($is_group_creator): ?>
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Cài đặt Nhóm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="group_detail.php?id=<?php echo $group_id; ?>" method="POST">
                    <input type="hidden" name="update_settings" value="1">
                    
                    <div class="mb-4">
                        <label class="fw-bold mb-2">Quyền riêng tư</label>
                        <select name="privacy" class="form-select border-0 bg-light">
                            <option value="public" <?php if($group['privacy'] == 'public') echo 'selected'; ?>>Công khai (Ai cũng thấy bài)</option>
                            <option value="private" <?php if($group['privacy'] == 'private') echo 'selected'; ?>>Riêng tư (Chỉ thành viên mới thấy)</option>
                        </select>
                    </div>

                    <div class="mb-4 form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="require_approval" id="reqApp" <?php if($group['require_approval'] == 1) echo 'checked'; ?>>
                        <label class="form-check-label fs-6 fw-bold ms-2" for="reqApp">Bật phê duyệt bài viết</label>
                        <div class="text-muted small mt-1 fs-6">Thành viên đăng bài sẽ phải chờ bạn duyệt mới được hiển thị.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Lưu Cài Đặt</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer_scripts.php'; ?>
<script>
    // JS Cho Form ảnh và AI
    const btnUploadImage = document.getElementById('btn-upload-image');
    if(btnUploadImage) {
        btnUploadImage.addEventListener('click', () => document.getElementById('post-image-input').click());
        document.getElementById('post-image-input').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const r = new FileReader(); r.onload = ev => { document.getElementById('post-image-preview-img').src = ev.target.result; document.getElementById('post-image-preview').classList.remove('d-none'); };
                r.readAsDataURL(e.target.files[0]);
            }
        });
        document.getElementById('btn-remove-image').addEventListener('click', function() { document.getElementById('post-image-input').value=''; document.getElementById('post-image-preview').classList.add('d-none');});
    }

    const btnAiDraw = document.getElementById('btn-ai-draw');
    if (btnAiDraw) {
        btnAiDraw.addEventListener('click', async function() {
            const content = document.getElementById('post-content').value;
            if (!content.trim()) return alert('Vui lòng nhập nội dung!');
            document.getElementById('ai-preview-box').classList.remove('d-none'); document.getElementById('ai-loading').classList.remove('d-none'); document.getElementById('ai-result').classList.add('d-none');
            this.disabled = true;
            try {
                const res = await fetch('api_generate.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ content: content }) });
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('ai-loading').classList.add('d-none'); document.getElementById('ai-result').classList.remove('d-none');
                    document.getElementById('ai-image-preview').src = data.image_url; document.getElementById('ai-topic-badge').innerHTML = '<i class="fa-solid fa-microchip me-1"></i> ' + data.topic;
                    document.getElementById('hidden_ai_topic').value = data.topic; document.getElementById('hidden_ai_image_url').value = data.image_url;
                } else { alert('Lỗi: ' + data.message); document.getElementById('ai-preview-box').classList.add('d-none'); }
            } catch (e) { alert('Có lỗi mạng!'); document.getElementById('ai-preview-box').classList.add('d-none'); } finally { this.disabled = false; }
        });
        document.getElementById('btn-remove-ai').addEventListener('click', function() { document.getElementById('ai-preview-box').classList.add('d-none'); document.getElementById('hidden_ai_topic').value = ''; document.getElementById('hidden_ai_image_url').value = ''; });
    }

    // JS Các nút chức năng
    document.querySelectorAll('.btn-join-group').forEach(btn => {
        btn.addEventListener('click', async function() {
            const groupId = this.dataset.id; this.disabled = true;
            try {
                const res = await fetch('api_group.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'join', group_id: groupId }) });
                const data = await res.json();
                if(data.status === 'pending') { alert(data.message); location.reload(); } else if(data.status === 'success') location.reload();
            } catch(e) { console.error(e); this.disabled = false; }
        });
    });

    document.querySelectorAll('.btn-handle-member').forEach(btn => {
        btn.addEventListener('click', async function() {
            const action = this.dataset.action; const memberId = this.dataset.member; const groupId = <?php echo $group_id; ?>;
            try {
                const res = await fetch('api_group.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: action, group_id: groupId, member_id: memberId }) });
                const data = await res.json();
                if(data.status === 'success') { document.getElementById('req-' + memberId).remove(); }
            } catch(e) { console.error(e); }
        });
    });

    document.querySelectorAll('.btn-like').forEach(b => {
        b.addEventListener('click', async function() {
            const postId = this.getAttribute('data-post-id'); const icon = document.getElementById('like-icon-' + postId); const countSpan = document.getElementById('like-count-' + postId);
            try {
                const res = await fetch('api_like.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ post_id: postId }) });
                const data = await res.json();
                if(data.status === 'success') {
                    countSpan.innerText = data.likes;
                    if(data.action === 'liked') { this.classList.add('liked'); icon.classList.replace('fa-regular', 'fa-solid'); } else { this.classList.remove('liked'); icon.classList.replace('fa-solid', 'fa-regular'); }
                }
            } catch(e) {}
        });
    });

    document.querySelectorAll('.form-comment').forEach(f => {
        f.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            const postId = this.getAttribute('data-post-id'); const inputField = document.getElementById('comment-input-' + postId); const content = inputField.value;
            if (!content.trim()) return;
            try {
                const res = await fetch('api_comment.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ post_id: postId, content: content }) });
                const data = await res.json();
                if (data.status === 'success') location.reload();
            } catch (err) {} 
        });
    });
</script>
</body>
</html>
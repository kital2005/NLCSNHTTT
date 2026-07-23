<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];
$active_menu = 'home';

// Đã Việt hóa: Cập nhật Thông báo
$sql_notif = "SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $current_user_id AND TB_DaDoc = 0";
$notif_count_query = $conn->query($sql_notif);
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
$is_user_admin = is_admin($conn, $current_user_id);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialAI - Cộng đồng trực tuyến</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <?php include 'includes/styles.php'; ?>
    <style>
        .status-input { background-color: #f1f5f9; border-radius: 20px; resize: none; }
        [data-bs-theme="dark"] .status-input { background-color: #0f172a; }
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
        .hover-underline:hover { text-decoration: underline !important; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

    <div class="container mt-4 pb-5">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col-md-6">
                <!-- KHU VỰC ĐĂNG BÀI -->
                <div class="card p-3 shadow-sm mb-4">
                    <form action="process_post.php" method="POST" id="post-form" enctype="multipart/form-data">
                        <div class="d-flex mb-2">
                            <img src="<?php echo !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']).'&background=random'; ?>" 
                                 class="rounded-circle me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0;"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random';">
                            
                            <textarea id="post-content" class="form-control border-0 status-input p-3" name="content" rows="2" placeholder="<?php echo htmlspecialchars($_SESSION['full_name']); ?> ơi, bạn đang nghĩ gì?" required></textarea>
                        </div>
                        
                        <!-- KHU VỰC PREVIEW AI -->
                        <div id="ai-preview-box" class="d-none mb-3 p-2 border rounded-3 bg-light position-relative">
                            <button type="button" id="btn-remove-ai" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle"><i class="fa-solid fa-xmark"></i></button>
                            <div id="ai-loading" class="text-center py-4 text-muted">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <p class="mb-0 small">AI đang vẽ ảnh, vui lòng đợi vài giây...</p>
                            </div>
                            <div id="ai-result" class="d-none">
                                <span id="ai-topic-badge" class="badge bg-primary mb-2"></span>
                                <img id="ai-image-preview" src="" class="img-fluid rounded-3 w-100" alt="Xem trước">
                            </div>
                            <input type="hidden" name="ai_topic" id="hidden_ai_topic" value="">
                            <input type="hidden" name="ai_sentiment" id="hidden_ai_sentiment" value="">
                            <input type="hidden" name="ai_image_url" id="hidden_ai_image_url" value="">
                        </div>

                        <!-- KHU VỰC PREVIEW ẢNH UPLOAD -->
                        <div id="post-image-preview" class="d-none mb-3 position-relative">
                            <button type="button" id="btn-remove-image" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle"><i class="fa-solid fa-xmark"></i></button>
                            <img id="post-image-preview-img" src="" class="img-fluid rounded-3 w-100" alt="Preview">
                        </div>
                        <input type="file" name="post_image" id="post-image-input" class="d-none" accept="image/*">

                        <hr class="text-muted opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-upload-image" class="btn btn-light text-muted fw-bold rounded-pill px-3" title="Thêm ảnh"><i class="fa-solid fa-image text-success"></i> Thêm ảnh</button>
                                <button type="button" id="btn-ai-draw" class="btn btn-ai rounded-pill px-3 shadow-sm"><i class="fa-solid fa-wand-magic-sparkles"></i> <span class="ms-1 d-none d-sm-inline">AI Vẽ</span></button>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2">
                                <select name="privacy" class="form-select form-select-sm bg-light border-0 text-muted fw-bold privacy-select" style="width: auto; cursor: pointer;">
                                    <option value="public" selected>&#xf0ac; Công khai</option>
                                    <option value="friends">&#xf0c0; Bạn bè</option>
                                    <option value="private">&#xf023; Chỉ mình tôi</option>
                                </select>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Đăng</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- HIỂN THỊ BẢNG TIN (ĐÃ VIỆT HÓA TOÀN BỘ) -->
                <?php
                // Đã chuyển đổi sang bảng Tiếng Việt và bẻ nhỏ xuống dòng
                $sql_posts = "SELECT posts.BV_Ma as id, posts.ND_Ma as user_id, posts.BV_NoiDung as content, 
                                     posts.BV_QuyenRiengTu as privacy, posts.BV_HinhAnh as image_url, 
                                     posts.BV_HinhAnhAI as generated_image_url, posts.BV_NgayDang as created_at, 
                                     posts.BV_MaChiaSe as shared_post_id, posts.N_Ma as group_id, 
                                     posts.BV_ChuDeAI as ai_topic, 
                                     users.ND_HoTen as full_name, users.ND_AnhDaiDien as avatar_url,
                                     (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = posts.BV_Ma) as like_count,
                                     (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = posts.BV_Ma AND ND_Ma = $current_user_id) as user_liked,
                                     (SELECT COUNT(*) FROM BINH_LUAN WHERE BV_Ma = posts.BV_Ma) as comment_count,
                                     (SELECT COUNT(*) FROM BAI_VIET_DA_LUU WHERE BV_Ma = posts.BV_Ma AND ND_Ma = $current_user_id) as is_saved,
                                     sp.BV_NoiDung as shared_content, sp.BV_HinhAnh as shared_image_url, 
                                     sp.BV_HinhAnhAI as shared_generated_image_url, sp.BV_ChuDeAI as shared_ai_topic, 
                                     sp.BV_NgayDang as shared_created_at,
                                     su.ND_HoTen as shared_full_name, su.ND_AnhDaiDien as shared_avatar_url,
                                     g.N_Ten as group_name
                              FROM BAI_VIET posts 
                              JOIN NGUOI_DUNG users ON posts.ND_Ma = users.ND_Ma 
                              LEFT JOIN BAI_VIET sp ON posts.BV_MaChiaSe = sp.BV_Ma
                              LEFT JOIN NGUOI_DUNG su ON sp.ND_Ma = su.ND_Ma
                              LEFT JOIN NHOM g ON posts.N_Ma = g.N_Ma
                              WHERE posts.BV_TrangThai = 'approved' AND (
                                  (posts.N_Ma IS NULL AND (
                                     posts.BV_QuyenRiengTu = 'public' 
                                     OR posts.ND_Ma = $current_user_id
                                     OR (posts.BV_QuyenRiengTu = 'friends' AND posts.ND_Ma IN (
                                         SELECT ND_Ma_Gui FROM BAN_BE WHERE ND_Ma_Nhan = $current_user_id AND BB_TrangThai = 'accepted'
                                         UNION
                                         SELECT ND_Ma_Nhan FROM BAN_BE WHERE ND_Ma_Gui = $current_user_id AND BB_TrangThai = 'accepted'
                                     ))
                                  ))
                                  OR 
                                  (posts.N_Ma IS NOT NULL AND posts.N_Ma IN (
                                      SELECT N_Ma FROM THANH_VIEN_NHOM WHERE ND_Ma = $current_user_id AND TVN_VaiTro IN ('member', 'admin')
                                  ))
                              )
                              ORDER BY posts.BV_NgayDang DESC";

                $result_posts = $conn->query($sql_posts);

                if ($result_posts && $result_posts->num_rows > 0) {
                    while($post = $result_posts->fetch_assoc()) {
                        $privacy_icon = 'fa-earth-asia';
                        if ($post['privacy'] == 'friends') $privacy_icon = 'fa-user-group';
                        if ($post['privacy'] == 'private') $privacy_icon = 'fa-lock';
                        
                        $is_liked_class = ($post['user_liked'] > 0) ? 'liked' : '';
                        $like_icon = ($post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular';
                        
                        // Lấy danh sách người đã like (Tiếng Việt)
                        $likers_sql = "SELECT u.ND_HoTen as full_name FROM LUOT_THICH l 
                                       JOIN NGUOI_DUNG u ON l.ND_Ma = u.ND_Ma 
                                       WHERE l.BV_Ma = {$post['id']} ORDER BY l.LT_NgayThich DESC LIMIT 2";
                        $likers_res = $conn->query($likers_sql);
                        $liker_names = [];
                        while($liker = $likers_res->fetch_assoc()) {
                            $liker_names[] = ($liker['full_name'] == $_SESSION['full_name']) ? "Bạn" : $liker['full_name'];
                        }
                        $like_text = "";
                        if ($post['like_count'] > 0) {
                            $like_text = implode(", ", $liker_names);
                            if ($post['like_count'] > count($liker_names)) {
                                $like_text .= " và " . ($post['like_count'] - count($liker_names)) . " người khác";
                            }
                        }
                ?>
                        <div class="card p-3 shadow-sm border-0 mb-4" id="post-<?php echo $post['id']; ?>" style="border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo !empty($post['avatar_url']) ? $post['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($post['full_name']).'&background=random'; ?>" 
                                         class="rounded-circle me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($post['full_name']); ?>&background=random';">

                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                            <a href="profile.php?user_id=<?php echo $post['user_id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($post['full_name']); ?></a>
                                            <!-- HIỂN THỊ TÊN NHÓM NẾU BÀI VIẾT THUỘC VỀ NHÓM -->
                                            <?php if (!empty($post['group_id'])): ?>
                                                <i class="fa-solid fa-caret-right text-muted mx-2" style="font-size: 0.8rem;"></i>
                                                <a href="group_detail.php?id=<?php echo $post['group_id']; ?>" class="text-dark text-decoration-none hover-underline"><?php echo htmlspecialchars($post['group_name']); ?></a>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="text-muted d-flex align-items-center" style="font-size: 0.85rem;">
                                            <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?> 
                                            <span class="mx-1">•</span>
                                            <?php if (!empty($post['group_id'])): ?>
                                                <i class="fa-solid fa-users text-primary" title="Đăng trong nhóm"></i>
                                            <?php else: ?>
                                                <i class="fa-solid <?php echo $privacy_icon; ?>" title="Quyền riêng tư"></i>
                                            <?php endif; ?>
                                            
                                            <?php if($post['shared_post_id']): ?>
                                                <span class="mx-1">•</span>
                                                <i class="fa-solid fa-share text-primary" title="Bài chia sẻ"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($post['ai_topic'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-microchip me-1"></i> <?php echo htmlspecialchars($post['ai_topic']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle text-muted" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <!-- Nút LƯU BÀI VIẾT -->
                                            <?php if ($post['is_saved'] > 0): ?>
                                                <li><a class="dropdown-item btn-save-post" href="javascript:void(0)" data-post-id="<?php echo $post['id']; ?>"><i class="fa-solid fa-bookmark text-primary me-2"></i> Bỏ lưu bài viết</a></li>
                                            <?php else: ?>
                                                <li><a class="dropdown-item btn-save-post" href="javascript:void(0)" data-post-id="<?php echo $post['id']; ?>"><i class="fa-regular fa-bookmark me-2"></i> Lưu bài viết</a></li>
                                            <?php endif; ?>

                                            <?php if ($post['user_id'] == $current_user_id): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item py-2" href="edit_post.php?id=<?php echo $post['id']; ?>"><i class="fa-solid fa-pen me-2"></i> Chỉnh sửa bài</a></li>
                                                <li><a class="dropdown-item text-danger py-2" href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');"><i class="fa-solid fa-trash me-2"></i> Xóa bài viết</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mb-2 mt-2 text-dark" style="white-space: pre-wrap;"><?php echo format_post_content($post['content']); ?></p>

                            <!-- BÀI GỐC NẾU ĐÂY LÀ BÀI SHARE -->
                            <?php if (!empty($post['shared_post_id']) && !empty($post['shared_full_name'])): ?>
                                <div class="border rounded-4 p-3 mt-2 mb-3 bg-light shadow-sm">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?php echo !empty($post['shared_avatar_url']) ? $post['shared_avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($post['shared_full_name']).'&background=random'; ?>" 
                                             class="rounded-circle me-2 shadow-sm" style="width: 30px; height: 30px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark fs-6"><?php echo htmlspecialchars($post['shared_full_name']); ?></h6>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('d/m/Y H:i', strtotime($post['shared_created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <p class="mb-2 text-dark" style="white-space: pre-wrap; font-size: 0.95rem;"><?php echo format_post_content($post['shared_content']); ?></p>
                                    
                                    <?php if (!empty($post['shared_image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['shared_image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="Post image">
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($post['shared_generated_image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['shared_generated_image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="AI Generated Image">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($post['image_url']) && empty($post['shared_post_id'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="Post image">
                            <?php endif; ?>
                            
                            <?php if (!empty($post['generated_image_url']) && empty($post['shared_post_id'])): ?>
                                <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="AI Generated Image">
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                                <span class="d-flex align-items-center">
                                    <i class="fa-solid fa-thumbs-up text-primary me-2 bg-primary bg-opacity-10 p-1 rounded-circle"></i> 
                                    <span id="like-text-<?php echo $post['id']; ?>">
                                        <?php echo $post['like_count'] > 0 ? htmlspecialchars($like_text) : "0 lượt thích"; ?>
                                    </span>
                                    <span id="like-count-<?php echo $post['id']; ?>" class="d-none"><?php echo $post['like_count']; ?></span>
                                </span>
                                <span><span id="comment-count-<?php echo $post['id']; ?>"><?php echo $post['comment_count']; ?></span> bình luận</span>
                            </div>

                            <hr class="my-2 opacity-25">
                            
                            <div class="d-flex justify-content-between px-md-2">
                                <button class="btn btn-light fw-bold flex-grow-1 me-1 text-muted btn-like <?php echo $is_liked_class; ?>" data-post-id="<?php echo $post['id']; ?>">
                                    <i class="<?php echo $like_icon; ?> fa-thumbs-up me-1" id="like-icon-<?php echo $post['id']; ?>"></i> Thích
                                </button>
                                <button class="btn btn-light fw-bold flex-grow-1 mx-1 text-muted" onclick="document.getElementById('comment-input-<?php echo $post['id']; ?>').focus();">
                                    <i class="fa-regular fa-message me-1"></i> Bình luận
                                </button>
                                <!-- NÚT CHIA SẺ -->
                                <button class="btn btn-light fw-bold flex-grow-1 ms-1 text-muted btn-share-post" data-post-id="<?php echo $post['shared_post_id'] ? $post['shared_post_id'] : $post['id']; ?>">
                                    <i class="fa-solid fa-share me-1"></i> Chia sẻ
                                </button>
                            </div>
                            
                            <hr class="my-2 opacity-25">
                            
                            <div class="mt-3">
                                <div id="comment-list-<?php echo $post['id']; ?>">
                                    <?php
                                    $post_id_cmt = $post['id'];
                                    // Việt hóa bảng BINH_LUAN
                                    $sql_cmts = "SELECT c.BL_Ma as id, c.BL_NoiDung as content, c.BL_NgayBinhLuan as created_at, 
                                                        u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                                 FROM BINH_LUAN c JOIN NGUOI_DUNG u ON c.ND_Ma = u.ND_Ma 
                                                 WHERE c.BV_Ma = $post_id_cmt ORDER BY c.BL_NgayBinhLuan ASC";
                                    $res_cmts = $conn->query($sql_cmts);
                                    if ($res_cmts && $res_cmts->num_rows > 0) {
                                        while($cmt = $res_cmts->fetch_assoc()) {
                                    ?>
                                        <div class="d-flex mb-3">
                                            <img src="<?php echo !empty($cmt['avatar_url']) ? $cmt['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($cmt['full_name']).'&background=random'; ?>" 
                                                 class="rounded-circle me-2 mt-1" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($cmt['full_name']); ?>&background=random';">
                                            
                                            <div class="comment-box">
                                                <h6 class="mb-0 fw-bold fs-6 text-dark"><?php echo htmlspecialchars($cmt['full_name']); ?></h6>
                                                <p class="mb-0 text-dark" style="font-size: 0.95rem;"><?php echo htmlspecialchars($cmt['content']); ?></p>
                                            </div>
                                        </div>
                                    <?php 
                                        } 
                                    } 
                                    ?>
                                </div>
                                
                                <div class="d-flex mt-2">
                                    <img src="<?php echo !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']).'&background=random'; ?>" 
                                         class="rounded-circle me-2 mt-1 shadow-sm" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random';">

                                    <form class="flex-grow-1 d-flex form-comment" data-post-id="<?php echo $post['id']; ?>">
                                        <input type="text" id="comment-input-<?php echo $post['id']; ?>" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Viết bình luận..." required>
                                        <button type="submit" class="btn btn-primary rounded-circle ms-2" style="width: 40px; height: 40px;"><i class="fa-solid fa-paper-plane"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                <?php 
                    } 
                } else {
                    echo '<div class="card p-5 text-center border-0 bg-transparent shadow-none mt-4"><i class="fa-solid fa-newspaper fa-4x text-muted opacity-25 mb-3"></i><h5 class="text-muted fw-bold">Bảng tin đang trống</h5><p class="text-muted">Hãy kết bạn hoặc chia sẻ bài viết đầu tiên!</p></div>';
                }
                ?>
            </div>

            <!-- CỘT PHẢI: NGƯỜI LIÊN HỆ -->
            <div class="col-md-3 d-none d-md-block right-menu">
                <div class="card p-3 shadow-sm border-0" style="border-radius: 16px;">
                    <h6 class="fw-bold text-dark mb-3">Người liên hệ</h6>
                    <ul class="list-unstyled mb-0">
                        <?php
                        // Việt hóa phần Danh bạ Bạn bè
                        $sql_contacts = "SELECT u.ND_Ma as id, u.ND_HoTen as full_name, u.ND_AnhDaiDien as avatar_url 
                                         FROM NGUOI_DUNG u 
                                         JOIN BAN_BE f ON (u.ND_Ma = f.ND_Ma_Gui OR u.ND_Ma = f.ND_Ma_Nhan) 
                                         WHERE (f.ND_Ma_Gui = $current_user_id OR f.ND_Ma_Nhan = $current_user_id) 
                                         AND u.ND_Ma != $current_user_id AND f.BB_TrangThai = 'accepted' LIMIT 10";
                        $res_contacts = $conn->query($sql_contacts);
                        if ($res_contacts && $res_contacts->num_rows > 0) {
                            while($contact = $res_contacts->fetch_assoc()) {
                        ?>
                        <li class="d-flex align-items-center mb-3 p-1 rounded custom-hover" style="cursor: pointer;" onclick="location.href='profile.php?user_id=<?php echo $contact['id']; ?>'">
                            <img src="<?php echo !empty($contact['avatar_url']) ? $contact['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($contact['full_name']).'&background=random'; ?>" 
                                 class="rounded-circle me-2 object-fit-cover shadow-sm" width="35" height="35" style="object-fit: cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($contact['full_name']); ?>&background=random';">
                            <span class="fw-bold fs-6 text-dark text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($contact['full_name']); ?></span>
                            <div class="bg-success rounded-circle ms-auto" style="width: 8px; height: 8px;"></div>
                        </li>
                        <?php 
                            }
                        } else {
                            echo '<li class="text-muted small fst-italic text-center py-3">Bạn chưa có người liên hệ nào. <br><a href="friends.php" class="text-primary text-decoration-none fw-bold">Tìm bạn ngay</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer_scripts.php'; ?>
    <script>
        // 1. CHUÔNG THÔNG BÁO
        const bellBtn = document.getElementById('bell-btn');
        if (bellBtn) {
            bellBtn.addEventListener('click', function() {
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    badge.style.display = 'none';
                    fetch('api_read_notif.php');
                }
            });
        }

        // 2. UPLOAD ẢNH TỪ MÁY TÍNH
        const btnUploadImage = document.getElementById('btn-upload-image');
        const postImageInput = document.getElementById('post-image-input');
        const postImagePreview = document.getElementById('post-image-preview');
        const postImagePreviewImg = document.getElementById('post-image-preview-img');
        const btnRemoveImage = document.getElementById('btn-remove-image');

        if (btnUploadImage) {
            btnUploadImage.addEventListener('click', () => postImageInput.click());
            
            postImageInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        postImagePreviewImg.src = ev.target.result;
                        postImagePreview.classList.remove('d-none');
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            btnRemoveImage.addEventListener('click', function() {
                postImageInput.value = '';
                postImagePreview.classList.add('d-none');
            });
        }

        // 3. AI VẼ ẢNH TỪ VĂN BẢN
        const btnAiDraw = document.getElementById('btn-ai-draw');
        if (btnAiDraw) {
            btnAiDraw.addEventListener('click', async function() {
                const content = document.getElementById('post-content').value;
                if (!content.trim()) return alert('Vui lòng nhập nội dung!');

                document.getElementById('ai-preview-box').classList.remove('d-none');
                document.getElementById('ai-loading').classList.remove('d-none');
                document.getElementById('ai-result').classList.add('d-none');
                this.disabled = true;

                try {
                    const response = await fetch('api_generate.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ content: content })
                    });
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        document.getElementById('ai-loading').classList.add('d-none');
                        document.getElementById('ai-result').classList.remove('d-none');
                        document.getElementById('ai-image-preview').src = data.image_url;
                        document.getElementById('ai-topic-badge').innerHTML = '<i class="fa-solid fa-microchip me-1"></i> ' + data.topic;
                        document.getElementById('hidden_ai_topic').value = data.topic;
                        document.getElementById('hidden_ai_sentiment').value = data.predicted_emotion || '';
                        document.getElementById('hidden_ai_image_url').value = data.image_url;
                    } else {
                        alert('Lỗi: ' + data.message);
                        document.getElementById('ai-preview-box').classList.add('d-none');
                    }
                } catch (error) {
                    alert('Có lỗi mạng xảy ra!');
                    document.getElementById('ai-preview-box').classList.add('d-none');
                } finally {
                    this.disabled = false;
                }
            });

            document.getElementById('btn-remove-ai').addEventListener('click', function() {
                document.getElementById('ai-preview-box').classList.add('d-none');
                document.getElementById('hidden_ai_topic').value = '';
                document.getElementById('hidden_ai_sentiment').value = '';
                document.getElementById('hidden_ai_image_url').value = '';
            });
        }

        // 4. CHIA SẺ BÀI VIẾT
        document.querySelectorAll('.btn-share-post').forEach(btn => {
            btn.addEventListener('click', async function() {
                if(!confirm('Bạn có muốn chia sẻ bài viết này lên tường nhà mình không?')) return;
                const postId = this.dataset.postId;
                btn.disabled = true;
                
                try {
                    const res = await fetch('api_share_post.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({post_id: postId})
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        alert('Đã chia sẻ thành công!');
                        location.reload(); 
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                    }
                } catch(e) { console.error(e); btn.disabled = false; }
            });
        });

        // 5. LƯU BÀI VIẾT
        document.querySelectorAll('.btn-save-post').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const postId = this.dataset.postId;
                try {
                    const res = await fetch('api_save_post.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({post_id: postId})
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        if(data.action === 'saved') {
                            this.innerHTML = '<i class="fa-solid fa-bookmark text-primary me-2"></i> Bỏ lưu bài viết';
                        } else {
                            this.innerHTML = '<i class="fa-regular fa-bookmark me-2"></i> Lưu bài viết';
                        }
                    }
                } catch(e) { console.error(e); }
            });
        });

        // 6. THÍCH BÀI VIẾT (LIKE)
        document.querySelectorAll('.btn-like').forEach(button => {
            button.addEventListener('click', async function() {
                const postId = this.getAttribute('data-post-id');
                const icon = document.getElementById('like-icon-' + postId);
                const countSpan = document.getElementById('like-count-' + postId);
                const textSpan = document.getElementById('like-text-' + postId);
                
                try {
                    const response = await fetch('api_like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ post_id: postId })
                    });
                    const data = await response.json();
                    if(data.status === 'success') {
                        countSpan.innerText = data.likes;
                        
                        if(data.likes == 0) {
                            textSpan.innerText = "0 lượt thích";
                        } else if(data.action === 'liked') {
                            textSpan.innerText = "Bạn và " + (data.likes - 1) + " người khác";
                        } else {
                            textSpan.innerText = data.likes + " lượt thích";
                        }

                        if(data.action === 'liked') {
                            this.classList.add('liked');
                            icon.classList.remove('fa-regular');
                            icon.classList.add('fa-solid');
                        } else {
                            this.classList.remove('liked');
                            icon.classList.remove('fa-solid');
                            icon.classList.add('fa-regular');
                        }
                    }
                } catch(e) { console.error(e); }
            });
        });

        // 7. BÌNH LUẬN (COMMENT)
        document.querySelectorAll('.form-comment').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault(); 
                
                const postId = this.getAttribute('data-post-id');
                const inputField = document.getElementById('comment-input-' + postId);
                const content = inputField.value;

                if (!content.trim()) return;

                const submitBtn = this.querySelector('button[type="submit"]');
                inputField.disabled = true;
                submitBtn.disabled = true;

                try {
                    const response = await fetch('api_comment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ post_id: postId, content: content })
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        const commentList = document.getElementById('comment-list-' + postId);
                        
                        const myAvatarUrl = '<?php echo !empty($_SESSION["avatar_url"]) ? $_SESSION["avatar_url"] : "https://ui-avatars.com/api/?name=".urlencode($_SESSION["full_name"])."&background=random"; ?>';
                        const avatarHTML = `<img src="${myAvatarUrl}" class="rounded-circle me-2 mt-1" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION["full_name"]); ?>&background=random';">`;

                        const newCommentHTML = `
                            <div class="d-flex mb-3">
                                ${avatarHTML}
                                <div class="comment-box">
                                    <h6 class="mb-0 fw-bold fs-6 text-dark">${data.full_name}</h6>
                                    <p class="mb-0 text-dark" style="font-size: 0.95rem;">${data.content}</p>
                                </div>
                            </div>
                        `;
                        commentList.insertAdjacentHTML('beforeend', newCommentHTML);

                        const countSpan = document.getElementById('comment-count-' + postId);
                        if(countSpan) {
                            countSpan.innerText = parseInt(countSpan.innerText) + 1;
                        }

                        inputField.value = '';
                    } else {
                        alert(data.message);
                    }
                } catch (err) {
                    alert("Có lỗi mạng khi đăng bình luận!");
                } finally {
                    inputField.disabled = false;
                    submitBtn.disabled = false;
                    inputField.focus();
                }
            });
        });
    </script>
</body>
</html>
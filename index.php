<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Đếm số thông báo chưa đọc
$notif_count_query = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $current_user_id AND is_read = 0");
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
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
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f8; }
        [data-bs-theme="dark"] body { background-color: #0f172a; }
        .navbar-custom { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        [data-bs-theme="dark"] .navbar-custom { background: rgba(30, 41, 59, 0.95); border-bottom: 1px solid rgba(255,255,255,0.05); }
        .card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04); margin-bottom: 20px; }
        [data-bs-theme="dark"] .card { background-color: #1e293b; color: #f8fafc; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #334155; color: #f8fafc; border: none; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #94a3b8; }
        [data-bs-theme="dark"] .btn-light { background-color: #334155; color: #f8fafc; border: none;}
        [data-bs-theme="dark"] .btn-light:hover { background-color: #475569; }
        [data-bs-theme="dark"] .text-dark { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .dropdown-menu { background-color: #334155; border-color: #475569; }
        [data-bs-theme="dark"] .dropdown-item { color: #f8fafc; }
        [data-bs-theme="dark"] .dropdown-item:hover { background-color: #475569; }
        .btn-ai { background: linear-gradient(135deg, #8b5cf6, #d946ef); color: white; border: none; font-weight: 600; }
        .btn-ai:hover { opacity: 0.9; color: white; }
        .status-input { background-color: #f1f5f9; border-radius: 20px; resize: none; }
        [data-bs-theme="dark"] .status-input { background-color: #0f172a; }
        .left-menu, .right-menu { position: sticky; top: 80px; }
        
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
        
        /* CSS cho Dropdown thông báo */
        .notif-item { transition: 0.2s; border-radius: 8px; margin: 0 8px; padding: 10px; }
        .notif-item:hover { background-color: #f1f5f9; }
        [data-bs-theme="dark"] .notif-item:hover { background-color: #475569; }
        .notif-icon { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        .unread-bg { background-color: rgba(14, 165, 233, 0.05); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">
                <i class="fa-solid fa-earth-asia me-1"></i> SocialAI
            </a>
            
            <div class="d-none d-md-block w-25">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control bg-light border-0 rounded-end-pill" placeholder="Tìm kiếm...">
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button id="btn-darkmode" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-moon"></i>
                </button>
                
                <!-- THÊM QUẢ CHUÔNG THÔNG BÁO -->
                <div class="dropdown me-1">
                    <button class="btn btn-light rounded-circle shadow-sm position-relative" type="button" data-bs-toggle="dropdown" id="bell-btn" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-bell"></i>
                        <?php if($unread_notif_count > 0): ?>
                            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6rem;">
                                <?php echo $unread_notif_count; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="width: 320px; max-height: 400px; overflow-y: auto; border-radius: 16px;">
                        <li><h6 class="dropdown-header fw-bold text-dark fs-6 mb-2">Thông báo của bạn</h6></li>
                        <?php
                        // Lấy 10 thông báo mới nhất
                        $sql_notif = "SELECT n.*, u.full_name, u.avatar_url FROM notifications n JOIN users u ON n.sender_id = u.id WHERE n.user_id = $current_user_id ORDER BY n.created_at DESC LIMIT 10";
                        $res_notif = $conn->query($sql_notif);
                        
                        if ($res_notif && $res_notif->num_rows > 0) {
                            while($notif = $res_notif->fetch_assoc()) {
                                $n_letter = mb_substr($notif['full_name'], 0, 1, "UTF-8");
                                $unread_class = ($notif['is_read'] == 0) ? 'unread-bg' : '';
                                
                                // Tạo nội dung thông báo
                                $notif_text = "";
                                $notif_link = "#";
                                if ($notif['type'] == 'like') {
                                    $notif_text = "đã <b>thích</b> bài viết của bạn.";
                                    $notif_link = "#post-" . $notif['post_id'];
                                } elseif ($notif['type'] == 'comment') {
                                    $notif_text = "đã <b>bình luận</b> về bài viết của bạn.";
                                    $notif_link = "#post-" . $notif['post_id'];
                                } elseif ($notif['type'] == 'friend_request') {
                                    $notif_text = "đã gửi cho bạn một <b>lời mời kết bạn</b>.";
                                    $notif_link = "friends.php";
                                } elseif ($notif['type'] == 'friend_accept') {
                                    $notif_text = "đã <b>chấp nhận</b> lời mời kết bạn.";
                                    $notif_link = "friends.php";
                                }
                        ?>
                            <li>
                                <a class="dropdown-item notif-item d-flex align-items-center <?php echo $unread_class; ?>" href="<?php echo $notif_link; ?>" style="white-space: normal;">
                                    <!-- Áp dụng sửa lỗi ảnh vỡ -->
                                    <img src="<?php echo !empty($notif['avatar_url']) ? $notif['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($notif['full_name']).'&background=random'; ?>" 
                                         class="notif-icon me-3 shadow-sm" 
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($notif['full_name']); ?>&background=random';">
                                         
                                    <div>
                                        <span class="text-dark" style="font-size: 0.9rem;"><?php echo "<b>" . htmlspecialchars($notif['full_name']) . "</b> " . $notif_text; ?></span>
                                        <div class="text-primary small" style="font-size: 0.75rem;"><i class="fa-regular fa-clock me-1"></i><?php echo date('H:i d/m', strtotime($notif['created_at'])); ?></div>
                                    </div>
                                </a>
                            </li>
                        <?php 
                            }
                        } else {
                            echo '<li class="text-center text-muted py-3 small">Bạn chưa có thông báo nào.</li>';
                        }
                        ?>
                    </ul>
                </div>

                <!-- AVATAR GÓC TRÊN PHẢI ĐÃ GẮN SỬA LỖI ẢNH -->
                <a href="profile.php" class="text-decoration-none">
                    <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm profile-btn-hover" data-bs-theme="light">
                        <img src="<?php echo !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']).'&background=random'; ?>" 
                             class="rounded-circle me-md-2" style="width: 32px; height: 32px; object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random';">
                             
                        <span class="fw-bold fs-6 pe-2 text-dark text-nowrap d-none d-md-inline-block text-truncate" style="max-width: 120px; vertical-align: middle;">
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </span>
                    </div>
                </a>
                
                <a href="logout.php" class="btn btn-light rounded-circle text-danger ms-2 shadow-sm" style="width: 40px; height: 40px;" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3 d-none d-md-block left-menu">
                <ul class="nav flex-column font-weight-bold gap-1">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 bg-primary bg-opacity-10 text-primary fw-bold" href="index.php"><i class="fa-solid fa-house fa-fw me-2"></i> Bảng tin</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="profile.php"><i class="fa-solid fa-user fa-fw me-2 text-success"></i> Trang cá nhân</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="friends.php"><i class="fa-solid fa-user-group fa-fw me-2 text-info"></i> Bạn bè</a></li>
                    <hr class="my-2">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-users fa-fw me-2 text-warning"></i> Nhóm (Sắp ra mắt)</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-robot fa-fw me-2 text-secondary"></i> Quản lý AI Model</a></li>
                </ul>
            </div>

            <div class="col-md-6">
                <!-- KHU VỰC ĐĂNG BÀI -->
                <div class="card p-3 shadow-sm">
                    <form action="process_post.php" method="POST" id="post-form">
                        <div class="d-flex mb-2">
                            <!-- AVATAR Ở Ô ĐĂNG BÀI ÁP DỤNG FIX ẢNH -->
                            <img src="<?php echo !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']).'&background=random'; ?>" 
                                 class="rounded-circle me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0;"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random';">
                            
                            <textarea id="post-content" class="form-control border-0 status-input p-3" name="content" rows="2" placeholder="<?php echo htmlspecialchars($_SESSION['full_name']); ?> ơi, bạn đang nghĩ gì?" required></textarea>
                        </div>
                        
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
                            <input type="hidden" name="ai_image_url" id="hidden_ai_image_url" value="">
                        </div>

                        <hr class="text-muted opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light text-muted fw-bold rounded-pill px-3"><i class="fa-solid fa-image text-success"></i></button>
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

                <!-- HIỂN THỊ BẢNG TIN -->
                <?php
                $sql_posts = "SELECT posts.*, users.full_name, users.avatar_url,
                                (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) as like_count,
                                (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = $current_user_id) as user_liked,
                                (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) as comment_count
                              FROM posts 
                              JOIN users ON posts.user_id = users.id 
                              WHERE posts.privacy = 'public' 
                                 OR posts.user_id = $current_user_id
                                 OR (posts.privacy = 'friends' AND posts.user_id IN (
                                     SELECT sender_id FROM friends WHERE receiver_id = $current_user_id AND status = 'accepted'
                                     UNION
                                     SELECT receiver_id FROM friends WHERE sender_id = $current_user_id AND status = 'accepted'
                                 ))
                              ORDER BY posts.created_at DESC";
                $result_posts = $conn->query($sql_posts);

                if ($result_posts && $result_posts->num_rows > 0) {
                    while($post = $result_posts->fetch_assoc()) {
                        $privacy_icon = 'fa-earth-asia';
                        if ($post['privacy'] == 'friends') $privacy_icon = 'fa-user-group';
                        if ($post['privacy'] == 'private') $privacy_icon = 'fa-lock';
                        
                        $is_liked_class = ($post['user_liked'] > 0) ? 'liked' : '';
                        $like_icon = ($post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular';
                        
                        $likers_sql = "SELECT u.full_name FROM likes l JOIN users u ON l.user_id = u.id WHERE l.post_id = {$post['id']} ORDER BY l.created_at DESC LIMIT 2";
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
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                        <div class="text-muted d-flex align-items-center" style="font-size: 0.85rem;">
                                            <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?> 
                                            <span class="mx-1">•</span>
                                            <i class="fa-solid <?php echo $privacy_icon; ?>" title="Quyền riêng tư"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($post['ai_topic'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-microchip me-1"></i> <?php echo htmlspecialchars($post['ai_topic']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($post['user_id'] == $current_user_id): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle text-muted" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li><a class="dropdown-item py-2" href="edit_post.php?id=<?php echo $post['id']; ?>"><i class="fa-solid fa-pen me-2"></i> Chỉnh sửa bài</a></li>
                                            <li><a class="dropdown-item text-danger py-2" href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');"><i class="fa-solid fa-trash me-2"></i> Xóa bài viết</a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="mb-2 mt-2 text-dark" style="white-space: pre-wrap;"><?php echo htmlspecialchars($post['content']); ?></p>
                            
                            <?php if (!empty($post['generated_image_url'])): ?>
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
                                <button class="btn btn-light fw-bold flex-grow-1 ms-1 text-muted"><i class="fa-solid fa-share me-1"></i> Chia sẻ</button>
                            </div>
                            
                            <hr class="my-2 opacity-25">
                            
                            <div class="mt-3">
                                <div id="comment-list-<?php echo $post['id']; ?>">
                                    <?php
                                    $post_id_cmt = $post['id'];
                                    $sql_cmts = "SELECT c.*, u.full_name, u.avatar_url FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = $post_id_cmt ORDER BY c.created_at ASC";
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
                        $sql_contacts = "SELECT u.id, u.full_name, u.avatar_url FROM users u 
                                         JOIN friends f ON (u.id = f.sender_id OR u.id = f.receiver_id) 
                                         WHERE (f.sender_id = $current_user_id OR f.receiver_id = $current_user_id) 
                                         AND u.id != $current_user_id AND f.status = 'accepted' LIMIT 10";
                        $res_contacts = $conn->query($sql_contacts);
                        if ($res_contacts && $res_contacts->num_rows > 0) {
                            while($contact = $res_contacts->fetch_assoc()) {
                        ?>
                        <li class="d-flex align-items-center mb-3 p-1 rounded custom-hover" style="cursor: pointer;">
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
    <script>
        const btnDarkMode = document.getElementById('btn-darkmode');
        const htmlElement = document.documentElement;
        const iconDarkMode = btnDarkMode.querySelector('i');

        const currentTheme = localStorage.getItem('theme') || 'light';
        setTheme(currentTheme);

        btnDarkMode.addEventListener('click', () => {
            setTheme(htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light');
        });

        function setTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            iconDarkMode.className = theme === 'dark' ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
        }

        // TÍNH NĂNG TẮT CHẤM ĐỎ KHI BẤM VÀO CHUÔNG THÔNG BÁO
        document.getElementById('bell-btn').addEventListener('click', function() {
            const badge = document.getElementById('notif-badge');
            if (badge) {
                // Xóa số hiển thị
                badge.style.display = 'none';
                // Gửi API ngầm báo đã đọc
                fetch('api_read_notif.php');
            }
        });

        document.getElementById('btn-ai-draw').addEventListener('click', async function() {
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
            document.getElementById('hidden_ai_image_url').value = '';
        });

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
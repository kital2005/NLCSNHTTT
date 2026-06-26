<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Lấy thông tin user hiện tại
$query = "SELECT * FROM users WHERE id = $current_user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

// Đếm số bài viết và tổng số lượt thích
$post_count_query = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = $current_user_id");
$post_count = $post_count_query->fetch_assoc()['total'];

$total_likes_query = $conn->query("SELECT COUNT(*) as total FROM likes JOIN posts ON likes.post_id = posts.id WHERE posts.user_id = $current_user_id");
$total_likes = $total_likes_query->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['full_name']); ?> | SocialAI</title>
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
        [data-bs-theme="dark"] .text-dark { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .form-control { background-color: #334155; color: #f8fafc; border: none; }
        [data-bs-theme="dark"] .modal-content { background-color: #1e293b; color: #f8fafc; }
        
        /* CSS LAYOUT PROFILE CHUẨN MẠNG XÃ HỘI MỚI */
        .profile-container { max-width: 800px; margin: 0 auto; }
        .cover-photo {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            background-color: #e2e8f0;
        }
        .profile-header-wrapper { position: relative; margin-bottom: 20px; background: transparent; }
        
        /* FIX LỖI MẤT ĐẦU: Dùng object-fit: contain kết hợp nền */
        .avatar-profile {
            width: 140px;
            height: 140px;
            object-fit: contain; 
            background-color: #f1f5f9;
            border-radius: 50%;
            border: 4px solid #fff;
            position: absolute;
            bottom: -50px;
            left: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        [data-bs-theme="dark"] .avatar-profile { border-color: #1e293b; background-color: #334155; }
        
        .profile-actions { padding-top: 15px; padding-right: 20px; display: flex; justify-content: flex-end; }
        .profile-details { padding: 20px 30px 10px 30px; margin-top: 10px; }
        
        /* Giao diện Modal Premium */
        .custom-file-upload {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            display: block;
        }
        .custom-file-upload:hover { border-color: #0ea5e9; background-color: rgba(14, 165, 233, 0.05); }
        .img-preview { max-width: 100%; height: 150px; object-fit: contain; border-radius: 8px; display: none; margin: 10px auto 0; }
        
        /* CSS cho Bình luận */
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-5" href="index.php">
                <i class="fa-solid fa-arrow-left me-2"></i> Trở về Bảng tin
            </a>
            <div class="d-flex align-items-center gap-3">
                <button id="btn-darkmode" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="profile-container mt-2">
        
        <div class="card shadow-sm border-0 pb-3 mb-4">
            <div class="profile-header-wrapper">
                <?php if (!empty($user['cover_url'])): ?>
                    <img src="<?php echo htmlspecialchars($user['cover_url']); ?>" class="cover-photo">
                <?php else: ?>
                    <div class="cover-photo d-flex justify-content-center align-items-center text-muted" style="background: linear-gradient(135deg, #e2e8f0, #cbd5e1);">
                        <i class="fa-solid fa-camera fa-3x opacity-25"></i>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($user['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" class="avatar-profile">
                <?php else: ?>
                    <div class="avatar-profile d-flex justify-content-center align-items-center text-white fw-bold" style="background: linear-gradient(135deg, #0ea5e9, #6366f1); font-size: 3rem;">
                        <?php echo mb_substr($user['full_name'], 0, 1, "UTF-8"); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="profile-actions">
                <button class="btn btn-outline-dark fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fa-solid fa-pen me-2"></i> Chỉnh sửa hồ sơ
                </button>
            </div>
            
            <div class="profile-details">
                <h3 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p class="text-muted mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                
                <?php if (!empty($user['bio'])): ?>
                    <p class="text-dark mb-3" style="white-space: pre-wrap; font-size: 0.95rem;"><?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
                
                <div class="d-flex gap-4 text-muted small mt-2">
                    <span><i class="fa-solid fa-file-lines me-1"></i> <strong class="text-dark"><?php echo $post_count; ?></strong> bài viết</span>
                    <span><i class="fa-solid fa-heart me-1"></i> <strong class="text-dark"><?php echo $total_likes; ?></strong> lượt thích nhận được</span>
                    <span><i class="fa-solid fa-calendar-days me-1"></i> Tham gia năm <?php echo date('Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3 text-dark px-2">Bài viết của bạn</h5>

        <?php
        $sql_posts = "SELECT posts.*, users.full_name, users.avatar_url,
                        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) as like_count,
                        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = $current_user_id) as user_liked,
                        (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) as comment_count
                      FROM posts 
                      JOIN users ON posts.user_id = users.id 
                      WHERE posts.user_id = $current_user_id
                      ORDER BY posts.created_at DESC";
        $result_posts = $conn->query($sql_posts);

        if ($result_posts && $result_posts->num_rows > 0) {
            while($post = $result_posts->fetch_assoc()) {
                $first_letter = mb_substr($post['full_name'], 0, 1, "UTF-8");
                $privacy_icon = 'fa-earth-asia';
                if ($post['privacy'] == 'friends') $privacy_icon = 'fa-user-group';
                if ($post['privacy'] == 'private') $privacy_icon = 'fa-lock';
                
                $is_liked_class = ($post['user_liked'] > 0) ? 'liked' : '';
                $like_icon = ($post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular';
                
                // --- TÍNH NĂNG MỚI: Lấy tên 2 người gần nhất đã Like bài viết ---
                $likers_sql = "SELECT u.full_name FROM likes l JOIN users u ON l.user_id = u.id WHERE l.post_id = {$post['id']} ORDER BY l.created_at DESC LIMIT 2";
                $likers_res = $conn->query($likers_sql);
                $liker_names = [];
                while($liker = $likers_res->fetch_assoc()) {
                    // Nếu là mình thì hiện chữ "Bạn"
                    $liker_names[] = ($liker['full_name'] == $user['full_name']) ? "Bạn" : $liker['full_name'];
                }
                $like_text = "";
                if ($post['like_count'] > 0) {
                    $like_text = implode(", ", $liker_names);
                    if ($post['like_count'] > count($liker_names)) {
                        $like_text .= " và " . ($post['like_count'] - count($liker_names)) . " người khác";
                    }
                }
        ?>
                <div class="card p-3 shadow-sm border-0 mb-4" style="border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($post['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" class="rounded-circle me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: contain; background:#f1f5f9;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2 fw-bold fs-5 shadow-sm" style="width: 45px; height: 45px;">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>

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
                            
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle text-muted" type="button" data-bs-toggle="dropdown" style="width: 35px; height: 35px;">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item py-2" href="edit_post.php?id=<?php echo $post['id']; ?>"><i class="fa-solid fa-pen me-2"></i> Chỉnh sửa bài</a></li>
                                    <li><a class="dropdown-item text-danger py-2" href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');"><i class="fa-solid fa-trash me-2"></i> Xóa bài viết</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mb-2 mt-2 text-dark" style="white-space: pre-wrap;"><?php echo htmlspecialchars($post['content']); ?></p>
                    
                    <?php if (!empty($post['generated_image_url'])): ?>
                        <div class="rounded-3 overflow-hidden border mb-2">
                            <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid w-100" alt="AI Image">
                        </div>
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
                                    $cmt_letter = mb_substr($cmt['full_name'], 0, 1, "UTF-8");
                            ?>
                                <div class="d-flex mb-3">
                                    <?php if (!empty($cmt['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($cmt['avatar_url']); ?>" class="rounded-circle me-2 mt-1" style="width: 32px; height: 32px; object-fit: contain; background:#f1f5f9; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2 mt-1" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.85rem;">
                                            <?php echo $cmt_letter; ?>
                                        </div>
                                    <?php endif; ?>
                                    
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
                            <?php if (!empty($_SESSION['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="rounded-circle me-2 mt-1 shadow-sm" style="width: 32px; height: 32px; object-fit: contain; background:#f1f5f9; flex-shrink: 0;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 mt-1 shadow-sm" style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <?php echo mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"); ?>
                                </div>
                            <?php endif; ?>

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
            echo '<div class="card p-5 text-center border-0 bg-transparent shadow-none mt-4">';
            echo '<i class="fa-solid fa-camera fa-4x text-muted opacity-25 mb-3"></i>';
            echo '<h5 class="text-muted fw-bold">Chưa có bài viết nào</h5>';
            echo '<p class="text-muted">Chia sẻ khoảnh khắc đầu tiên của bạn với cộng đồng.</p>';
            echo '<a href="index.php" class="btn btn-primary rounded-pill px-4 mt-2">Đăng bài ngay</a>';
            echo '</div>';
        }
        ?>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-4">Cập nhật hồ sơ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="process_profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Ảnh đại diện mới</label>
                            <label class="custom-file-upload w-100">
                                <i class="fa-solid fa-cloud-arrow-up text-primary fs-3 mb-2"></i>
                                <div class="fw-bold text-primary">Nhấn để chọn ảnh đại diện</div>
                                <small class="text-muted">JPG, PNG, GIF</small>
                                <input type="file" class="d-none" name="avatar" id="avatar-input" accept="image/*">
                            </label>
                            <img id="avatar-preview-img" class="img-preview shadow-sm border mt-2" style="height: 100px; width: 100px; border-radius: 50%;">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Ảnh bìa (Banner)</label>
                            <label class="custom-file-upload w-100 pb-3 pt-3">
                                <i class="fa-regular fa-image text-primary fs-4 mb-1"></i>
                                <div class="fw-bold text-muted small">Chọn ảnh bìa</div>
                                <input type="file" class="d-none" name="cover" id="cover-input" accept="image/*">
                            </label>
                            <img id="cover-preview-img" class="img-preview shadow-sm border mt-2">
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="floatingName" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            <label for="floatingName" class="text-muted">Tên hiển thị</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" placeholder="Tiểu sử" id="floatingBio" name="bio" style="height: 80px"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            <label for="floatingBio" class="text-muted">Tiểu sử ngắn gọn</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="floatingPass" name="new_password" placeholder="Pass">
                            <label for="floatingPass" class="text-muted">Mật khẩu mới (Không bắt buộc)</label>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold py-3 fs-6 shadow-sm">Lưu cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentUserAvatar = '<?php echo isset($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : ""; ?>';
        const currentUserInitial = '<?php echo mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"); ?>';
        const currentUserName = '<?php echo $_SESSION['full_name']; ?>';

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

        // PREVIEW ẢNH UPLOAD ĐẸP MẮT
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('avatar-preview-img');
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('cover-input').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('cover-preview-img');
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // XỬ LÝ LIKE BẰNG AJAX (Cập nhật giao diện Like xịn)
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
                        
                        // Đổi chữ hiển thị
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

        // XỬ LÝ COMMENT BẰNG AJAX TỪ INDEX.PHP
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
                        const avatarHTML = currentUserAvatar 
                            ? `<img src="${currentUserAvatar}" class="rounded-circle me-2 mt-1" style="width: 32px; height: 32px; object-fit: contain; background:#f1f5f9; flex-shrink: 0;">`
                            : `<div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2 mt-1" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.85rem;">${currentUserInitial}</div>`;

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
                        if(countSpan) countSpan.innerText = parseInt(countSpan.innerText) + 1;
                        
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
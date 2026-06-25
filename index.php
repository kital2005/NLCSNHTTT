<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];
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
        .text-gradient { background: linear-gradient(135deg, #0ea5e9, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
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
        .left-menu { position: sticky; top: 80px; }
        .privacy-select { border-radius: 20px; font-size: 0.85rem; padding-top: 0.25rem; padding-bottom: 0.25rem; }
        
        /* Chỉnh CSS riêng cho phần Bình luận */
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
        
        /* Hiệu ứng hover cho nút tới Profile */
        .profile-btn-hover:hover { background-color: #e2e8f0 !important; transition: 0.2s; }
        [data-bs-theme="dark"] .profile-btn-hover:hover { background-color: #475569 !important; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand fw-bold text-gradient fs-4" href="#">
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
                
                <!-- NÚT CHUYỂN SANG TRANG CÁ NHÂN KÈM HIỂN THỊ AVATAR -->
                <a href="profile.php" class="text-decoration-none">
                    <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm profile-btn-hover" data-bs-theme="light">
                        <?php if (!empty($_SESSION['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="rounded-circle me-md-2" style="width: 32px; height: 32px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-md-2" style="width: 32px; height: 32px; flex-shrink: 0;">
                                <?php echo mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"); ?>
                            </div>
                        <?php endif; ?>
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
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-user-group fa-fw me-2 text-info"></i> Bạn bè</a></li>
                    <hr class="my-2">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-robot fa-fw me-2 text-secondary"></i> Quản lý AI Model</a></li>
                </ul>
            </div>

            <div class="col-md-6">
                <!-- KHU VỰC ĐĂNG BÀI -->
                <div class="card p-3 shadow-sm">
                    <form action="process_post.php" method="POST" id="post-form">
                        <div class="d-flex mb-2">
                            <!-- AVATAR Ở Ô ĐĂNG BÀI -->
                            <?php if (!empty($_SESSION['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="rounded-circle me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                    <?php echo mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"); ?>
                                </div>
                            <?php endif; ?>
                            
                            <textarea id="post-content" class="form-control border-0 status-input p-3" name="content" rows="2" placeholder="<?php echo htmlspecialchars($_SESSION['full_name']); ?> ơi, bạn đang nghĩ gì?" required></textarea>
                        </div>
                        
                        <div id="ai-preview-box" class="d-none mb-3 p-2 border rounded-3 bg-light position-relative">
                            <button type="button" id="btn-remove-ai" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle" style="z-index: 10;"><i class="fa-solid fa-xmark"></i></button>
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
                // THÊM users.avatar_url VÀO CÂU LỆNH SQL
                $sql_posts = "SELECT posts.*, users.full_name, users.avatar_url,
                                (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) as like_count,
                                (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = $current_user_id) as user_liked,
                                (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) as comment_count
                              FROM posts 
                              JOIN users ON posts.user_id = users.id 
                              WHERE posts.privacy = 'public' 
                                 OR posts.privacy = 'friends' 
                                 OR (posts.privacy = 'private' AND posts.user_id = $current_user_id)
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
                ?>
                        <div class="card p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    
                                    <!-- AVATAR Ở BÀI VIẾT -->
                                    <?php if (!empty($post['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" class="rounded-circle me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2 fw-bold fs-5 shadow-sm" style="width: 45px; height: 45px;">
                                            <?php echo $first_letter; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['full_name']); ?></h6>
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
                                            <li><a class="dropdown-item" href="edit_post.php?id=<?php echo $post['id']; ?>"><i class="fa-solid fa-pen me-2"></i> Chỉnh sửa bài</a></li>
                                            <li><a class="dropdown-item text-danger" href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');"><i class="fa-solid fa-trash me-2"></i> Xóa bài viết</a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="mb-2 mt-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($post['content']); ?></p>
                            
                            <?php if (!empty($post['generated_image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="AI Generated Image">
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                                <span><i class="fa-solid fa-thumbs-up text-primary me-1"></i> <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['like_count']; ?></span></span>
                                
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
                                    // THÊM u.avatar_url VÀO LỆNH GỌI COMMENT
                                    $sql_cmts = "SELECT c.*, u.full_name, u.avatar_url FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = $post_id_cmt ORDER BY c.created_at ASC";
                                    $res_cmts = $conn->query($sql_cmts);
                                    if ($res_cmts && $res_cmts->num_rows > 0) {
                                        while($cmt = $res_cmts->fetch_assoc()) {
                                            $cmt_letter = mb_substr($cmt['full_name'], 0, 1, "UTF-8");
                                    ?>
                                        <div class="d-flex mb-3">
                                            <!-- AVATAR Ở KHU VỰC HIỂN THỊ COMMENT -->
                                            <?php if (!empty($cmt['avatar_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($cmt['avatar_url']); ?>" class="rounded-circle me-2 mt-1" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2 mt-1" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.85rem;">
                                                    <?php echo $cmt_letter; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="comment-box">
                                                <h6 class="mb-0 fw-bold fs-6"><?php echo htmlspecialchars($cmt['full_name']); ?></h6>
                                                <p class="mb-0" style="font-size: 0.95rem;"><?php echo htmlspecialchars($cmt['content']); ?></p>
                                            </div>
                                        </div>
                                    <?php 
                                        } 
                                    } 
                                    ?>
                                </div>
                                
                                <div class="d-flex mt-2">
                                    <!-- AVATAR Ở Ô NHẬP COMMENT -->
                                    <?php if (!empty($_SESSION['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="rounded-circle me-2 mt-1 shadow-sm" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;">
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
                    echo '<div class="card p-4 text-center border-0 bg-light"><h5 class="text-muted fw-bold">Bảng tin đang trống</h5></div>';
                }
                ?>
            </div>

            <div class="col-md-3 d-none d-md-block">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lưu thông tin Avatar của User hiện tại để lát nữa vẽ comment AJAX
        const currentUserAvatar = '<?php echo isset($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : ""; ?>';
        const currentUserInitial = '<?php echo mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"); ?>';

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

        // --- XỬ LÝ VẼ ẢNH AI BẰNG AJAX ---
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

        // --- XỬ LÝ NÚT THÍCH BẰNG AJAX ---
        document.querySelectorAll('.btn-like').forEach(button => {
            button.addEventListener('click', async function() {
                const postId = this.getAttribute('data-post-id');
                const icon = document.getElementById('like-icon-' + postId);
                const countSpan = document.getElementById('like-count-' + postId);
                
                try {
                    const response = await fetch('api_like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ post_id: postId })
                    });
                    const data = await response.json();
                    if(data.status === 'success') {
                        countSpan.innerText = data.likes;
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

        // ========================================================
        // SCRIPT XỬ LÝ BÌNH LUẬN SIÊU MƯỢT (KHÔNG LOAD TRANG)
        // ========================================================
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
                        
                        // Kĩ thuật in hình Avatar khi đăng bình luận (ưu tiên ảnh thật, nếu không có mới dùng chữ)
                        const avatarHTML = currentUserAvatar 
                            ? `<img src="${currentUserAvatar}" class="rounded-circle me-2 mt-1" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;">`
                            : `<div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2 mt-1" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.85rem;">${currentUserInitial}</div>`;

                        const newCommentHTML = `
                            <div class="d-flex mb-3">
                                ${avatarHTML}
                                <div class="comment-box">
                                    <h6 class="mb-0 fw-bold fs-6">${data.full_name}</h6>
                                    <p class="mb-0" style="font-size: 0.95rem;">${data.content}</p>
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
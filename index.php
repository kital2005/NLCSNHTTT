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
                
                <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm" data-bs-theme="light">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span class="fw-bold fs-6 pe-2 text-dark text-nowrap"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
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
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 bg-primary bg-opacity-10 text-primary fw-bold" href="#"><i class="fa-solid fa-house fa-fw me-2"></i> Bảng tin</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-user-group fa-fw me-2 text-info"></i> Bạn bè</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-users fa-fw me-2 text-primary"></i> Nhóm</a></li>
                    <hr class="my-2">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="#"><i class="fa-solid fa-robot fa-fw me-2 text-secondary"></i> Quản lý AI Model</a></li>
                </ul>
            </div>

            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <form action="process_post.php" method="POST" id="post-form">
                        <div class="d-flex mb-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                <i class="fa-solid fa-user"></i>
                            </div>
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

                <?php
                // Chỉ hiển thị bài Công khai, Bạn bè, hoặc Bài CỦA CHÍNH MÌNH (kể cả Chỉ mình tôi)
                $sql_posts = "SELECT posts.*, users.full_name 
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
                        
                        // Map icon quyền riêng tư
                        $privacy_icon = 'fa-earth-asia';
                        if ($post['privacy'] == 'friends') $privacy_icon = 'fa-user-group';
                        if ($post['privacy'] == 'private') $privacy_icon = 'fa-lock';
                ?>
                        <div class="card p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2 fw-bold fs-5 shadow-sm" style="width: 45px; height: 45px;">
                                        <?php echo $first_letter; ?>
                                    </div>
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
                            
                            <hr class="my-2 opacity-25">
                            <div class="d-flex justify-content-between px-md-2">
                                <button class="btn btn-light fw-bold flex-grow-1 me-1 text-muted"><i class="fa-regular fa-thumbs-up me-1"></i> Thích</button>
                                <button class="btn btn-light fw-bold flex-grow-1 mx-1 text-muted"><i class="fa-regular fa-message me-1"></i> Bình luận</button>
                                <button class="btn btn-light fw-bold flex-grow-1 ms-1 text-muted"><i class="fa-solid fa-share me-1"></i> Chia sẻ</button>
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
        // ... (Giữ nguyên mã JS cho Dark Mode và AJAX vẽ ảnh) ...
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
    </script>
</body>
</html>
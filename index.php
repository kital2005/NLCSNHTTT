<?php
session_start();
require_once 'db.php';

// Nếu chưa có thẻ Session (chưa đăng nhập), đuổi cổ về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f4f6f8; 
        }
        [data-bs-theme="dark"] body { background-color: #0f172a; }
        
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        [data-bs-theme="dark"] .navbar-custom {
            background: rgba(30, 41, 59, 0.95);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .text-gradient {
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
        }
        
        [data-bs-theme="dark"] .card { 
            background-color: #1e293b; 
            color: #f8fafc; 
        }
        [data-bs-theme="dark"] .form-control { background-color: #334155; color: #f8fafc; border: none; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #94a3b8; }
        [data-bs-theme="dark"] .btn-light { background-color: #334155; color: #f8fafc; border: none;}
        [data-bs-theme="dark"] .btn-light:hover { background-color: #475569; }
        [data-bs-theme="dark"] .text-dark { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }

        .btn-ai {
            background: linear-gradient(135deg, #8b5cf6, #d946ef);
            color: white;
            border: none;
            font-weight: 600;
        }
        .btn-ai:hover { opacity: 0.9; color: white; }
        
        .status-input {
            background-color: #f1f5f9;
            border-radius: 20px;
            resize: none;
        }
        [data-bs-theme="dark"] .status-input { background-color: #0f172a; }
        .left-menu { position: sticky; top: 80px; }

        /* =========================================
           TỐI ƯU GIAO DIỆN RIÊNG CHO MOBILE
           ========================================= */
        @media (max-width: 576px) {
            .btn-mobile-compact {
                padding: 0.3rem 0.6rem !important;
                font-size: 0.85rem !important;
            }
            .text-mobile-hide {
                display: none !important;
            }
            .action-gap {
                gap: 0.25rem !important;
            }
            .nav-avatar-mobile {
                width: 28px !important; 
                height: 28px !important;
            }
        }
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
                    <input type="text" class="form-control bg-light border-0 rounded-end-pill" placeholder="Tìm kiếm trên SocialAI...">
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">
                <button id="btn-darkmode" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm nav-avatar-mobile" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-moon"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm" data-bs-theme="light">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-md-2 nav-avatar-mobile" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span class="fw-bold fs-6 pe-2 text-dark text-nowrap d-inline-block text-truncate text-mobile-hide" style="max-width: 120px; vertical-align: middle;">
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </span>
                    </div>
                    <a href="logout.php" class="btn btn-light rounded-circle text-danger ms-2 shadow-sm d-flex align-items-center justify-content-center nav-avatar-mobile" style="width: 40px; height: 40px; flex-shrink: 0;" title="Đăng xuất">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-3 mt-md-4">
        <div class="row">
            
            <div class="col-md-3 d-none d-md-block left-menu">
                <ul class="nav flex-column font-weight-bold gap-1">
                    <li class="nav-item">
                        <a class="nav-link text-dark rounded-3 px-3 py-2 bg-primary bg-opacity-10 text-primary fw-bold" href="#">
                            <i class="fa-solid fa-house fa-fw me-2"></i> Bảng tin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="#">
                            <i class="fa-solid fa-user-group fa-fw me-2 text-info"></i> Bạn bè
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="#">
                            <i class="fa-solid fa-users fa-fw me-2 text-primary"></i> Nhóm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="#">
                            <i class="fa-solid fa-bookmark fa-fw me-2 text-warning"></i> Đã lưu
                        </a>
                    </li>
                    <hr class="my-2">
                    <li class="nav-item">
                        <a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="#">
                            <i class="fa-solid fa-robot fa-fw me-2 text-secondary"></i> Quản lý AI Model
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <form action="process_post.php" method="POST">
                        <div class="d-flex mb-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 mt-1 shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <textarea class="form-control border-0 status-input p-3" name="content" rows="2" placeholder="<?php echo htmlspecialchars($_SESSION['full_name']); ?> ơi, bạn đang nghĩ gì?" required></textarea>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2 action-gap">
                                <button type="button" class="btn btn-light text-muted fw-bold rounded-pill px-3 btn-mobile-compact">
                                    <i class="fa-solid fa-image text-success"></i> 
                                    <span class="ms-1 text-mobile-hide">Ảnh/Video</span>
                                </button>
                                
                                <button type="submit" name="post_type" value="ai_draw" class="btn btn-ai rounded-pill px-3 shadow-sm btn-mobile-compact">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> 
                                    <span class="ms-1 d-none d-sm-inline">Tự vẽ ảnh AI</span>
                                    <span class="ms-1 d-inline d-sm-none">AI Vẽ</span> 
                                </button>
                            </div>
                            
                            <button type="submit" name="post_type" value="normal" class="btn btn-primary rounded-pill px-4 fw-bold btn-mobile-compact">Đăng</button>
                        </div>
                    </form>
                </div>

                <?php
                // Truy vấn lấy bài viết và tên người đăng
                $sql_posts = "SELECT posts.*, users.full_name 
                              FROM posts 
                              JOIN users ON posts.user_id = users.id 
                              ORDER BY posts.created_at DESC";
                $result_posts = $conn->query($sql_posts);

                if ($result_posts && $result_posts->num_rows > 0) {
                    while($post = $result_posts->fetch_assoc()) {
                        // Lấy chữ cái đầu của tên làm Avatar
                        $first_letter = mb_substr($post['full_name'], 0, 1, "UTF-8");
                ?>
                        <div class="card p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2 fw-bold fs-5 shadow-sm" style="width: 45px; height: 45px;">
                                        <?php echo $first_letter; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></small>
                                    </div>
                                </div>
                                
                                <?php if (!empty($post['ai_topic'])): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2 py-1">
                                        <i class="fa-solid fa-microchip me-1"></i> <?php echo htmlspecialchars($post['ai_topic']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="mb-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($post['content']); ?></p>
                            
                            <?php if (!empty($post['generated_image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="AI Generated Image">
                            <?php endif; ?>
                            
                            <hr class="my-2 opacity-25">
                            <div class="d-flex justify-content-between px-md-2 action-gap">
                                <button class="btn btn-light fw-bold flex-grow-1 me-1 text-muted btn-mobile-compact">
                                    <i class="fa-regular fa-thumbs-up me-1"></i> <span class="d-none d-sm-inline">Thích</span>
                                </button>
                                <button class="btn btn-light fw-bold flex-grow-1 mx-1 text-muted btn-mobile-compact">
                                    <i class="fa-regular fa-message me-1"></i> <span class="d-none d-sm-inline">Bình luận</span>
                                </button>
                                <button class="btn btn-light fw-bold flex-grow-1 ms-1 text-muted btn-mobile-compact">
                                    <i class="fa-solid fa-share me-1"></i> <span class="d-none d-sm-inline">Chia sẻ</span>
                                </button>
                            </div>
                        </div>
                <?php 
                    } 
                } else {
                    echo '<div class="card p-4 text-center shadow-sm border-0 bg-light" data-bs-theme="light">';
                    echo '<i class="fa-solid fa-box-open fa-3x text-muted mb-3 opacity-50"></i>';
                    echo '<h5 class="text-muted fw-bold">Bảng tin đang trống</h5>';
                    echo '<p class="text-muted mb-0">Hãy là người đầu tiên chia sẻ cảm nghĩ của bạn!</p>';
                    echo '</div>';
                }
                ?>
            </div>

            <div class="col-md-3 d-none d-md-block left-menu">
                <div class="card p-3 mb-3 shadow-sm">
                    <h6 class="fw-bold text-muted mb-3">Người liên hệ</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-3">
                            <img src="https://ui-avatars.com/api/?name=Ngo+Van&background=random" class="rounded-circle me-2" width="35" height="35">
                            <span class="fw-bold fs-6">Giảng Viên Hướng Dẫn</span>
                            <div class="bg-success rounded-circle ms-auto" style="width: 8px; height: 8px;"></div>
                        </li>
                        <li class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=Lop+Truong&background=random" class="rounded-circle me-2" width="35" height="35">
                            <span class="fw-bold fs-6">Bạn Cùng Nhóm</span>
                            <div class="bg-secondary rounded-circle ms-auto" style="width: 8px; height: 8px;"></div>
                        </li>
                    </ul>
                </div>
                
                <div class="card p-3 shadow-sm">
                    <h6 class="fw-bold text-muted mb-2">Thịnh hành AI</h6>
                    <div class="mb-2">
                        <small class="text-primary fw-bold">#LapTrinhWeb</small><br>
                        <small class="text-muted">1,200 bài viết</small>
                    </div>
                    <div>
                        <small class="text-primary fw-bold">#MachineLearning</small><br>
                        <small class="text-muted">850 bài viết sinh ảnh</small>
                    </div>
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
            const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
        });

        function setTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            if(theme === 'dark') {
                iconDarkMode.classList.replace('fa-moon', 'fa-sun');
                iconDarkMode.classList.add('text-warning');
            } else {
                iconDarkMode.classList.replace('fa-sun', 'fa-moon');
                iconDarkMode.classList.remove('text-warning');
            }
        }
    </script>
</body>
</html>
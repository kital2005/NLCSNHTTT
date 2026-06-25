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

// Đếm số bài viết
$post_count_query = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = $current_user_id");
$post_count = $post_count_query->fetch_assoc()['total'];

// Đếm tổng số lượt Thích đã nhận được
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
        .card { border: none; border-radius: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); margin-bottom: 20px; transition: 0.3s; }
        [data-bs-theme="dark"] .card { background-color: #1e293b; color: #f8fafc; }
        [data-bs-theme="dark"] .text-dark { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .form-control { background-color: #334155; color: #f8fafc; border: none; }
        [data-bs-theme="dark"] .modal-content { background-color: #1e293b; color: #f8fafc; }
        [data-bs-theme="dark"] .bg-glass { background: linear-gradient(145deg, #1e293b, #0f172a) !important; }
        
        /* CSS DÀNH RIÊNG CHO LAYOUT DASHBOARD MỚI */
        .bg-glass { background: linear-gradient(145deg, #ffffff, #f1f5f9); border: 1px solid rgba(255,255,255,0.4); }
        .avatar-modern {
            width: 140px; 
            height: 140px; 
            object-fit: cover; 
            border: 4px solid #fff; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        [data-bs-theme="dark"] .avatar-modern { border-color: #334155; }
        
        .cover-modern {
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            position: absolute; 
            top:0; left:0;
            transition: transform 0.5s ease;
        }
        .cover-card:hover .cover-modern { transform: scale(1.05); }
        .cover-overlay {
            position: absolute; 
            bottom: 0; left: 0; 
            width: 100%; height: 50%; 
            background: linear-gradient(to top, rgba(15,23,42,0.8), transparent);
        }
        
        /* Modal & Utilities */
        .img-preview { width: 100%; height: 200px; object-fit: cover; border-radius: 12px; display: none; }
        .avatar-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; display: none; margin: 0 auto; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-5" href="index.php">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Bảng tin
            </a>
            <div class="d-flex align-items-center">
                <button id="btn-darkmode" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <!-- HEADER DASHBOARD: TÁCH BIỆT 2 KHỐI RÕ RÀNG -->
        <div class="row mb-4 align-items-stretch">
            
            <!-- THẺ THÔNG TIN (Profile Info Card) -->
            <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
                <div class="card h-100 p-4 text-center border-0 bg-glass d-flex flex-column justify-content-center">
                    <div class="position-relative mx-auto mb-3">
                        <?php if (!empty($user['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" class="rounded-circle avatar-modern">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center mx-auto avatar-modern" style="font-size: 3.5rem; font-weight: bold;">
                                <?php echo mb_substr($user['full_name'], 0, 1, "UTF-8"); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-primary fw-bold mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <div class="px-3 mb-4">
                        <?php if (!empty($user['bio'])): ?>
                            <p class="text-muted small fst-italic mb-0" style="white-space: pre-wrap;">"<?php echo htmlspecialchars($user['bio']); ?>"</p>
                        <?php else: ?>
                            <p class="text-muted small mb-0">Chưa có tiểu sử.</p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-center gap-4 border-top pt-3 mt-auto">
                        <div>
                            <h4 class="fw-bold mb-0 text-dark"><?php echo $post_count; ?></h4>
                            <small class="text-muted">Bài viết</small>
                        </div>
                        <div style="border-left: 1px solid #e2e8f0;"></div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark"><?php echo $total_likes; ?></h4>
                            <small class="text-muted">Lượt thích</small>
                        </div>
                    </div>

                    <button class="btn btn-dark w-100 fw-bold rounded-pill mt-4 py-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fa-solid fa-user-pen me-2"></i> Chỉnh sửa hồ sơ
                    </button>
                </div>
            </div>

            <!-- THẺ ẢNH BÌA NGHỆ THUẬT (Cover Art Card) -->
            <div class="col-lg-8 col-md-7">
                <div class="card h-100 border-0 overflow-hidden position-relative cover-card" style="min-height: 280px;">
                    <?php if (!empty($user['cover_url'])): ?>
                        <img src="<?php echo htmlspecialchars($user['cover_url']); ?>" class="cover-modern" alt="Cover">
                    <?php else: ?>
                        <div class="w-100 h-100 d-flex justify-content-center align-items-center" style="background: linear-gradient(135deg, #cbd5e1, #94a3b8); position: absolute; top:0; left:0;">
                            <i class="fa-solid fa-image fa-5x text-white opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Lớp mờ đen bên dưới cùng để chữ nổi lên -->
                    <div class="cover-overlay"></div>
                    <div class="position-absolute bottom-0 start-0 p-4 p-md-5 text-white w-100">
                        <h3 class="fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i>Bộ sưu tập số</h3>
                        <p class="mb-0 opacity-75">Nơi lưu giữ các tác phẩm nghệ thuật và ý tưởng AI của bạn.</p>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- KHU VỰC DÒNG THỜI GIAN (ĐƯỢC CĂN GIỮA) -->
        <div class="row justify-content-center mt-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                    <i class="fa-solid fa-layer-group fa-lg text-primary me-2"></i>
                    <h5 class="fw-bold mb-0 text-dark">Dòng thời gian</h5>
                </div>

                <?php
                // Lấy tất cả bài viết CỦA RIÊNG USER NÀY
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
                        
                        $is_liked_class = ($post['user_liked'] > 0) ? 'liked text-primary' : '';
                        $like_icon = ($post['user_liked'] > 0) ? 'fa-solid text-primary' : 'fa-regular';
                ?>
                        <div class="card p-3 p-md-4 shadow-sm border-0 mb-4" style="border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($post['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" class="rounded-circle me-3 shadow-sm" style="width: 48px; height: 48px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3 fw-bold fs-5 shadow-sm" style="width: 48px; height: 48px;">
                                            <?php echo $first_letter; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                        <div class="text-muted d-flex align-items-center mt-1" style="font-size: 0.85rem;">
                                            <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?> 
                                            <span class="mx-2">•</span>
                                            <i class="fa-solid <?php echo $privacy_icon; ?>" title="Quyền riêng tư"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($post['ai_topic'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-2">
                                            <i class="fa-solid fa-microchip me-1"></i> <?php echo htmlspecialchars($post['ai_topic']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-light rounded-circle text-muted" type="button" data-bs-toggle="dropdown" style="width: 38px; height: 38px;">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px;">
                                            <li><a class="dropdown-item py-2" href="edit_post.php?id=<?php echo $post['id']; ?>"><i class="fa-solid fa-pen me-3"></i> Chỉnh sửa bài</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger py-2" href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Xóa bài này nhé?');"><i class="fa-solid fa-trash me-3"></i> Xóa bài viết</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mb-3 text-dark fs-6" style="white-space: pre-wrap; line-height: 1.6;"><?php echo htmlspecialchars($post['content']); ?></p>
                            
                            <?php if (!empty($post['generated_image_url'])): ?>
                                <div class="rounded-4 overflow-hidden mb-3 border">
                                    <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid w-100" alt="AI Image">
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between text-muted small px-2">
                                <span class="fw-bold"><i class="fa-solid fa-thumbs-up text-primary me-1"></i> <?php echo $post['like_count']; ?></span>
                                <span class="fw-bold"><?php echo $post['comment_count']; ?> bình luận</span>
                            </div>
                        </div>
                <?php 
                    } 
                } else {
                    echo '<div class="card p-5 text-center border-0 bg-transparent shadow-none mt-4">';
                    echo '<i class="fa-solid fa-box-open fa-4x text-muted opacity-25 mb-3"></i>';
                    echo '<h5 class="text-muted fw-bold">Trang cá nhân của bạn khá yên tĩnh</h5>';
                    echo '<p class="text-muted">Hãy tạo bài viết đầu tiên để chia sẻ với mọi người!</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- MODAL CHỈNH SỬA TRANG CÁ NHÂN -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-4">Hồ sơ cá nhân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="process_profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4 text-center">
                            <img id="avatar-preview-img" class="avatar-preview mb-3 shadow-sm border border-3 border-white">
                            <label class="form-label fw-bold text-dark w-100 text-start">Ảnh đại diện</label>
                            <input type="file" class="form-control bg-light" name="avatar" id="avatar-input" accept="image/*">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Ảnh bìa (Banner)</label>
                            <img id="cover-preview-img" class="img-preview mb-3 shadow-sm">
                            <input type="file" class="form-control bg-light" name="cover" id="cover-input" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Tên hiển thị</label>
                            <input type="text" class="form-control bg-light p-3" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Tiểu sử ngắn</label>
                            <textarea class="form-control bg-light p-3" name="bio" rows="3" placeholder="Giới thiệu nhanh về bạn..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Mật khẩu mới <small class="text-muted fw-normal">(Không bắt buộc)</small></label>
                            <input type="password" class="form-control bg-light p-3" name="new_password" placeholder="Chỉ nhập khi muốn đổi pass">
                        </div>

                        <div class="modal-footer border-top-0 px-0 pb-0 mt-2">
                            <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold py-3 fs-6 shadow-sm">Lưu cập nhật</button>
                        </div>
                    </form>
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

        // JS XEM TRƯỚC ẢNH
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
    </script>
</body>
</html>
<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php'; // Gọi helper để dùng hàm format_post_content

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Lấy tham số tìm kiếm (ưu tiên hashtag nếu có)
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$hashtag = isset($_GET['hashtag']) ? trim($_GET['hashtag']) : '';

$safe_keyword = $conn->real_escape_string($keyword);
$safe_hashtag = $conn->real_escape_string($hashtag);

// Đếm số thông báo chưa đọc
$notif_count_query = $conn->query("SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $current_user_id AND TB_DaDoc = 0");
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm | SocialAI</title>
    
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
        [data-bs-theme="dark"] .form-control { background-color: #334155; color: #f8fafc; border: none; }
        [data-bs-theme="dark"] .text-dark { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        .left-menu { position: sticky; top: 80px; }
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
        
        .search-user-card { transition: 0.2s; border: 1px solid transparent; }
        .search-user-card:hover { border-color: #0ea5e9; background-color: #f8fafc; }
        [data-bs-theme="dark"] .search-user-card:hover { background-color: #334155; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">
                <i class="fa-solid fa-earth-asia me-1"></i> SocialAI
            </a>
            
            <div class="d-none d-md-block w-25">
                <form action="search.php" method="GET" class="w-100 m-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 rounded-start-pill text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <!-- Nếu đang tìm hashtag thì ô search hiển thị rỗng, nếu tìm keyword thì hiện keyword -->
                        <input type="text" name="q" class="form-control bg-light border-0 rounded-end-pill" placeholder="Tìm kiếm bạn bè, bài viết..." value="<?php echo htmlspecialchars($keyword); ?>" required>
                    </div>
                </form>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button id="btn-darkmode" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-moon"></i>
                </button>
                
                <a href="profile.php" class="text-decoration-none">
                    <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm" data-bs-theme="light">
                        <img src="<?php echo !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']).'&background=random'; ?>" 
                             class="rounded-circle me-md-2" style="width: 32px; height: 32px; object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random';">
                        <span class="fw-bold fs-6 pe-2 text-dark d-none d-md-block"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3 d-none d-md-block left-menu">
                <ul class="nav flex-column font-weight-bold gap-1">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="index.php"><i class="fa-solid fa-house fa-fw me-2 text-primary"></i> Bảng tin</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="profile.php"><i class="fa-solid fa-user fa-fw me-2 text-success"></i> Trang cá nhân</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2" href="friends.php"><i class="fa-solid fa-user-group fa-fw me-2 text-info"></i> Bạn bè</a></li>
                    <hr class="my-2">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 bg-primary bg-opacity-10 text-primary fw-bold" href="#"><i class="fa-solid fa-magnifying-glass fa-fw me-2"></i> Kết quả tìm kiếm</a></li>
                </ul>
            </div>

            <div class="col-md-7">
                <div class="mb-4">
                    <?php if(!empty($safe_hashtag)): ?>
                        <h4 class="fw-bold text-dark">Hashtag: <span class="text-primary">#<?php echo htmlspecialchars($hashtag); ?></span></h4>
                    <?php elseif(!empty($safe_keyword)): ?>
                        <h4 class="fw-bold text-dark">Kết quả tìm kiếm cho: <span class="text-primary">"<?php echo htmlspecialchars($keyword); ?>"</span></h4>
                    <?php else: ?>
                        <h4 class="fw-bold text-dark">Vui lòng nhập từ khóa để tìm kiếm.</h4>
                    <?php endif; ?>
                </div>

                <!-- 1. KẾT QUẢ NGƯỜI DÙNG (Chỉ hiện khi tìm bằng Keyword bình thường) -->
                <?php
                if (!empty($safe_keyword)) {
                    $sql_users = "SELECT ND_Ma as id, ND_TaiKhoan as username, ND_HoTen as full_name, ND_AnhDaiDien as avatar_url 
                                  FROM NGUOI_DUNG 
                                  WHERE (ND_HoTen LIKE '%$safe_keyword%' OR ND_TaiKhoan LIKE '%$safe_keyword%') 
                                  AND ND_Ma != $current_user_id LIMIT 5";
                    $res_users = $conn->query($sql_users);

                    if ($res_users && $res_users->num_rows > 0) {
                        echo '<h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-users text-success me-2"></i>Mọi người</h5>';
                        echo '<div class="card p-2 shadow-sm mb-4">';
                        while ($u = $res_users->fetch_assoc()) {
                ?>
                            <div class="d-flex align-items-center justify-content-between p-3 search-user-card rounded-3">
                                <a href="profile.php?user_id=<?php echo $u['id']; ?>" class="d-flex align-items-center text-decoration-none flex-grow-1">
                                    <img src="<?php echo !empty($u['avatar_url']) ? $u['avatar_url'] : 'https://ui-avatars.com/api/?name='.urlencode($u['full_name']).'&background=random'; ?>" 
                                         class="rounded-circle me-3 border" style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($u['full_name']); ?>&background=random';">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($u['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($u['username']); ?></small>
                                    </div>
                                </a>
                                
                                <?php
                                $target_id = $u['id'];
                                $sql_friend = "SELECT BB_TrangThai as status, ND_Ma_Gui as sender_id 
                                               FROM BAN_BE 
                                               WHERE (ND_Ma_Gui=$current_user_id AND ND_Ma_Nhan=$target_id) 
                                                  OR (ND_Ma_Gui=$target_id AND ND_Ma_Nhan=$current_user_id)";
                                $check_friend = $conn->query($sql_friend);
                                
                                if ($check_friend->num_rows > 0) {
                                    $f_data = $check_friend->fetch_assoc();
                                    if ($f_data['status'] == 'accepted') {
                                        echo '<button class="btn btn-secondary btn-sm rounded-pill px-3" disabled><i class="fa-solid fa-user-check me-1"></i> Bạn bè</button>';
                                    } elseif ($f_data['sender_id'] == $current_user_id) {
                                        echo '<button class="btn btn-light btn-sm rounded-pill px-3" disabled>Đã gửi lời mời</button>';
                                    } else {
                                        echo '<a href="friends.php" class="btn btn-primary btn-sm rounded-pill px-3">Phản hồi</a>';
                                    }
                                } else {
                                    echo '<button class="btn btn-outline-primary btn-sm rounded-pill px-3 btn-action" data-action="add" data-id="'.$target_id.'"><i class="fa-solid fa-user-plus me-1"></i> Thêm</button>';
                                }
                                ?>
                            </div>
                <?php
                        }
                        echo '</div>';
                    }
                }
                ?>

                <!-- 2. KẾT QUẢ BÀI VIẾT (ÁP DỤNG TRUY VẤN JOIN ĐỈNH CAO CHO HASHTAG) -->
                <?php if(!empty($safe_keyword) || !empty($safe_hashtag)): ?>
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-newspaper text-primary me-2"></i>Bài viết</h5>
                <?php
                    // Khởi tạo các biến để ghép vào câu SQL
                    $join_tables = "";
                    $where_condition = "";

                    if (!empty($safe_hashtag)) {
                        // NẾU TÌM THEO HASHTAG: Liên kết 2 bảng BAI_VIET_HASHTAG và HASHTAG
                        $join_tables = " JOIN BAI_VIET_HASHTAG ph ON posts.BV_Ma = ph.BV_Ma 
                                         JOIN HASHTAG h ON ph.HT_Ma = h.HT_Ma ";
                        $where_condition = " h.HT_Ten = '$safe_hashtag' ";
                    } else {
                        // NẾU TÌM BẰNG TỪ KHÓA BÌNH THƯỜNG
                        $where_condition = " (posts.BV_NoiDung LIKE '%$safe_keyword%' OR posts.BV_ChuDeAI LIKE '%$safe_keyword%') ";
                    }

                    // Ráp câu lệnh SQL hoàn chỉnh bằng ngôn ngữ Tiếng Việt
                    $sql_posts = "SELECT posts.BV_Ma as id, posts.ND_Ma as user_id, posts.BV_NoiDung as content, 
                                         posts.BV_QuyenRiengTu as privacy, posts.BV_HinhAnh as image_url, 
                                         posts.BV_HinhAnhAI as generated_image_url, posts.BV_NgayDang as created_at, 
                                         posts.BV_ChuDeAI as ai_topic, 
                                         users.ND_HoTen as full_name, users.ND_AnhDaiDien as avatar_url,
                                         (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = posts.BV_Ma) as like_count,
                                         (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = posts.BV_Ma AND ND_Ma = $current_user_id) as user_liked,
                                         (SELECT COUNT(*) FROM BINH_LUAN WHERE BV_Ma = posts.BV_Ma) as comment_count
                                  FROM BAI_VIET posts 
                                  JOIN NGUOI_DUNG users ON posts.ND_Ma = users.ND_Ma 
                                  $join_tables
                                  WHERE $where_condition
                                    AND (posts.BV_QuyenRiengTu = 'public' 
                                     OR posts.ND_Ma = $current_user_id
                                     OR (posts.BV_QuyenRiengTu = 'friends' AND posts.ND_Ma IN (
                                         SELECT ND_Ma_Gui FROM BAN_BE WHERE ND_Ma_Nhan = $current_user_id AND BB_TrangThai = 'accepted'
                                         UNION
                                         SELECT ND_Ma_Nhan FROM BAN_BE WHERE ND_Ma_Gui = $current_user_id AND BB_TrangThai = 'accepted'
                                     )))
                                  ORDER BY posts.BV_NgayDang DESC";
                                  
                    $result_posts = $conn->query($sql_posts);

                    if ($result_posts && $result_posts->num_rows > 0) {
                        while($post = $result_posts->fetch_assoc()) {
                            $privacy_icon = 'fa-earth-asia';
                            if ($post['privacy'] == 'friends') $privacy_icon = 'fa-user-group';
                            if ($post['privacy'] == 'private') $privacy_icon = 'fa-lock';
                            $is_liked_class = ($post['user_liked'] > 0) ? 'liked' : '';
                            $like_icon = ($post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular';
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
                                                <i class="fa-solid <?php echo $privacy_icon; ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($post['ai_topic'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-microchip me-1"></i> <?php echo htmlspecialchars($post['ai_topic']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php 
                                    // 1. Biến Hashtag thành thẻ Link trước
                                    $formatted_content = format_post_content($post['content']);
                                    // 2. Nếu tìm kiếm từ khóa, bôi đậm từ khóa (ẩn bên ngoài thẻ HTML để không vỡ link)
                                    if (!empty($safe_keyword)) {
                                        $formatted_content = preg_replace('/('.preg_quote($keyword, '/').')(?![^<]*>)/iu', '<mark class="bg-warning rounded px-1">$1</mark>', $formatted_content);
                                    }
                                ?>
                                <p class="mb-2 mt-2 text-dark" style="white-space: pre-wrap;"><?php echo $formatted_content; ?></p>
                                
                                <?php if (!empty($post['generated_image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid rounded-3 border mb-2" alt="AI Generated Image">
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                                    <span><i class="fa-solid fa-thumbs-up text-primary me-1"></i> <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['like_count']; ?></span> lượt thích</span>
                                    <span><span id="comment-count-<?php echo $post['id']; ?>"><?php echo $post['comment_count']; ?></span> bình luận</span>
                                </div>

                                <hr class="my-2 opacity-25">
                                
                                <div class="d-flex justify-content-between px-md-2">
                                    <button class="btn btn-light fw-bold flex-grow-1 me-1 text-muted btn-like <?php echo $is_liked_class; ?>" data-post-id="<?php echo $post['id']; ?>">
                                        <i class="<?php echo $like_icon; ?> fa-thumbs-up me-1" id="like-icon-<?php echo $post['id']; ?>"></i> Thích
                                    </button>
                                    <a href="index.php#post-<?php echo $post['id']; ?>" class="btn btn-light fw-bold flex-grow-1 mx-1 text-muted">
                                        <i class="fa-regular fa-message me-1"></i> Bình luận
                                    </a>
                                </div>
                            </div>
                <?php 
                        } 
                    } else {
                        echo '<div class="card p-5 text-center border-0 bg-transparent shadow-none"><i class="fa-solid fa-box-open fa-3x text-muted opacity-25 mb-3"></i><p class="text-muted">Không tìm thấy bài viết nào chứa từ khóa này.</p></div>';
                    }
                endif;
                ?>
            </div>
            
            <div class="col-md-2"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const btnDarkMode = document.getElementById('btn-darkmode');
        const htmlElement = document.documentElement;
        const iconDarkMode = btnDarkMode.querySelector('i');
        const currentTheme = localStorage.getItem('theme') || 'light';
        setTheme(currentTheme);

        btnDarkMode.addEventListener('click', () => { setTheme(htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light'); });
        function setTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            iconDarkMode.className = theme === 'dark' ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
        }

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

        document.querySelectorAll('.btn-action').forEach(button => {
            button.addEventListener('click', async function() {
                const action = this.getAttribute('data-action');
                const targetId = this.getAttribute('data-id');
                const btn = this;
                btn.disabled = true;

                try {
                    const response = await fetch('api_friend.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: action, target_id: targetId })
                    });
                    const data = await response.json();

                    if(data.status === 'success' && action === 'add') {
                        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Đã gửi';
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-secondary');
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                    }
                } catch(e) { console.error(e); }
            });
        });
    </script>
</body>
</html>
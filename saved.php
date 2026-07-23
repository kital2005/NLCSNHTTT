<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];
$active_menu = 'saved'; // Kích hoạt màu sáng ở menu Đã lưu

$notif_count_query = $conn->query("SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $current_user_id AND TB_DaDoc = 0");
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
$is_user_admin = is_admin($conn, $current_user_id);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài viết đã lưu | SocialAI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <?php include 'includes/styles.php'; ?>
    <style>
        .comment-box { background-color: #f8fafc; border-radius: 12px; padding: 10px 15px; }
        [data-bs-theme="dark"] .comment-box { background-color: #0f172a; }
        .btn-like.liked { color: #0ea5e9 !important; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col-md-6">
                <!-- Tiêu đề trang -->
                <div class="mb-4 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fa-solid fa-bookmark fa-xl text-primary"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Kho lưu trữ</h4>
                        <small class="text-muted">Chỉ mình bạn xem được danh mục này</small>
                    </div>
                </div>

                <?php
                // TRUY VẤN: Lọc các bài viết dựa trên ID có trong bảng BAI_VIET_DA_LUU
                $sql_posts = "SELECT posts.BV_Ma as id, posts.ND_Ma as user_id, posts.BV_NoiDung as content, 
                                     posts.BV_QuyenRiengTu as privacy, posts.BV_HinhAnh as image_url, 
                                     posts.BV_HinhAnhAI as generated_image_url, posts.BV_NgayDang as created_at, 
                                     posts.BV_MaChiaSe as shared_post_id, posts.BV_ChuDeAI as ai_topic, 
                                     users.ND_HoTen as full_name, users.ND_AnhDaiDien as avatar_url,
                                     (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = posts.BV_Ma) as like_count,
                                     (SELECT COUNT(*) FROM LUOT_THICH WHERE BV_Ma = posts.BV_Ma AND ND_Ma = $current_user_id) as user_liked,
                                     (SELECT COUNT(*) FROM BINH_LUAN WHERE BV_Ma = posts.BV_Ma) as comment_count,
                                     1 as is_saved,
                                     sp.BV_NoiDung as shared_content, sp.BV_HinhAnh as shared_image_url, 
                                     sp.BV_HinhAnhAI as shared_generated_image_url, sp.BV_ChuDeAI as shared_ai_topic, 
                                     sp.BV_NgayDang as shared_created_at,
                                     su.ND_HoTen as shared_full_name, su.ND_AnhDaiDien as shared_avatar_url
                              FROM BAI_VIET_DA_LUU sv
                              JOIN BAI_VIET posts ON sv.BV_Ma = posts.BV_Ma
                              JOIN NGUOI_DUNG users ON posts.ND_Ma = users.ND_Ma 
                              LEFT JOIN BAI_VIET sp ON posts.BV_MaChiaSe = sp.BV_Ma
                              LEFT JOIN NGUOI_DUNG su ON sp.ND_Ma = su.ND_Ma
                              WHERE sv.ND_Ma = $current_user_id
                              ORDER BY sv.BVL_NgayLuu DESC";

                $result_posts = $conn->query($sql_posts);

                if ($result_posts && $result_posts->num_rows > 0) {
                    while($post = $result_posts->fetch_assoc()) {
                        $privacy_icon = 'fa-earth-asia';
                        if ($post['privacy'] == 'friends') $privacy_icon = 'fa-user-group';
                        if ($post['privacy'] == 'private') $privacy_icon = 'fa-lock';
                        
                        $is_liked_class = ($post['user_liked'] > 0) ? 'liked' : '';
                        $like_icon = ($post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular';
                        
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
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                        <div class="text-muted d-flex align-items-center" style="font-size: 0.85rem;">
                                            <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?> 
                                            <span class="mx-1">•</span>
                                            <i class="fa-solid <?php echo $privacy_icon; ?>" title="Quyền riêng tư"></i>
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
                                            <!-- Nút Bỏ lưu bài viết -->
                                            <li><a class="dropdown-item btn-save-post" href="javascript:void(0)" data-post-id="<?php echo $post['id']; ?>"><i class="fa-solid fa-bookmark text-primary me-2"></i> Bỏ lưu bài viết</a></li>
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
                                <button class="btn btn-light fw-bold flex-grow-1 ms-1 text-muted btn-share-post" data-post-id="<?php echo $post['shared_post_id'] ? $post['shared_post_id'] : $post['id']; ?>">
                                    <i class="fa-solid fa-share me-1"></i> Chia sẻ
                                </button>
                            </div>
                        </div>
                <?php 
                    } 
                } else {
                    echo '<div class="card p-5 text-center border-0 bg-transparent shadow-none mt-4"><i class="fa-solid fa-bookmark fa-4x text-muted opacity-25 mb-3"></i><h5 class="text-muted fw-bold">Chưa có bài viết nào được lưu</h5><p class="text-muted">Nhấn vào menu của bài viết để lưu lại xem sau nhé!</p></div>';
                }
                ?>
            </div>

            <div class="col-md-3 d-none d-md-block right-menu">
               <!-- Cột phải có thể để trống hoặc thêm gợi ý bạn bè/quảng cáo -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer_scripts.php'; ?>
    <script>
        // Xử lý Hủy lưu bài viết bằng AJAX
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
                        // Xóa thẻ bài viết khỏi màn hình mượt mà
                        const card = document.getElementById('post-' + postId);
                        if (card) {
                            card.style.transition = '0.3s';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                    }
                } catch(e) { console.error(e); }
            });
        });

        // Xử lý Chia sẻ bài viết bằng AJAX
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
                    } else {
                        alert(data.message);
                    }
                } catch(e) { console.error(e); }
                finally { btn.disabled = false; }
            });
        });

        // Xử lý Like bài viết
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
    </script>
</body>
</html>
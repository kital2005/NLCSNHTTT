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
    <title>Bạn bè | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f8; }
        [data-bs-theme="dark"] body { background-color: #0f172a; }
        .navbar-custom { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        [data-bs-theme="dark"] .navbar-custom { background: rgba(30, 41, 59, 0.95); border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        /* ĐỔI MỚI THIẾT KẾ THẺ BẠN BÈ: CHUYỂN SANG DẠNG NGANG NHỎ GỌN */
        .friend-row-card { 
            border: none; 
            border-radius: 16px; 
            background: #fff; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.02); 
            transition: all 0.2s ease;
            margin-bottom: 15px;
        }
        .friend-row-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            border-color: #e2e8f0;
        }
        [data-bs-theme="dark"] .friend-row-card { background-color: #1e293b; color: #f8fafc; }
        
        .avatar-compact {
            width: 65px; 
            height: 65px; 
            object-fit: cover; 
            border-radius: 50%;
            border: 2px solid #f1f5f9;
        }
        [data-bs-theme="dark"] .avatar-compact { border-color: #334155; }
        
        .left-menu { position: sticky; top: 80px; }
        .section-title { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; color: #64748b; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">
                <i class="fa-solid fa-earth-asia me-1"></i> SocialAI
            </a>
            <div class="d-flex align-items-center gap-2">
                <button id="btn-darkmode" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <a href="profile.php" class="text-decoration-none">
                    <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm" data-bs-theme="light">
                        <?php if (!empty($_SESSION['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="rounded-circle me-md-2" style="width: 32px; height: 32px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-md-2" style="width: 32px; height: 32px; flex-shrink: 0;">
                                <?php echo mb_substr($_SESSION['full_name'], 0, 1, "UTF-8"); ?>
                            </div>
                        <?php endif; ?>
                        <span class="fw-bold fs-6 pe-2 text-dark text-nowrap d-none d-md-inline-block text-truncate" style="max-width: 120px;">
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3 d-none d-md-block left-menu">
                <ul class="nav flex-column font-weight-bold gap-1">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="index.php"><i class="fa-solid fa-house fa-fw me-2 text-primary"></i> Bảng tin</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="profile.php"><i class="fa-solid fa-user fa-fw me-2 text-success"></i> Trang cá nhân</a></li>
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 bg-info bg-opacity-10 text-info fw-bold" href="friends.php"><i class="fa-solid fa-user-group fa-fw me-2"></i> Bạn bè</a></li>
                    <hr class="my-2">
                    <li class="nav-item"><a class="nav-link text-dark rounded-3 px-3 py-2 custom-hover" href="#"><i class="fa-solid fa-users fa-fw me-2 text-warning"></i> Nhóm (Sắp ra mắt)</a></li>
                </ul>
            </div>

            <div class="col-md-9">
                <div class="bg-white p-4 rounded-4 shadow-sm" data-bs-theme="light">
                    
                    <h6 class="fw-bold section-title"><i class="fa-solid fa-bell text-primary me-2"></i> Lời mời kết bạn đang chờ</h6>
                    <div class="row mb-5">
                        <?php
                        $sql_requests = "SELECT u.* FROM users u JOIN friends f ON u.id = f.sender_id WHERE f.receiver_id = $current_user_id AND f.status = 'pending'";
                        $res_requests = $conn->query($sql_requests);
                        
                        if ($res_requests && $res_requests->num_rows > 0) {
                            while($req = $res_requests->fetch_assoc()) {
                                $initial = mb_substr($req['full_name'], 0, 1, "UTF-8");
                        ?>
                        <div class="col-md-6 user-card-wrapper" id="user-card-<?php echo $req['id']; ?>">
                            <div class="friend-row-card p-3 border">
                                <div class="d-flex align-items-center">
                                    <?php if(!empty($req['avatar_url'])): ?>
                                        <img src="<?php echo $req['avatar_url']; ?>" class="avatar-compact me-3">
                                    <?php else: ?>
                                        <div class="avatar-compact d-flex justify-content-center align-items-center fw-bold fs-4 text-white bg-primary me-3"><?php echo $initial; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($req['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($req['username']); ?></small>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary rounded-pill px-3 fw-bold btn-sm btn-action" data-action="accept" data-id="<?php echo $req['id']; ?>">Đồng ý</button>
                                        <button class="btn btn-light rounded-pill px-3 fw-bold btn-sm btn-action border text-muted" data-action="cancel" data-id="<?php echo $req['id']; ?>">Xóa</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                            echo '<div class="col-12"><p class="text-muted fst-italic px-2">Bạn không có lời mời kết bạn nào.</p></div>';
                        }
                        ?>
                    </div>

                    <h6 class="fw-bold section-title"><i class="fa-solid fa-wand-magic-sparkles text-success me-2"></i> Có thể bạn biết</h6>
                    <div class="row mb-5">
                        <?php
                        $sql_suggest = "SELECT * FROM users WHERE id != $current_user_id AND id NOT IN (
                                            SELECT sender_id FROM friends WHERE receiver_id = $current_user_id
                                            UNION
                                            SELECT receiver_id FROM friends WHERE sender_id = $current_user_id
                                        ) LIMIT 6";
                        $res_suggest = $conn->query($sql_suggest);
                        
                        if ($res_suggest && $res_suggest->num_rows > 0) {
                            while($sug = $res_suggest->fetch_assoc()) {
                                $initial = mb_substr($sug['full_name'], 0, 1, "UTF-8");
                        ?>
                        <div class="col-md-6 user-card-wrapper" id="user-card-<?php echo $sug['id']; ?>">
                            <div class="friend-row-card p-3 border">
                                <div class="d-flex align-items-center">
                                    <?php if(!empty($sug['avatar_url'])): ?>
                                        <img src="<?php echo $sug['avatar_url']; ?>" class="avatar-compact me-3">
                                    <?php else: ?>
                                        <div class="avatar-compact d-flex justify-content-center align-items-center fw-bold fs-4 text-white bg-success me-3"><?php echo $initial; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($sug['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($sug['username']); ?></small>
                                    </div>
                                    
                                    <div>
                                        <button class="btn btn-outline-primary rounded-pill px-3 fw-bold btn-sm btn-action" id="btn-add-<?php echo $sug['id']; ?>" data-action="add" data-id="<?php echo $sug['id']; ?>">
                                            <i class="fa-solid fa-user-plus me-1"></i> Thêm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                            echo '<div class="col-12"><p class="text-muted fst-italic px-2">Tạm thời chưa có gợi ý mới.</p></div>';
                        }
                        ?>
                    </div>

                    <h6 class="fw-bold section-title"><i class="fa-solid fa-address-book text-info me-2"></i> Danh bạ của bạn</h6>
                    <div class="row">
                        <?php
                        $sql_friends = "SELECT u.* FROM users u JOIN friends f ON (u.id = f.sender_id OR u.id = f.receiver_id) 
                                        WHERE (f.sender_id = $current_user_id OR f.receiver_id = $current_user_id) 
                                        AND u.id != $current_user_id AND f.status = 'accepted'";
                        $res_friends = $conn->query($sql_friends);
                        
                        if ($res_friends && $res_friends->num_rows > 0) {
                            while($fr = $res_friends->fetch_assoc()) {
                                $initial = mb_substr($fr['full_name'], 0, 1, "UTF-8");
                        ?>
                        <div class="col-md-6 user-card-wrapper" id="user-card-<?php echo $fr['id']; ?>">
                            <div class="friend-row-card p-3 border bg-light">
                                <div class="d-flex align-items-center">
                                    <?php if(!empty($fr['avatar_url'])): ?>
                                        <img src="<?php echo $fr['avatar_url']; ?>" class="avatar-compact me-3">
                                    <?php else: ?>
                                        <div class="avatar-compact d-flex justify-content-center align-items-center fw-bold fs-4 text-white bg-info me-3"><?php echo $initial; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($fr['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($fr['username']); ?></small>
                                    </div>
                                    
                                    <div>
                                        <button class="btn btn-light rounded-pill px-3 fw-bold btn-sm text-danger border btn-action" data-action="unfriend" data-id="<?php echo $fr['id']; ?>">
                                            Hủy bạn
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } else {
                            echo '<div class="col-12"><p class="text-muted fst-italic px-2">Bạn chưa có người bạn nào trên hệ thống.</p></div>';
                        }
                        ?>
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
            setTheme(htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light');
        });

        function setTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            iconDarkMode.className = theme === 'dark' ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
        }

        // XỬ LÝ AJAX KẾT BẠN
        document.querySelectorAll('.btn-action').forEach(button => {
            button.addEventListener('click', async function() {
                const action = this.getAttribute('data-action');
                const targetId = this.getAttribute('data-id');
                const cardWrapper = document.getElementById('user-card-' + targetId);
                const btn = this;

                btn.disabled = true;

                try {
                    const response = await fetch('api_friend.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: action, target_id: targetId })
                    });
                    const data = await response.json();

                    if(data.status === 'success') {
                        if(action === 'add') {
                            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Đã gửi';
                            btn.classList.remove('btn-outline-primary');
                            btn.classList.add('btn-secondary');
                        } 
                        else if (action === 'accept' || action === 'cancel' || action === 'unfriend') {
                            cardWrapper.style.transition = "opacity 0.3s, transform 0.3s";
                            cardWrapper.style.opacity = "0";
                            cardWrapper.style.transform = "scale(0.9)";
                            setTimeout(() => { cardWrapper.remove(); }, 300);
                        }
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                    }
                } catch(e) {
                    console.error(e);
                    alert("Có lỗi kết nối mạng!");
                    btn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
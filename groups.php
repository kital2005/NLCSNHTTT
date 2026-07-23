<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$current_user_id = $_SESSION['user_id'];
$active_menu = 'groups';

// Đã cập nhật bảng THONG_BAO
$sql_notif = "SELECT COUNT(*) as total FROM THONG_BAO WHERE ND_Ma_Nhan = $current_user_id AND TB_DaDoc = 0";
$notif_count_query = $conn->query($sql_notif);
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
$is_user_admin = is_admin($conn, $current_user_id);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Nhóm cộng đồng | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .group-hero-banner { background: linear-gradient(135deg, #3b82f6, #8b5cf6); border-radius: 16px; padding: 40px 30px; color: white; margin-bottom: 30px; position: relative; overflow: hidden; }
        .group-hero-banner i { position: absolute; right: -20px; bottom: -30px; font-size: 150px; opacity: 0.15; transform: rotate(-15deg); }
        .group-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.04); background: #fff; height: 100%; display: flex; flex-direction: column; transition: transform 0.3s ease; }
        .group-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        [data-bs-theme="dark"] .group-card { background-color: #1e293b; }
        .group-cover { height: 140px; object-fit: cover; width: 100%; background: #e2e8f0; }
        .group-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .group-title { font-size: 1.15rem; font-weight: 800; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .group-desc { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; flex-grow: 1;}
        [data-bs-theme="dark"] .group-desc { color: #94a3b8; }
        .section-title { font-size: 1.2rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; color: #475569; }
        [data-bs-theme="dark"] .section-title { color: #cbd5e1; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="col-md-9">
                <div class="group-hero-banner shadow-sm">
                    <h2 class="fw-bold mb-2">Khám phá Cộng đồng</h2>
                    <p class="mb-0 opacity-75 fs-6">Tham gia các nhóm chất lượng, chia sẻ đam mê và học hỏi cùng mọi người.</p>
                    <i class="fa-solid fa-people-group"></i>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">Quản lý Nhóm</h5>
                    <button class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                        <i class="fa-solid fa-plus me-1"></i> Tạo nhóm mới
                    </button>
                </div>

                <h6 class="section-title"><i class="fa-solid fa-layer-group text-primary me-2"></i> Nhóm của bạn</h6>
                <div class="row mb-5">
                    <?php
                    // CẬP NHẬT: Dùng bảng NHOM và THANH_VIEN_NHOM
                    $sql = "SELECT g.N_Ma as id, g.N_Ten as name, g.N_MoTa as description, 
                                   g.N_AnhBia as cover_url, g.N_QuyenRiengTu as privacy, 
                                   (SELECT COUNT(*) FROM THANH_VIEN_NHOM WHERE N_Ma = g.N_Ma AND TVN_VaiTro != 'pending') as mem_cnt 
                            FROM NHOM g 
                            JOIN THANH_VIEN_NHOM gm ON g.N_Ma = gm.N_Ma 
                            WHERE gm.ND_Ma = $current_user_id AND gm.TVN_VaiTro != 'pending'";
                    
                    $res = $conn->query($sql);
                    if ($res && $res->num_rows > 0) {
                        while($grp = $res->fetch_assoc()) {
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="group-card border">
                            <img src="<?php echo htmlspecialchars($grp['cover_url']); ?>" class="group-cover" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($grp['name']); ?>&background=random&size=500';">
                            <div class="group-content">
                                <h6 class="group-title text-dark">
                                    <?php echo htmlspecialchars($grp['name']); ?>
                                    <?php if($grp['privacy']=='private') echo '<i class="fa-solid fa-lock ms-1 text-muted fs-6" title="Nhóm kín"></i>'; ?>
                                </h6>
                                <p class="group-desc"><?php echo htmlspecialchars($grp['description'] ? $grp['description'] : 'Chưa có mô tả'); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <small class="text-muted fw-bold"><i class="fa-solid fa-user-group me-1"></i> <?php echo $grp['mem_cnt']; ?> TV</small>
                                    <a href="group_detail.php?id=<?php echo $grp['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">Vào Nhóm</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }} else { echo '<div class="col-12"><div class="p-4 bg-light rounded-4 text-center text-muted fst-italic">Bạn chưa tham gia nhóm nào.</div></div>'; } ?>
                </div>

                <h6 class="section-title"><i class="fa-solid fa-compass text-success me-2"></i> Khám phá Nhóm mới</h6>
                <div class="row mb-5">
                    <?php
                    // CẬP NHẬT: Gợi ý các nhóm chưa tham gia
                    $sql_discover = "SELECT g.N_Ma as id, g.N_Ten as name, g.N_MoTa as description, 
                                            g.N_AnhBia as cover_url, g.N_QuyenRiengTu as privacy, 
                                            (SELECT COUNT(*) FROM THANH_VIEN_NHOM WHERE N_Ma = g.N_Ma AND TVN_VaiTro != 'pending') as mem_cnt 
                                     FROM NHOM g 
                                     WHERE g.N_Ma NOT IN (SELECT N_Ma FROM THANH_VIEN_NHOM WHERE ND_Ma = $current_user_id) LIMIT 9";
                    
                    $res_discover = $conn->query($sql_discover);
                    if ($res_discover && $res_discover->num_rows > 0) {
                        while($sug = $res_discover->fetch_assoc()) {
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="group-card border bg-light">
                            <img src="<?php echo htmlspecialchars($sug['cover_url']); ?>" class="group-cover opacity-75" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($sug['name']); ?>&background=random&size=500';">
                            <div class="group-content">
                                <h6 class="group-title text-dark">
                                    <?php echo htmlspecialchars($sug['name']); ?>
                                    <?php if($sug['privacy']=='private') echo '<i class="fa-solid fa-lock ms-1 text-muted fs-6" title="Nhóm kín"></i>'; ?>
                                </h6>
                                <p class="group-desc"><?php echo htmlspecialchars($sug['description'] ? $sug['description'] : 'Chưa có mô tả'); ?></p>
                                <div class="mt-auto">
                                    <div class="mb-2 small text-muted fw-bold"><i class="fa-solid fa-user-group me-1"></i> <?php echo $sug['mem_cnt']; ?> thành viên</div>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold btn-join-group bg-white" data-id="<?php echo $sug['id']; ?>">
                                        Tham gia ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }} else { echo '<div class="col-12"><div class="p-4 bg-light rounded-4 text-center text-muted fst-italic">Hiện không có nhóm mới để gợi ý.</div></div>'; } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TẠO NHÓM MỚI -->
    <div class="modal fade" id="createGroupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-4">Tạo nhóm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="form-create-group">
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold text-dark small w-100 text-start">Ảnh bìa nhóm (Tùy chọn)</label>
                            <label class="w-100 pb-3 pt-3" style="border: 2px dashed #cbd5e1; border-radius: 12px; cursor: pointer; transition: 0.3s;" id="upload-box">
                                <i class="fa-regular fa-image text-primary fs-4 mb-1"></i>
                                <div class="fw-bold text-muted small">Nhấn để chọn ảnh bìa</div>
                                <input type="file" class="d-none" id="group-cover-input" accept="image/*">
                            </label>
                            <img id="group-cover-preview" class="d-none mt-2 rounded-3 w-100" style="height: 120px; object-fit: cover; border: 1px solid #e2e8f0;">
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="groupName" placeholder="Tên nhóm" required>
                            <label for="groupName" class="text-muted">Tên nhóm</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select fw-bold" id="groupPrivacy">
                                <option value="public">Công khai (Ai cũng có thể tham gia)</option>
                                <option value="private">Riêng tư (Phải được Chủ nhóm duyệt)</option>
                            </select>
                            <label for="groupPrivacy" class="text-muted">Quyền riêng tư</label>
                        </div>
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Mô tả" id="groupDesc" style="height: 100px"></textarea>
                            <label for="groupDesc" class="text-muted">Mô tả mục đích của nhóm</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-3 fs-6 shadow-sm" id="btn-submit-group">Tạo nhóm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer_scripts.php'; ?>
    <script>
        document.getElementById('group-cover-input').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('group-cover-preview').src = ev.target.result;
                    document.getElementById('group-cover-preview').classList.remove('d-none');
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('form-create-group').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-group');
            const name = document.getElementById('groupName').value;
            const desc = document.getElementById('groupDesc').value;
            const privacy = document.getElementById('groupPrivacy').value;
            const coverFile = document.getElementById('group-cover-input').files[0];

            if(!name.trim()) return alert("Tên nhóm không được để trống!");
            btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang tạo...';

            const formData = new FormData();
            formData.append('action', 'create'); formData.append('name', name); formData.append('description', desc); formData.append('privacy', privacy);
            if(coverFile) formData.append('cover', coverFile);

            try {
                const res = await fetch('api_group.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.status === 'success') location.reload();
                else { alert(data.message); btn.disabled = false; btn.innerHTML = 'Tạo nhóm'; }
            } catch(err) { console.error(err); alert("Lỗi mạng!"); btn.disabled = false; btn.innerHTML = 'Tạo nhóm'; }
        });

        document.querySelectorAll('.btn-join-group').forEach(btn => {
            btn.addEventListener('click', async function() {
                const groupId = this.dataset.id; this.disabled = true;
                try {
                    const res = await fetch('api_group.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'join', group_id: groupId }) });
                    const data = await res.json();
                    if(data.status === 'pending') { alert(data.message); location.reload(); }
                    else if(data.status === 'success') location.reload();
                } catch(e) { console.error(e); this.disabled = false; }
            });
        });
    </script>
</body>
</html>
<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = intval($_SESSION['user_id']);
if (!is_admin($conn, $current_user_id)) {
    header("Location: index.php");
    exit();
}

$active_menu = 'admin';

$stats = [
    'users' => (int)$conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'],
    'posts' => (int)$conn->query("SELECT COUNT(*) as c FROM posts")->fetch_assoc()['c'],
    'comments' => (int)$conn->query("SELECT COUNT(*) as c FROM comments")->fetch_assoc()['c'],
    'ai_logs' => table_exists($conn, 'ai_logs') ? (int)$conn->query("SELECT COUNT(*) as c FROM ai_logs")->fetch_assoc()['c'] : 0,
];

$users_res = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM posts WHERE user_id=u.id) as post_count FROM users u ORDER BY u.created_at DESC LIMIT 50");
$posts_res = $conn->query("SELECT p.*, u.full_name FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
</head>
<body>

<?php
$notif_count_query = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $current_user_id AND is_read = 0");
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
$is_user_admin = true;
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <div class="col-md-9">
            <h4 class="fw-bold mb-4"><i class="fa-solid fa-shield-halved me-2 text-danger"></i>Bảng quản trị</h4>

            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="kpi-card shadow-sm bg-white p-3"><div class="text-muted small">Người dùng</div><div class="fs-3 fw-bold"><?php echo $stats['users']; ?></div></div></div>
                <div class="col-md-3"><div class="kpi-card shadow-sm bg-white p-3"><div class="text-muted small">Bài viết</div><div class="fs-3 fw-bold text-primary"><?php echo $stats['posts']; ?></div></div></div>
                <div class="col-md-3"><div class="kpi-card shadow-sm bg-white p-3"><div class="text-muted small">Bình luận</div><div class="fs-3 fw-bold text-success"><?php echo $stats['comments']; ?></div></div></div>
                <div class="col-md-3"><div class="kpi-card shadow-sm bg-white p-3"><div class="text-muted small">Log AI</div><div class="fs-3 fw-bold text-purple"><?php echo $stats['ai_logs']; ?></div></div></div>
            </div>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-users">Người dùng</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-posts">Bài viết</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-users">
                    <div class="card shadow-sm overflow-hidden">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light"><tr><th>ID</th><th>Tên</th><th>Email/Username</th><th>Bài viết</th><th>Vai trò</th><th>Thao tác</th></tr></thead>
                            <tbody>
                            <?php while ($u = $users_res->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo $u['post_count']; ?></td>
                                    <td><span class="badge bg-<?php echo ($u['role'] ?? 'user') === 'admin' ? 'danger' : 'secondary'; ?>"><?php echo $u['role'] ?? 'user'; ?></span></td>
                                    <td>
                                        <?php if ($u['id'] != $current_user_id): ?>
                                        <button class="btn btn-sm btn-outline-danger btn-admin-action" data-action="toggle_role" data-id="<?php echo $u['id']; ?>">Đổi role</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-posts">
                    <div class="card shadow-sm overflow-hidden">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light"><tr><th>ID</th><th>Người đăng</th><th>Nội dung</th><th>Quyền riêng tư</th><th>Thao tác</th></tr></thead>
                            <tbody>
                            <?php while ($p = $posts_res->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                                    <td class="text-truncate" style="max-width:200px"><?php echo htmlspecialchars($p['content']); ?></td>
                                    <td><?php echo $p['privacy']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger btn-admin-action" data-action="delete_post" data-id="<?php echo $p['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer_scripts.php'; ?>
<script>
document.querySelectorAll('.btn-admin-action').forEach(btn => {
    btn.addEventListener('click', async function() {
        const action = this.dataset.action;
        const id = this.dataset.id;
        if (action === 'delete_post' && !confirm('Xóa bài viết này?')) return;
        const res = await fetch('api_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, id: parseInt(id) })
        });
        const data = await res.json();
        alert(data.message || (data.status === 'success' ? 'OK' : 'Lỗi'));
        if (data.status === 'success') location.reload();
    });
});
</script>
</body>
</html>

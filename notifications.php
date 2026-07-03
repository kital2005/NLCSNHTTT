<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = intval($_SESSION['user_id']);
$active_menu = 'notifications';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$valid_filters = ['all', 'like', 'comment', 'friend_request', 'friend_accept', 'ai_complete'];
if (!in_array($filter, $valid_filters)) $filter = 'all';

$where = "n.user_id = $current_user_id";
if ($filter !== 'all') {
    $f = $conn->real_escape_string($filter);
    $where .= " AND n.type = '$f'";
}

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$total = (int)$conn->query("SELECT COUNT(*) as c FROM notifications n WHERE $where")->fetch_assoc()['c'];
$total_pages = max(1, ceil($total / $per_page));

$sql = "SELECT n.*, u.full_name, u.avatar_url FROM notifications n JOIN users u ON n.sender_id = u.id WHERE $where ORDER BY n.created_at DESC LIMIT $per_page OFFSET $offset";
$res = $conn->query($sql);

$notif_count_query = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $current_user_id AND is_read = 0");
$unread_notif_count = $notif_count_query->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <div class="col-md-9">
            <div class="card p-4 shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-bell me-2 text-warning"></i>Thông báo</h4>
                    <button id="btn-mark-all" class="btn btn-outline-primary btn-sm rounded-pill">Đánh dấu tất cả đã đọc</button>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-4">
                    <?php
                    $filters = [
                        'all' => 'Tất cả',
                        'like' => 'Thích',
                        'comment' => 'Bình luận',
                        'friend_request' => 'Lời mời',
                        'friend_accept' => 'Kết bạn',
                        'ai_complete' => 'AI'
                    ];
                    foreach ($filters as $key => $label):
                        $active = ($filter === $key) ? 'active' : '';
                    ?>
                    <a href="?filter=<?php echo $key; ?>" class="btn btn-sm rounded-pill <?php echo $active ? 'btn-primary' : 'btn-light'; ?>"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>

                <?php if ($res && $res->num_rows > 0): ?>
                    <div class="list-group list-group-flush">
                    <?php while ($notif = $res->fetch_assoc()):
                        $link = notif_link($notif['type'], $notif['post_id']);
                        $icon = notif_icon_class($notif['type']);
                        $unread = ($notif['is_read'] == 0);
                    ?>
                        <a href="<?php echo $link; ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-2 notif-page-item <?php echo $unread ? 'unread-bg' : ''; ?>" data-notif-id="<?php echo $notif['id']; ?>" style="padding: 14px;">
                            <div class="d-flex align-items-start">
                                <div class="position-relative me-3 flex-shrink-0">
                                    <img src="<?php echo avatar_url($notif['full_name'], $notif['avatar_url']); ?>" class="notif-icon shadow-sm" alt="">
                                    <i class="<?php echo $icon; ?> position-absolute bottom-0 end-0 bg-white rounded-circle p-1" style="font-size: 0.7rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 text-dark" style="font-size: 0.95rem;">
                                        <strong><?php echo htmlspecialchars($notif['full_name']); ?></strong>
                                        <?php echo ' ' . strip_tags(notif_text($notif['type']), '<b>'); ?>
                                    </p>
                                    <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo time_ago($notif['created_at']); ?></small>
                                </div>
                                <?php if ($unread): ?>
                                    <span class="badge bg-primary rounded-pill align-self-center">Mới</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link rounded-circle mx-1" href="?filter=<?php echo $filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-bell-slash fa-4x opacity-25 mb-3"></i>
                        <h5>Không có thông báo</h5>
                        <p class="small">Khi có người thích, bình luận hoặc kết bạn, bạn sẽ thấy ở đây.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer_scripts.php'; ?>
<script>
document.querySelectorAll('.notif-page-item').forEach(item => {
    item.addEventListener('click', function() {
        const id = this.dataset.notifId;
        fetch('api_notif_read_one.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(id) })
        });
    });
});

document.getElementById('btn-mark-all').addEventListener('click', async function() {
    await fetch('api_read_notif.php', { method: 'POST' });
    document.querySelectorAll('.unread-bg').forEach(el => el.classList.remove('unread-bg'));
    document.querySelectorAll('.badge.bg-primary').forEach(el => el.remove());
    const badge = document.getElementById('notif-badge');
    if (badge) badge.classList.add('d-none');
});
</script>
</body>
</html>

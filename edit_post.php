<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin bài viết từ bảng BAI_VIET và dùng AS để giữ nguyên biến HTML
$query = "SELECT BV_Ma as id, BV_NoiDung as content, BV_HinhAnhAI as generated_image_url, BV_QuyenRiengTu as privacy 
          FROM BAI_VIET 
          WHERE BV_Ma = $post_id AND ND_Ma = $user_id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<script>alert('Bạn không có quyền sửa bài viết này!'); window.location.href='index.php';</script>";
    exit();
}

$post = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa bài viết - SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f8; }
        .navbar-custom { background: rgba(255, 255, 255, 0.95); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04); }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom py-2 mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>
    </nav>

    <div class="container d-flex justify-content-center">
        <div class="card p-4 col-md-6">
            <h5 class="fw-bold mb-3">Chỉnh sửa bài viết</h5>
            <form action="process_edit_post.php" method="POST">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                
                <textarea class="form-control mb-3 bg-light border-0 p-3" name="content" rows="4" style="resize: none;" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                
                <?php if (!empty($post['generated_image_url'])): ?>
                    <div class="mb-3">
                        <small class="text-muted"><i class="fa-solid fa-wand-magic-sparkles"></i> Ảnh AI đi kèm (Không thể thay đổi)</small>
                        <img src="<?php echo htmlspecialchars($post['generated_image_url']); ?>" class="img-fluid rounded-3 mt-1 opacity-75">
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <select name="privacy" class="form-select border-0 bg-light fw-bold text-muted" style="width: auto;">
                        <option value="public" <?php echo ($post['privacy'] == 'public') ? 'selected' : ''; ?>>&#xf0ac; Công khai</option>
                        <option value="friends" <?php echo ($post['privacy'] == 'friends') ? 'selected' : ''; ?>>&#xf0c0; Bạn bè</option>
                        <option value="private" <?php echo ($post['privacy'] == 'private') ? 'selected' : ''; ?>>&#xf023; Chỉ mình tôi</option>
                    </select>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
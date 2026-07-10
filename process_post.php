<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $content = $conn->real_escape_string(trim($_POST['content']));
    $privacy = isset($_POST['privacy']) ? $conn->real_escape_string($_POST['privacy']) : 'public';

    $ai_topic = isset($_POST['ai_topic']) ? $conn->real_escape_string($_POST['ai_topic']) : '';
    $ai_sentiment = isset($_POST['ai_sentiment']) ? $conn->real_escape_string($_POST['ai_sentiment']) : '';
    $generated_image_url = isset($_POST['ai_image_url']) ? $conn->real_escape_string($_POST['ai_image_url']) : '';
    $image_url = '';

    if (empty($content)) {
        header("Location: index.php");
        exit();
    }

    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['post_image']['tmp_name']);
        finfo_close($finfo);

        if (in_array($mime, $allowed) && $_FILES['post_image']['size'] <= 5 * 1024 * 1024) {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            $ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
            $filename = 'post_' . $user_id . '_' . time() . '.' . preg_replace('/[^a-z0-9]/i', '', $ext);
            $dest = 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $dest)) {
                $image_url = $conn->real_escape_string($dest);
            }
        }
    }

    $query = "INSERT INTO posts (user_id, content, privacy, ai_topic, ai_sentiment, image_url, generated_image_url)
              VALUES ($user_id, '$content', '$privacy', '$ai_topic', '$ai_sentiment', " .
              ($image_url ? "'$image_url'" : "NULL") . ", " .
              ($generated_image_url ? "'$generated_image_url'" : "NULL") . ")";
        if ($conn->query($query)) {
        $post_id = $conn->insert_id;

        // Xử lý Hashtag: Tìm tất cả các từ bắt đầu bằng dấu #
        preg_match_all('/#(\w+)/u', $_POST['content'], $matches);
        $tags = array_unique($matches[1]);

        foreach ($tags as $tag) {
            $tag = $conn->real_escape_string($tag);
            
            // 1. Kiểm tra hashtag đã tồn tại chưa, nếu chưa thì thêm mới
            $conn->query("INSERT IGNORE INTO hashtags (tag_name) VALUES ('$tag')");
            
            // 2. Lấy id của hashtag
            $res = $conn->query("SELECT id FROM hashtags WHERE tag_name = '$tag'");
            if ($row = $res->fetch_assoc()) {
                $hashtag_id = $row['id'];
                // 3. Liên kết bài viết với hashtag
                $conn->query("INSERT IGNORE INTO post_hashtags (post_id, hashtag_id) VALUES ($post_id, $hashtag_id)");
            }
        }
    }

    header("Location: index.php");
    exit();
}
?>

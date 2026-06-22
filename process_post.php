<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = $conn->real_escape_string(trim($_POST['content']));
    $post_type = $_POST['post_type']; 
    
    if (empty($content)) {
        header("Location: index.php");
        exit();
    }

    // 1. Lưu bài viết vào Database
    $query = "INSERT INTO posts (user_id, content) VALUES ('$user_id', '$content')";
    $conn->query($query);
    $post_id = $conn->insert_id; 

    // 2. Xử lý AI nếu người dùng bấm nút "Tự vẽ ảnh AI"
    if ($post_type === 'ai_draw') {
        
        // Ép XAMPP và Python sử dụng chung ngôn ngữ UTF-8 để không hỏng JSON
        putenv('PYTHONIOENCODING=utf-8');
        
        $content_escaped = escapeshellarg($content);
        
        // Thêm 2>&1 để gom toàn bộ thông báo lỗi của hệ thống (nếu có) vào biến $output
        $command = "python ai_engine.py $content_escaped 2>&1";
        $output = shell_exec($command);
        
        $result = json_decode($output, true);

        // BẮT LỖI TẠI CHỖ: Nếu Python không trả về JSON hợp lệ, hiện màn hình thông báo ngay!
        if ($result === null) {
            die("<div style='padding:30px; background:#fff0f0; color:#d8000c; font-family:sans-serif; border:1px solid #d8000c; border-radius:10px; max-width:800px; margin:50px auto; line-height:1.6;'>
                    <h2 style='margin-top:0;'><i class='fa-solid fa-triangle-exclamation'></i> Cảnh báo: Hệ thống AI không phản hồi!</h2>
                    <p>PHP đã gọi Python nhưng bị hệ thống (Windows/XAMPP) chặn hoặc văng lỗi.</p>
                    <hr>
                    <p><strong>Lệnh đã chạy:</strong> <code style='background:#ddd; padding:2px 5px;'>$command</code></p>
                    <p><strong>Kết quả bắt được (Lỗi thô):</strong></p>
                    <pre style='background:#222; color:#0f0; padding:15px; border-radius:5px; overflow-x:auto;'>$output</pre>
                    <hr>
                    <p><em>Tuấn hãy chụp lại toàn bộ màn hình này để mình phân tích và cấu hình lại đường dẫn Python nhé!</em></p>
                    <a href='index.php' style='display:inline-block; padding:10px 20px; background:#d8000c; color:white; text-decoration:none; border-radius:5px;'>Quay lại trang chủ</a>
                 </div>");
        }

        // Nếu thành công thì update vào CSDL
        if ($result['status'] == 'success') {
            $topic = $result['topic'];
            $image_url = $result['image_url'];
            
            $update_sql = "UPDATE posts SET ai_topic='$topic', generated_image_url='$image_url' WHERE id=$post_id";
            $conn->query($update_sql);
        }
    }

    header("Location: index.php");
    exit();
}
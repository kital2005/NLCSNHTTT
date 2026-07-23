<?php
require_once 'db.php';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];

    // Kiểm tra xem username đã tồn tại chưa trong bảng NGUOI_DUNG
    $check_query = "SELECT ND_Ma FROM NGUOI_DUNG WHERE ND_TaiKhoan = '$username'";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        $error_message = "Tên đăng nhập này đã có người sử dụng!";
    } else {
        // Mã hóa mật khẩu trước khi lưu để bảo mật hệ thống
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Chèn người dùng mới vào database Tiếng Việt
        $insert_query = "INSERT INTO NGUOI_DUNG (ND_TaiKhoan, ND_HoTen, ND_MatKhau) 
                         VALUES ('$username', '$full_name', '$hashed_password')";
        
        if ($conn->query($insert_query) === TRUE) {
            $success_message = "Đăng ký thành công! Đang chuyển hướng sang trang đăng nhập...";
            header("refresh:2;url=login.php");
        } else {
            $error_message = "Lỗi hệ thống: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="card login-card">
            <div class="row g-0">
                
                <div class="col-md-6 p-5 bg-white d-flex flex-column justify-content-center">
                    <div class="mb-4 text-center">
                        <h3 class="fw-bold text-dark"><i class="fa-solid fa-earth-asia text-primary me-2"></i>SocialAI</h3>
                        <p class="text-muted">Tạo tài khoản cộng đồng mới</p>
                    </div>

                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger rounded-3 py-2 small"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success rounded-3 py-2 small"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Tên hiển thị (Họ và tên)</label>
                            <input type="text" class="form-control" name="full_name" placeholder="Ví dụ: Thái Tuấn" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Tên đăng nhập / Email</label>
                            <input type="text" class="form-control" name="username" placeholder="Ví dụ: tuan.thai hoặc email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" placeholder="Tối thiểu 6 ký tự" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Đăng ký tài khoản</button>
                        
                        <div class="text-center">
                            <small class="text-muted">Đã có tài khoản? <a href="login.php" class="text-primary fw-bold text-decoration-none">Đăng nhập ngay</a></small>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-6 bg-gradient-primary text-white p-5 d-flex flex-column justify-content-center align-items-center text-center d-none d-md-flex">
                    <i class="fa-solid fa-user-plus fa-4x mb-4 opacity-75"></i>
                    <h3 class="fw-bold mb-3">Gia nhập không gian</h3>
                    <h3 class="fw-bold mb-4">Sáng tạo thông minh</h3>
                    <p class="opacity-75 mb-0">Hệ thống phân tích ngữ cảnh bài viết thông minh bằng Machine Learning và tự động minh họa ý tưởng của bạn.</p>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
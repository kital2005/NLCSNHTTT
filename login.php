<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            /* Căn giữa tuyệt đối theo chiều dọc */
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
        .btn-primary:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        
        <div class="card login-card">
            <div class="row g-0">
                
                <div class="col-md-6 p-5 bg-white d-flex flex-column justify-content-center">
                    <div class="mb-4 text-center">
                        <h3 class="fw-bold text-dark"><i class="fa-solid fa-earth-asia text-primary me-2"></i>SocialAI</h3>
                        <p class="text-muted">Chào mừng trở lại cộng đồng!</p>
                    </div>
                    
                    <form action="process_login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Tên đăng nhập / Email</label>
                            <input type="text" class="form-control" name="username" placeholder="Nhập email hoặc username" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Đăng nhập</button>
                        
                        <div class="text-center">
                            <small class="text-muted">Chưa có tài khoản? <a href="register.php" class="text-primary fw-bold text-decoration-none">Đăng ký ngay</a></small>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-6 bg-gradient-primary text-white p-5 d-flex flex-column justify-content-center align-items-center text-center d-none d-md-flex">
                    <i class="fa-solid fa-wand-magic-sparkles fa-4x mb-4 opacity-75"></i>
                    <h3 class="fw-bold mb-3">Chia sẻ nội dung,</h3>
                    <h3 class="fw-bold mb-4">Sinh ảnh tự động</h3>
                    <p class="opacity-75 mb-0">Hệ thống phân tích ngữ cảnh bài viết thông minh bằng Machine Learning và tự động minh họa ý tưởng của bạn.</p>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
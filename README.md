# SocialAI — Nền tảng chia sẻ nội dung và cộng đồng trực tuyến thông minh

Niên luận hệ thống thông tin — PHP/Bootstrap + Python ML trên XAMPP.

## Yêu cầu

- XAMPP (Apache + MySQL/MariaDB + PHP 8+)
- Python 3.10+
- pip install -r requirements.txt

## Cài đặt

1. Copy project vào `htdocs/NLCSNHTTT`
2. Tạo database `social_ai_db` trong phpMyAdmin
3. **Nếu DB mới:** import `schema.sql`
4. **Nếu DB đã có dữ liệu:** chạy `migration_v2.sql` trong phpMyAdmin
5. Cài Python dependencies:
   ```bash
   pip install -r requirements.txt
   ```
6. Huấn luyện model AI:
   ```bash
   python generate_data.py
   python train_model.py
   ```
7. Truy cập: `http://localhost/NLCSNHTTT/login.php`

## Tài khoản admin

Sau khi chạy `migration_v2.sql`, user `id=1` được gán role `admin` — có quyền vào **Quản trị** và **Huấn luyện lại AI**.

## Cấu trúc chính

| File | Mô tả |
|------|-------|
| `index.php` | Bảng tin, đăng bài, AI vẽ ảnh |
| `ai_dashboard.php` | Quản lý AI Model (Dashboard) |
| `notifications.php` | Trang thông báo đầy đủ |
| `admin.php` | Quản trị user/bài viết |
| `train_model.py` | TF-IDF + SVM vs Random Forest |
| `ai_engine.py` | Inference + sinh ảnh (Pollinations / HF SD) |

## Pipeline AI

```
Bài viết → TF-IDF → SVM/RF (chủ đề + cảm xúc) → Prompt EN → Sinh ảnh minh họa
```

## Hugging Face Stable Diffusion (tùy chọn)

Tạo file `hf_token.txt` chứa token Hugging Face, chọn provider **Hugging Face SD v1.5** trong AI Dashboard.

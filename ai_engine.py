import sys
import json
import requests
import time
import os

# Ép hệ thống dùng chuẩn UTF-8 để tiếng Việt không bị lỗi khi truyền qua PHP
sys.stdout.reconfigure(encoding='utf-8')

# 1. Nhận dữ liệu trạng thái từ PHP truyền sang
if len(sys.argv) < 2:
    print(json.dumps({"error": "Chưa có nội dung đầu vào!"}))
    sys.exit()

post_content = sys.argv[1]

# 2. GIAI ĐOẠN 1: Phân loại ngữ cảnh (Text Classification)
# Lưu ý: Đây là logic giả lập (Mock) để test luồng. 
# Khi ráp mô hình Machine Learning, ta sẽ dùng: model = joblib.load('svm_model.pkl')
def classify_text(text):
    text_lower = text.lower()
    if any(word in text_lower for word in ["code", "server", "máy tính", "linux", "công nghệ"]):
        return "Khoa học & Công nghệ"
    elif any(word in text_lower for word in ["game", "chơi", "giải trí"]):
        return "Giải trí & Gaming"
    elif any(word in text_lower for word in ["học", "trường", "thi", "luận văn"]):
        return "Học tập"
    else:
        return "Đời sống thường ngày"

topic = classify_text(post_content)

# 3. GIAI ĐOẠN 2: Gọi API Sinh ảnh (Stable Diffusion)
# API của Hugging Face
API_URL = "https://api-inference.huggingface.co/models/runwayml/stable-diffusion-v1-5"
# THAY TOKEN CỦA BẠN VÀO BÊN DƯỚI (Giữ nguyên chữ Bearer và dấu cách)
headers = {"Authorization": "Bearer YOUR_TOKEN_HERE"}

# Tối ưu hóa câu lệnh (Prompt Engineering) để ảnh sát ngữ cảnh hơn
prompt = f"A beautiful, high quality masterpiece illustration about {topic}. Context: {post_content}. Highly detailed, 8k resolution, trending on artstation."

def query_api(payload):
    response = requests.post(API_URL, headers=headers, json=payload)
    return response.content

try:
    image_bytes = query_api({"inputs": prompt})
    
    # 4. Lưu ảnh vừa sinh ra vào thư mục uploads của dự án
    if not os.path.exists("uploads"):
        os.makedirs("uploads")
        
    image_filename = f"ai_generated_{int(time.time())}.jpg"
    image_path = f"uploads/{image_filename}"
    
    with open(image_path, "wb") as f:
        f.write(image_bytes)
        
    # 5. Đóng gói kết quả (Chủ đề + Đường dẫn ảnh) thành file JSON trả về cho PHP
    result = {
        "status": "success",
        "topic": topic,
        "image_url": image_path
    }
    print(json.dumps(result))

except Exception as e:
    # Báo lỗi về PHP nếu gọi API thất bại
    error_result = {
        "status": "error",
        "message": str(e)
    }
    print(json.dumps(error_result))
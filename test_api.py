import requests

print("1. Đang đọc Token từ hf_token.txt...")
try:
    with open("hf_token.txt", "r") as f:
        # strip() giúp xóa sạch mọi dấu cách/xuống dòng thừa mứa
        token = f.read().strip() 
        print(f" -> Đã lấy Token: {token[:5]}... (ẩn phần sau để bảo mật)")
except FileNotFoundError:
    print(" -> LỖI: Không tìm thấy file hf_token.txt!")
    exit()

API_URL = "https://api-inference.huggingface.co/models/runwayml/stable-diffusion-v1-5"
headers = {"Authorization": f"Bearer {token}"}

print("\n2. Đang gửi yêu cầu vẽ ảnh lên máy chủ Hugging Face (Vui lòng đợi 10-30s)...")
try:
    # Gửi yêu cầu với timeout 60 giây
    response = requests.post(API_URL, headers=headers, json={"inputs": "A cup of hot coffee next to a laptop"}, timeout=60)
    
    if response.status_code == 200:
        print(" -> ✅ THÀNH CÔNG! Kết nối hoàn hảo, API đã chịu nhả ảnh ra rồi!")
    else:
        print(f" -> ❌ LỖI SERVER: Mã lỗi {response.status_code}")
        print(f" -> CHI TIẾT LỖI TỪ HUGGING FACE: {response.text}")
        
except Exception as e:
    print(f" -> ❌ LỖI ĐƯỜNG TRUYỀN (DNS/Network/Timeout): {e}")
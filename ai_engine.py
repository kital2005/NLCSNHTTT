import sys
import json
import requests
import time
import os
import joblib
import urllib.parse
from deep_translator import GoogleTranslator

# Ép hệ thống dùng chuẩn UTF-8
sys.stdout.reconfigure(encoding='utf-8')

if len(sys.argv) < 2:
    print(json.dumps({"error": "Chưa có nội dung đầu vào!"}))
    sys.exit()

post_content = sys.argv[1]

# =====================================================================
# GIAI ĐOẠN 1: PHÂN LOẠI BẰNG MACHINE LEARNING (SVM)
# =====================================================================
try:
    vectorizer = joblib.load('tfidf_vectorizer.pkl')
    model = joblib.load('social_ai_model.pkl')
    X_new = vectorizer.transform([post_content])
    prediction = model.predict(X_new)[0] 
    
    if " | " in prediction:
        topic, emotion = prediction.split(" | ")
    else:
        topic = prediction
        emotion = "Bình thường"
except Exception as e:
    topic = "Đời sống thường ngày"
    emotion = "Bình thường"

# =====================================================================
# GIAI ĐOẠN 2: DỊCH SANG TIẾNG ANH & GỌI API VẼ ẢNH
# =====================================================================
try:
    # Dịch nội dung và cảm xúc sang tiếng Anh để AI vẽ hiểu chính xác 100%
    translator = GoogleTranslator(source='vi', target='en')
    eng_content = translator.translate(post_content)
    eng_emotion = translator.translate(emotion)
    
    # Prompt tiếng Anh cực chuẩn
    prompt = f"A high quality digital illustration showing exactly this scene: {eng_content}. The mood is {eng_emotion}. Highly detailed, masterpiece, realistic."
except:
    # Nếu lỗi dịch thuật thì xài lại tiếng Việt (đề phòng rớt mạng)
    prompt = f"A high quality digital illustration about: {post_content}. The mood is {emotion}, {topic} theme, trending on artstation, masterpiece."

encoded_prompt = urllib.parse.quote(prompt)
API_URL = f"https://image.pollinations.ai/prompt/{encoded_prompt}?width=800&height=500&nologo=true"

if not os.path.exists("uploads"):
    os.makedirs("uploads")

image_filename = f"ai_generated_{int(time.time())}.jpg"
image_path = f"uploads/{image_filename}"

try:
    response = requests.get(API_URL, timeout=60)
    
    if response.status_code == 200:
        with open(image_path, "wb") as f:
            f.write(response.content)
            
        result = {
            "status": "success",
            "topic": f"{topic} - Cảm xúc: {emotion} (Ảnh AI Thật)",
            "image_url": image_path
        }
        print(json.dumps(result))
    else:
        raise Exception("Lỗi API tải ảnh")

except Exception as e:
    result = {
        "status": "success",
        "topic": f"{topic} - Cảm xúc: {emotion} (Mạng dự phòng)",
        "image_url": "https://images.unsplash.com/photo-1518770660439-4636190af475?w=800"
    }
    print(json.dumps(result))
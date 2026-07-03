import sys
import json
import requests
import time
import os
import joblib
import urllib.parse
from deep_translator import GoogleTranslator

sys.stdout.reconfigure(encoding='utf-8')
start_ms = int(time.time() * 1000)

if len(sys.argv) < 2:
    print(json.dumps({"error": "Chưa có nội dung đầu vào!"}))
    sys.exit(1)

post_content = sys.argv[1]
image_provider = sys.argv[2] if len(sys.argv) > 2 else 'pollinations'

topic = "Đời sống thường ngày"
emotion = "Bình thường"
ml_status = "fallback"

try:
    vectorizer = joblib.load('tfidf_vectorizer.pkl')
    model = joblib.load('social_ai_model.pkl')
    X_new = vectorizer.transform([post_content])
    prediction = model.predict(X_new)[0]

    if " | " in prediction:
        topic, emotion = prediction.split(" | ", 1)
    else:
        topic = prediction
        emotion = "Bình thường"
    ml_status = "success"
except Exception:
    pass

topic_display = f"{topic} - Cảm xúc: {emotion}"
image_status = "fallback"
image_path = ""

try:
    translator = GoogleTranslator(source='vi', target='en')
    eng_content = translator.translate(post_content[:500])
    eng_emotion = translator.translate(emotion)
    prompt = f"A high quality digital illustration showing exactly this scene: {eng_content}. The mood is {eng_emotion}. Topic: {topic}. Highly detailed, masterpiece."
except Exception:
    prompt = f"A high quality digital illustration about: {post_content[:300]}. Mood: {emotion}, theme: {topic}, masterpiece."

if not os.path.exists("uploads"):
    os.makedirs("uploads")

image_filename = f"ai_generated_{int(time.time())}.jpg"
image_path = f"uploads/{image_filename}"

try:
    if image_provider == 'huggingface' and os.path.exists('hf_token.txt'):
        with open('hf_token.txt', 'r') as f:
            hf_token = f.read().strip()
        api_url = "https://api-inference.huggingface.co/models/runwayml/stable-diffusion-v1-5"
        headers = {"Authorization": f"Bearer {hf_token}"}
        payload = {"inputs": prompt[:500]}
        response = requests.post(api_url, headers=headers, json=payload, timeout=90)
        if response.status_code == 200 and response.content[:4] != b'{"er':
            with open(image_path, "wb") as f:
                f.write(response.content)
            image_status = "success"
        else:
            raise Exception("HF API error")
    else:
        encoded_prompt = urllib.parse.quote(prompt)
        API_URL = f"https://image.pollinations.ai/prompt/{encoded_prompt}?width=800&height=500&nologo=true"
        response = requests.get(API_URL, timeout=60)
        if response.status_code == 200:
            with open(image_path, "wb") as f:
                f.write(response.content)
            image_status = "success"
        else:
            raise Exception("Pollinations error")
except Exception:
    image_path = "https://images.unsplash.com/photo-1518770660439-4636190af475?w=800"
    image_status = "fallback"

processing_time = int(time.time() * 1000) - start_ms

suffix = "(Ảnh AI Thật)" if image_status == "success" else "(Mạng dự phòng)"
result = {
    "status": "success",
    "topic": f"{topic_display} {suffix}",
    "predicted_topic": topic,
    "predicted_emotion": emotion,
    "image_url": image_path,
    "processing_time_ms": processing_time,
    "ml_status": ml_status,
    "image_status": image_status,
    "provider": image_provider
}
print(json.dumps(result, ensure_ascii=False))

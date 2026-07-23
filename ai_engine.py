import sys, json, time, os, random, urllib.parse, requests, urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
sys.stdout.reconfigure(encoding='utf-8')

start_ms = int(time.time() * 1000)

result = {
    "status": "success",
    "topic": "Đời sống thường ngày (Mạng dự phòng)",
    "predicted_topic": "Đời sống",
    "predicted_emotion": "Bình thường",
    "image_url": "https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=800",
    "processing_time_ms": 0,
    "image_status": "fallback",
    "error_debug": "OK"
}

# =========================================================
# DÁN API KEY "AQ..." CỦA BẠN VÀO ĐÂY NHÉ
GEMINI_API_KEY = "" 
# =========================================================

content = sys.argv[1] if len(sys.argv) > 1 else "beautiful landscape"
gemini_error = ""

def enhance_prompt_with_gemini(text):
    global gemini_error
    if not GEMINI_API_KEY or GEMINI_API_KEY.strip() == "":
        gemini_error = "Chưa nhập Key Gemini"
        return None
    try:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={GEMINI_API_KEY.strip()}"
        
        ai_instruction = f"""You are a professional prompt engineer for Stable Diffusion.
        Translate the following Vietnamese text to English and expand it into a highly detailed visual prompt.
        CRITICAL RULES:
        1. If the text describes a scenery (like raining, dark sky, empty street), DO NOT add humans, girls, or portraits. Focus strictly on the atmosphere, landscape, and lighting.
        2. Output ONLY the English prompt. No explanations.
        Text to translate: '{text}'"""
        
        payload = {"contents": [{"parts": [{"text": ai_instruction}]}]}
        
        # ĐÃ THÊM verify=False ĐỂ XAMPP KHÔNG CHẶN GOOGLE NỮA
        res = requests.post(url, json=payload, headers={'Content-Type': 'application/json'}, timeout=15, verify=False)
        
        if res.status_code == 200:
            return res.json()['candidates'][0]['content']['parts'][0]['text'].strip()
        else:
            gemini_error = f"Lỗi HTTP {res.status_code}: {res.text}"
            return None
    except Exception as e:
        gemini_error = str(e)
        return None

def translate_vi_to_en_fallback(text):
    try:
        url = f"https://translate.googleapis.com/translate_a/single?client=gtx&sl=vi&tl=en&dt=t&q={urllib.parse.quote(text)}"
        res = requests.get(url, timeout=3, verify=False).json()
        return f"{res[0][0][0]}, high quality, highly detailed, photorealistic, scenery"
    except Exception:
        return "beautiful scenery, high quality"

try:
    topic = "Đời sống thường ngày"
    emotion = "Bình thường"
    ml_status = "fallback"

    try:
        import joblib
        vectorizer = joblib.load('tfidf_vectorizer.pkl')
        model = joblib.load('social_ai_model.pkl')
        X_new = vectorizer.transform([content])
        prediction = model.predict(X_new)[0]
        if " | " in prediction:
            topic, emotion = prediction.split(" | ", 1)
        else:
            topic = prediction
        ml_status = "success"
    except Exception:
        pass  

    final_prompt = enhance_prompt_with_gemini(content)
    used_gemini = True
    
    if not final_prompt:
        final_prompt = translate_vi_to_en_fallback(content)
        used_gemini = False

    encoded_prompt = urllib.parse.quote(final_prompt)
    seed = random.randint(1, 999999)

    api_url = f"https://image.pollinations.ai/prompt/{encoded_prompt}?width=800&height=500&seed={seed}&nologo=true"
    headers = {'User-Agent': 'Mozilla/5.0'}

    res = requests.get(api_url, headers=headers, timeout=45, verify=False)

    if res.status_code == 200 and len(res.content) > 5000:
        if not os.path.exists("uploads"):
            os.makedirs("uploads")
        
        filename = f"uploads/ai_{int(time.time())}_{random.randint(100,999)}.jpg"
        with open(filename, "wb") as f:
            f.write(res.content)
            
        result["image_url"] = filename
        result["image_status"] = "success"
        
        tag = "(Ảnh AI Thật - Có Gemini)" if used_gemini else "(Ảnh AI Thật - MẤT GEMINI)"
        result["topic"] = f"{topic} - Cảm xúc: {emotion} {tag}"
        
        if not used_gemini:
            result["error_debug"] = f"Lỗi Gemini: {gemini_error}"
        else:
            result["error_debug"] = f"Prompt: {final_prompt[:100]}..."
    else:
        result["error_debug"] = f"API Pollinations lỗi {res.status_code}"
        result["topic"] = f"{topic} - Cảm xúc: {emotion} (Mạng dự phòng)"

    result["predicted_topic"] = topic
    result["predicted_emotion"] = emotion
    result["ml_status"] = ml_status

except Exception as e:
    result["error_debug"] = f"Exception: {str(e)}"
    result["topic"] = f"Lỗi xử lý (Mạng dự phòng)"

result["processing_time_ms"] = int(time.time() * 1000) - start_ms
print(json.dumps(result, ensure_ascii=False)
)
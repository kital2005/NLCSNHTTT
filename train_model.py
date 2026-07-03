import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split
from sklearn.svm import SVC
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
import joblib
import json
import time
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

print("=== HỆ THỐNG HUẤN LUYỆN AI BẢN 20K DÒNG ===")
start_time = time.time()
print("[1] Đang tải bộ dữ liệu dataset_20k.csv...")

try:
    df = pd.read_csv('dataset_20k.csv')
except FileNotFoundError:
    print(json.dumps({"error": "Không tìm thấy dataset_20k.csv"}))
    sys.exit(1)

df['Label'] = df['Topic'] + " | " + df['Emotion']
X = df['Text']
y = df['Label']

print("[2] Đang trích xuất đặc trưng TF-IDF (3000 từ vựng)...")
vectorizer = TfidfVectorizer(max_features=3000)
X_vectors = vectorizer.fit_transform(X)

X_train, X_test, y_train, y_test = train_test_split(X_vectors, y, test_size=0.2, random_state=42)

print("[3] Huấn luyện và so sánh SVM vs Random Forest...")

svm_model = SVC(kernel='linear')
svm_model.fit(X_train, y_train)
svm_preds = svm_model.predict(X_test)
svm_accuracy = accuracy_score(y_test, svm_preds)
print(f" -> Độ chính xác SVM: {svm_accuracy * 100:.2f}%")

rf_model = RandomForestClassifier(n_estimators=100, random_state=42)
rf_model.fit(X_train, y_train)
rf_preds = rf_model.predict(X_test)
rf_accuracy = accuracy_score(y_test, rf_preds)
print(f" -> Độ chính xác Random Forest: {rf_accuracy * 100:.2f}%")

best_model = svm_model if svm_accuracy >= rf_accuracy else rf_model
best_name = "SVM" if svm_accuracy >= rf_accuracy else "Random Forest"
best_preds = svm_preds if svm_accuracy >= rf_accuracy else rf_preds

print(f"\n[4] Thuật toán chiến thắng: {best_name}")

joblib.dump(best_model, 'social_ai_model.pkl')
joblib.dump(vectorizer, 'tfidf_vectorizer.pkl')

duration = int(time.time() - start_time)

topic_counts = df['Topic'].value_counts().to_dict()
emotion_counts = df['Emotion'].value_counts().to_dict()

labels = sorted(y.unique())
cm = confusion_matrix(y_test, best_preds, labels=labels)

try:
    import matplotlib
    matplotlib.use('Agg')
    import matplotlib.pyplot as plt
    import numpy as np

    fig, ax = plt.subplots(figsize=(12, 10))
    im = ax.imshow(cm, interpolation='nearest', cmap=plt.cm.Blues)
    ax.set_title(f'Confusion Matrix - {best_name}')
    tick_labels = [l[:20] + '...' if len(l) > 20 else l for l in labels]
    ax.set_xticks(np.arange(len(labels)))
    ax.set_yticks(np.arange(len(labels)))
    ax.set_xticklabels(tick_labels, rotation=90, fontsize=6)
    ax.set_yticklabels(tick_labels, fontsize=6)
    plt.colorbar(im, ax=ax)
    plt.tight_layout()
    plt.savefig('confusion_matrix.png', dpi=120, bbox_inches='tight')
    plt.close()
    print("[5] Đã lưu confusion_matrix.png")
except Exception as e:
    print(f"[5] Không tạo được biểu đồ: {e}")

model_info = {
    "best_model": best_name,
    "svm_accuracy": round(svm_accuracy * 100, 2),
    "rf_accuracy": round(rf_accuracy * 100, 2),
    "dataset_size": len(df),
    "training_duration_seconds": duration,
    "trained_at": time.strftime("%Y-%m-%d %H:%M:%S"),
    "topic_distribution": topic_counts,
    "emotion_distribution": emotion_counts,
    "num_labels": len(labels)
}

with open('model_info.json', 'w', encoding='utf-8') as f:
    json.dump(model_info, f, ensure_ascii=False, indent=2)

result = {
    "status": "success",
    **model_info
}
print(json.dumps(result, ensure_ascii=False))
print("HOÀN TẤT!")

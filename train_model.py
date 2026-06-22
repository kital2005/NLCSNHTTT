import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split
from sklearn.svm import SVC
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score
import joblib
import os

print("=== HỆ THỐNG HUẤN LUYỆN AI (SOCIAL NETWORK) ===")
print("[1] Đang tải bộ dữ liệu dataset.csv...")

# 1. Đọc dữ liệu
try:
    df = pd.read_csv('dataset_auto.csv')
except FileNotFoundError:
    print("Lỗi: Không tìm thấy file dataset.csv!")
    exit()

# Gộp Chủ đề và Cảm xúc lại thành một Nhãn kép để ảnh sinh ra sát ngữ cảnh nhất
# Ví dụ: "Học tập - Buồn rầu"
df['Label'] = df['Topic'] + " | " + df['Emotion']

X = df['Text']
y = df['Label']

# 2. Vector hóa văn bản bằng TF-IDF
print("[2] Đang trích xuất đặc trưng ngôn ngữ bằng TF-IDF...")
vectorizer = TfidfVectorizer(max_features=1000)
X_vectors = vectorizer.fit_transform(X)

# Chia tập dữ liệu: 80% để học, 20% để thi thử
X_train, X_test, y_train, y_test = train_test_split(X_vectors, y, test_size=0.2, random_state=42)

# 3. Huấn luyện và Đánh giá Mô hình
print("[3] Bắt đầu huấn luyện và so sánh thuật toán...")

# Thuật toán SVM (Support Vector Machine)
svm_model = SVC(kernel='linear')
svm_model.fit(X_train, y_train)
svm_preds = svm_model.predict(X_test)
svm_accuracy = accuracy_score(y_test, svm_preds)
print(f" -> Độ chính xác của SVM: {svm_accuracy * 100:.2f}%")

# Thuật toán Random Forest
rf_model = RandomForestClassifier(n_estimators=100, random_state=42)
rf_model.fit(X_train, y_train)
rf_preds = rf_model.predict(X_test)
rf_accuracy = accuracy_score(y_test, rf_preds)
print(f" -> Độ chính xác của Random Forest: {rf_accuracy * 100:.2f}%")

# 4. Chọn mô hình tốt nhất và đóng gói
best_model = svm_model if svm_accuracy >= rf_accuracy else rf_model
best_name = "SVM" if svm_accuracy >= rf_accuracy else "Random Forest"

print(f"\n[4] Thuật toán chiến thắng: {best_name}")
print("Đang đóng gói và lưu trữ mô hình...")

# Lưu mô hình và công cụ chuyển hóa ngôn ngữ lại thành file
joblib.dump(best_model, 'social_ai_model.pkl')
joblib.dump(vectorizer, 'tfidf_vectorizer.pkl')

print("HOÀN TẤT! Đã xuất file 'social_ai_model.pkl' và 'tfidf_vectorizer.pkl' thành công.")
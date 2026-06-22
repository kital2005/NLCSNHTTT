import pandas as pd
import random
import os

print("Đang khởi động máy tạo dữ liệu AI...")

data = []

# ==========================================
# 1. BỘ TỪ ĐIỂN CỐT LÕI (Từ khóa & Ngữ cảnh)
# ==========================================
templates = {
    "Khoa học & Công nghệ": {
        "Hào hứng": ["Mới cài xong {} chạy mượt quá", "Code xong chức năng {} thấy mình bá thật", "Nay mới tậu {} dùng sướng vãi", "Vừa fix xong bug {} mừng rớt nước mắt", "Test thành công hệ thống {}"],
        "Căng thẳng": ["Bug {} fix cả đêm chưa xong trầm cảm", "Hệ thống {} đang báo lỗi đỏ lòm", "Cấu hình {} khó quá nhức hết cả đầu", "Làm đồ án với {} mà code không chạy"],
        "Buồn rầu": ["Mới làm hỏng {} tốn mớ tiền", "Lỡ tay xóa mất {} không cứu được", "Máy chủ {} bị sập chán quá"]
    },
    "Giải trí & Gaming": {
        "Hào hứng": ["Vừa thắng chuỗi trận {} cảm giác yomost", "Mở được nhân vật xịn trong {} bá cháy", "Đi chơi {} với anh em cười đau bụng", "Gánh team còng lưng trong {}"],
        "Căng thẳng": ["Chuỗi thua {} tụt rank thảm hại", "Gặp team tạ trong {} ức chế thật", "Chơi {} bị giật lag ping cao bực mình", "Ping {} nhảy lên 999ms nản luôn"],
        "Thư giãn": ["Cuối tuần ngồi chơi {} chill chill", "Cày {} giải trí sau giờ học", "Làm ván {} cho nhẹ đầu", "Đọc liền tù tì bộ {} cuốn quá"]
    },
    "Học tập": {
        "Căng thẳng": ["Mai thi môn {} mà chưa chữ nào vào đầu", "Deadline môn {} dí sấp mặt", "Làm bài tập {} với team mệt mỏi quá", "Cài đặt lab cho {} phức tạp ghê"],
        "Buồn rầu": ["Rớt môn {} rồi, buồn quá", "Bị điểm thấp bài kiểm tra {} chán chả buồn nói", "Thầy chấm {} gắt quá", "Học {} khó hiểu quá chắc tạch"],
        "Hào hứng": ["Đạt điểm A môn {} sung sướng quá", "Thuyết trình {} thành công rực rỡ", "Giải được bài {} khó nhất lớp", "Cuối cùng cũng qua môn {}"]
    },
    "Đời sống thường ngày": {
        "Vui vẻ": ["Hôm nay trời đẹp đi {} quá xá đã", "Mới được nhận lương đi {} thôi", "Được tặng món quà {} vui quá", "Gặp lại bạn cũ đi {} cười thả ga"],
        "Buồn rầu": ["Cuối tháng hết tiền, phải {} cho qua ngày", "Trời mưa ngập đường không đi {} được chán quá", "Hôm nay xui xẻo, lỡ làm {} hỏng", "Xe hư giữa đường lúc đang {}"],
        "Thư giãn": ["Trời mưa nằm nhà {} là sướng nhất", "Cuối tuần đi {} hít thở không khí trong lành", "Sáng sớm dậy {} thấy lòng bình yên", "Pha ly cà phê ngồi {} ngắm phố phường"]
    }
}

keywords = {
    "Khoa học & Công nghệ": ["server Linux", "Ubuntu dual boot", "database Firebase", "code React", "mạng ảo Kathara", "PSU 650W", "code PHP", "CentOS", "máy ảo VMware"],
    "Giải trí & Gaming": ["Valorant", "Wuthering Waves", "Minecraft", "Return of the Disaster-Class Hero", "truyện Manhwa", "chuột FPS", "OBS stream", "rank Kim Cương"],
    "Học tập": ["Quản trị hệ thống", "CT179", "đồ án niên luận", "thực tập mạng", "cấu hình Router", "bài tập nhóm", "bảo vệ đề tài", "viết báo cáo"],
    "Đời sống thường ngày": ["chạy xe quanh Cần Thơ", "uống cà phê", "xem phim Donghua", "ngủ nướng", "dạo phố", "ăn mì tôm", "nghe nhạc", "ăn quán vỉa hè"]
}

# ==========================================
# 2. VÒNG LẶP SINH RA 2.500 DÒNG DỮ LIỆU
# ==========================================
print("Đang trộn dữ liệu...")
for i in range(2500):
    topic = random.choice(list(templates.keys()))
    emotion = random.choice(list(templates[topic].keys()))
    template = random.choice(templates[topic][emotion])
    keyword = random.choice(keywords[topic])
    
    # Thêm tiền tố và hậu tố ngẫu nhiên để các câu tự nhiên như người dùng gõ
    prefix = random.choice(["", "Hôm nay ", "Nay ", "Tự nhiên ", "Đang ", "Thề luôn "])
    suffix = random.choice(["", " hihi", " :((", " haha", " chán", " quá", " thật sự", " ae ạ", " mn ơi"])
    
    sentence = prefix + template.format(keyword) + suffix
    data.append([sentence.strip(), topic, emotion])

# ==========================================
# 3. LƯU THÀNH FILE CSV SIÊU SẠCH
# ==========================================
df = pd.DataFrame(data, columns=['Text', 'Topic', 'Emotion'])
# Dùng utf-8-sig để ép chuẩn tiếng Việt, không bị lỗi font
df.to_csv('dataset_auto.csv', index=False, encoding='utf-8-sig')

print(f"✅ ĐÃ XONG! Đã sinh thành công {len(df)} dòng dữ liệu siêu chuẩn vào file 'dataset_auto.csv'.")
import pandas as pd
import random

print("Đang khởi động siêu máy tạo dữ liệu 20.000 dòng...")

data = []

# ==========================================
# 1. BỘ TỪ ĐIỂN MỞ RỘNG SIÊU ĐA DẠNG
# ==========================================
templates = {
    "Khoa học & Công nghệ": {
        "Hào hứng": [
            "Mới cài xong {} chạy bao mượt", "Code xong chức năng {} thấy mình bá thật", 
            "Nay mới tậu {} dùng sướng vãi", "Vừa fix xong bug {} mừng rớt nước mắt", 
            "Test thành công hệ thống {}", "Dựng xong {} nhìn ngầu bá cháy", 
            "Cấu hình thành công {}, tối nay ngủ ngon rồi"
        ],
        "Căng thẳng": [
            "Bug {} fix cả đêm chưa xong trầm cảm", "Hệ thống {} đang báo lỗi đỏ lòm", 
            "Cấu hình {} khó quá nhức hết cả đầu", "Làm đồ án với {} mà code không chạy",
            "Màn hình xanh vì {}, hoang mang tột độ", "Xung đột driver {} sửa mãi không được"
        ],
        "Buồn rầu": [
            "Mới làm hỏng {} tốn mớ tiền", "Lỡ tay xóa mất {} không cứu được", 
            "Máy chủ {} bị sập chán quá", "Đang code ngon thì {} dở chứng",
            "Tài khoản {} bị khóa, khóc ròng"
        ]
    },
    "Giải trí & Gaming": {
        "Hào hứng": [
            "Vừa thắng chuỗi trận {} cảm giác yomost", "Mở được nhân vật xịn trong {} bá cháy", 
            "Đi chơi {} với anh em cười đau bụng", "Gánh team còng lưng trong {}",
            "Vừa lập kỷ lục mới trong {}", "Phá đảo {} trong một đêm"
        ],
        "Căng thẳng": [
            "Chuỗi thua {} tụt rank thảm hại", "Gặp team tạ trong {} ức chế thật", 
            "Chơi {} bị giật lag ping cao bực mình", "Ping {} nhảy lên 999ms nản luôn",
            "Bị đồng đội toxic trong {} bực cả mình", "Đang combat {} thì rớt mạng"
        ],
        "Buồn rầu": [
            "Roll mãi không ra waifu trong {}, trầm cảm", "Trượt mất phần thưởng {} cay quá",
            "Game {} dạo này chán quá, hết hứng chơi", "Bị khóa acc {} oan ức quá"
        ],
        "Thư giãn": [
            "Cuối tuần ngồi chơi {} chill chill", "Cày {} giải trí sau giờ học", 
            "Làm ván {} cho nhẹ đầu", "Đọc liền tù tì bộ {} cuốn quá",
            "Nghe nhạc lofi và chơi {} thật bình yên"
        ]
    },
    "Học tập": {
        "Căng thẳng": [
            "Mai thi môn {} mà chưa chữ nào vào đầu", "Deadline môn {} dí sấp mặt", 
            "Làm bài tập {} với team mệt mỏi quá", "Cài đặt lab cho {} phức tạp ghê",
            "Chưa làm xong báo cáo {}, toang rồi", "Thức trắng đêm ôn {}"
        ],
        "Buồn rầu": [
            "Rớt môn {} rồi, buồn quá", "Bị điểm thấp bài kiểm tra {} chán chả buồn nói", 
            "Thầy chấm {} gắt quá", "Học {} khó hiểu quá chắc tạch",
            "Làm sai bét câu cuối môn {}, tiếc đứt ruột"
        ],
        "Hào hứng": [
            "Đạt điểm A môn {} sung sướng quá", "Thuyết trình {} thành công rực rỡ", 
            "Giải được bài {} khó nhất lớp", "Cuối cùng cũng qua môn {}",
            "Thầy khen bài tập {} làm xuất sắc"
        ]
    },
    "Đời sống thường ngày": {
        "Vui vẻ": [
            "Hôm nay trời đẹp đi {} quá xá đã", "Mới được nhận lương đi {} thôi", 
            "Được tặng món quà {} vui quá", "Gặp lại bạn cũ đi {} cười thả ga",
            "Ăn trúng quán {} ngon bá cháy"
        ],
        "Buồn rầu": [
            "Cuối tháng hết tiền, phải {} cho qua ngày", "Trời mưa ngập đường không đi {} được chán quá", 
            "Hôm nay xui xẻo, lỡ làm {} hỏng", "Xe hư giữa đường lúc đang {}",
            "Nhà cúp điện đen thui, không thể {} được, chán thật"
        ],
        "Căng thẳng": [
            "Kẹt xe cứng ngắc lúc đang đi {}", "Đợi {} mỏi hết cả chân",
            "Gặp chuyện bực mình lúc {}", "Đi làm về mệt mà còn phải {}"
        ],
        "Thư giãn": [
            "Trời mưa nằm nhà {} là sướng nhất", "Cuối tuần đi {} hít thở không khí trong lành", 
            "Sáng sớm dậy {} thấy lòng bình yên", "Pha ly cà phê ngồi {} ngắm phố phường",
            "Trốn việc đi {} xả stress"
        ]
    }
}

keywords = {
    "Khoa học & Công nghệ": [
        "server Linux", "Ubuntu dual boot", "database Firebase", "code React", 
        "mạng ảo Kathara", "PSU 650W", "code PHP", "CentOS", "máy ảo VMware",
        "card màn hình", "chip Ryzen", "mã nguồn Python", "API", "router mạng"
    ],
    "Giải trí & Gaming": [
        "Valorant", "Wuthering Waves", "Minecraft", "Return of the Disaster-Class Hero", 
        "truyện Manhwa", "chuột FPS", "OBS stream", "rank Kim Cương",
        "phim hoạt hình", "board game", "Đấu Trường Chân Lý", "Discord"
    ],
    "Học tập": [
        "Quản trị hệ thống", "CT179", "đồ án niên luận", "thực tập mạng", 
        "cấu hình Router", "bài tập nhóm", "bảo vệ đề tài", "viết báo cáo",
        "Toán rời rạc", "Lập trình web", "Cấu trúc dữ liệu", "thi cuối kỳ"
    ],
    "Đời sống thường ngày": [
        "chạy xe quanh Cần Thơ", "uống cà phê", "xem phim Donghua", "ngủ nướng", 
        "dạo phố", "ăn mì tôm", "nghe nhạc", "ăn quán vỉa hè", 
        "đi siêu thị", "tập gym", "nấu ăn", "dọn dẹp nhà cửa"
    ]
}

# ==========================================
# 2. VÒNG LẶP SINH RA 20.000 DÒNG DỮ LIỆU
# ==========================================
for i in range(20000):
    topic = random.choice(list(templates.keys()))
    emotion = random.choice(list(templates[topic].keys()))
    template = random.choice(templates[topic][emotion])
    keyword = random.choice(keywords[topic])
    
    prefix = random.choice(["", "Hôm nay ", "Nay ", "Tự nhiên ", "Đang ", "Thề luôn ", "Buồn cười quá ", "Sáng ra "])
    suffix = random.choice(["", " hihi", " :((", " haha", " chán", " quá", " thật sự", " ae ạ", " mn ơi", " :v", " vãi"])
    
    sentence = prefix + template.format(keyword) + suffix
    data.append([sentence.strip(), topic, emotion])

# ==========================================
# 3. LƯU THÀNH FILE CSV
# ==========================================
df = pd.DataFrame(data, columns=['Text', 'Topic', 'Emotion'])
df.drop_duplicates(subset=['Text'], inplace=True) # Xóa các câu bị trùng lặp ngẫu nhiên
df.to_csv('dataset_20k.csv', index=False, encoding='utf-8-sig')

print(f"✅ ĐÃ XONG! Tạo thành công {len(df)} dòng dữ liệu không trùng lặp vào file 'dataset_20k.csv'.")
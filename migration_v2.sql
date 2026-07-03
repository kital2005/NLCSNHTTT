-- Migration v2: Chạy trên DB social_ai_db đang có sẵn dữ liệu
-- Mở phpMyAdmin > chọn social_ai_db > tab SQL > dán và chạy từng khối nếu bị lỗi cột đã tồn tại

USE `social_ai_db`;

-- 1. Thêm cột role (bỏ qua nếu báo Duplicate column)
ALTER TABLE `users` ADD COLUMN `role` enum('user','admin') NOT NULL DEFAULT 'user' AFTER `cover_url`;
UPDATE `users` SET `role` = 'admin' WHERE `id` = 1;

-- 2. Thêm cột upload ảnh bài viết
ALTER TABLE `posts` ADD COLUMN `image_url` varchar(255) DEFAULT NULL AFTER `ai_sentiment`;

-- 3. Mở rộng loại thông báo
ALTER TABLE `notifications` MODIFY COLUMN `type` enum('like','comment','friend_request','friend_accept','ai_complete') NOT NULL;

-- 4. Bảng AI
CREATE TABLE IF NOT EXISTS `ai_training_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(50) NOT NULL,
  `svm_accuracy` decimal(5,2) DEFAULT NULL,
  `rf_accuracy` decimal(5,2) DEFAULT NULL,
  `dataset_size` int(11) DEFAULT NULL,
  `training_duration_seconds` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ai_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `post_content` text DEFAULT NULL,
  `predicted_topic` varchar(100) DEFAULT NULL,
  `predicted_emotion` varchar(50) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `processing_time_ms` int(11) DEFAULT NULL,
  `status` enum('success','fallback','error') DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ai_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

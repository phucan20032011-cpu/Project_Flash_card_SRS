-- =============================================
-- DATABASE: flashcard_srs
-- Dự án: Website học từ vựng bằng Flashcards + SRS
-- =============================================

CREATE DATABASE IF NOT EXISTS flashcard_srs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flashcard_srs;

-- Bảng người dùng
CREATE TABLE Users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) DEFAULT '',
    role        ENUM('user', 'admin') DEFAULT 'user',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng bộ từ vựng
CREATE TABLE Vocabulary_Sets (
    set_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Bảng flashcard
CREATE TABLE Flashcards (
    card_id     INT AUTO_INCREMENT PRIMARY KEY,
    set_id      INT NOT NULL,
    front_text  TEXT NOT NULL,
    back_text   TEXT NOT NULL,
    tags        VARCHAR(255) DEFAULT '',
    is_flagged  TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (set_id) REFERENCES Vocabulary_Sets(set_id) ON DELETE CASCADE
);

-- Bảng lịch SRS (mỗi user - mỗi thẻ có 1 bản ghi)
CREATE TABLE SRS_Schedule (
    schedule_id      INT AUTO_INCREMENT PRIMARY KEY,
    card_id          INT NOT NULL,
    user_id          INT NOT NULL,
    box_level        INT DEFAULT 1,
    easiness_factor  FLOAT DEFAULT 2.5,
    next_review_date DATE DEFAULT (CURDATE()),
    UNIQUE KEY uq_card_user (card_id, user_id),
    FOREIGN KEY (card_id) REFERENCES Flashcards(card_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Bảng lịch sử học tập
CREATE TABLE Study_History (
    history_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    card_id         INT NOT NULL,
    response_grade  TINYINT NOT NULL COMMENT '0=Quên, 1=Khó, 2=Khá, 3=Nhớ',
    reviewed_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES Flashcards(card_id) ON DELETE CASCADE
);

-- Tài khoản mẫu: admin@demo.com / password: 123456
INSERT INTO Users (email, password_hash, full_name, role) VALUES
('admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Demo', 'admin'),
('student@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sinh Viên Demo', 'user');

-- Bộ từ vựng mẫu
INSERT INTO Vocabulary_Sets (user_id, title, description) VALUES
(2, 'Tiếng Anh Cơ Bản', 'Từ vựng tiếng Anh thông dụng hàng ngày'),
(2, 'Động Từ Bất Quy Tắc', 'Các động từ bất quy tắc hay gặp');

-- Flashcard mẫu
INSERT INTO Flashcards (set_id, front_text, back_text, tags) VALUES
(1, 'Apple', 'Táo - loại quả màu đỏ hoặc xanh', 'danh từ,đồ ăn'),
(1, 'Book', 'Sách - vật dụng để đọc', 'danh từ,học tập'),
(1, 'Run', 'Chạy - di chuyển nhanh bằng chân', 'động từ'),
(1, 'Beautiful', 'Đẹp - có vẻ ngoài hấp dẫn', 'tính từ'),
(1, 'Happiness', 'Hạnh phúc - trạng thái vui vẻ, thỏa mãn', 'danh từ,cảm xúc'),
(2, 'Go - Went - Gone', 'Đi - Đã đi - Đã đi (xong)', 'bất quy tắc'),
(2, 'See - Saw - Seen', 'Nhìn thấy - Đã nhìn thấy - Đã nhìn thấy (xong)', 'bất quy tắc'),
(2, 'Take - Took - Taken', 'Lấy/Mang - Đã lấy - Đã lấy (xong)', 'bất quy tắc');

# FlashCard SRS — Hướng dẫn Cài đặt & Chạy trên XAMPP

## Yêu cầu hệ thống

- XAMPP (Apache + MySQL + PHP 7.4+)
- Trình duyệt Chrome / Firefox / Edge

---

## Cấu trúc thư mục

```
flashcard/
├── config/
│   └── db.php
├── assets/
│   ├── style.css
│   └── script.js
├── api/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── save_set.php
│   ├── delete_set.php
│   ├── save_card.php
│   ├── delete_card.php
│   ├── flag_card.php
│   ├── update_srs.php
│   └── delete_user.php
├── views/
│   ├── header.php
│   └── footer.php
├── index.php
├── dashboard.php
├── cards.php
├── study.php
├── stats.php
├── admin_dashboard.php
├── admin_users.php
└── database.sql
```

---

## Các bước cài đặt

### Bước 1: Copy thư mục vào XAMPP

```
C:\xampp\htdocs\flashcard\
```

### Bước 2: Tạo Database

1. Mở trình duyệt → truy cập `http://localhost/phpmyadmin`
2. Click **"New"** → Đặt tên `flashcard_srs` → Click **"Create"**
3. Chọn tab **"Import"** → Chọn file `database.sql` → Click **"Go"**

Hoặc dán toàn bộ nội dung `database.sql` vào tab **"SQL"** và nhấn **"Go"**.

### Bước 3: Kiểm tra cấu hình database

Mở file `config/db.php` và kiểm tra:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'flashcard_srs');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Bước 4: Khởi động XAMPP

- Mở XAMPP Control Panel
- Start **Apache** và **MySQL**

### Bước 5: Truy cập website

Mở trình duyệt → `http://localhost/flashcard/`

---

## Tài khoản Demo

| Email            | Mật khẩu | Vai trò    |
| ---------------- | -------- | ---------- |
| student@demo.com | password | Người dùng |
| admin@demo.com   | password | Admin      |

---

## Thuật toán SRS (Leitner đơn giản)

| Đánh giá | Hành động      | Khoảng cách ôn     |
| -------- | -------------- | ------------------ |
| Quên (0) | Reset về Hộp 1 | 1 ngày             |
| Khó (1)  | Tăng 1 hộp     | 2^(box-1) / 2 ngày |
| Khá (2)  | Tăng 1 hộp     | 2^(box-1) ngày     |
| Nhớ (3)  | Tăng 1 hộp     | 2^(box-1) ngày     |

Ví dụ: Hộp 1 → 1 ngày, Hộp 2 → 2 ngày, Hộp 3 → 4 ngày, Hộp 4 → 8 ngày...

---

## Bảo mật

- Dùng **PDO + Prepared Statements** → Chống SQL Injection
- Mật khẩu mã hóa bằng **bcrypt** (password_hash)
- Kiểm tra quyền sở hữu trước mọi thao tác sửa/xóa
- Session PHP để xác thực người dùng

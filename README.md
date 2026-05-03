# 🧠 FlashCard SRS — Hướng dẫn Cài đặt & Chạy trên XAMPP

## Yêu cầu hệ thống
- XAMPP (Apache + MySQL + PHP 7.4+)
- Trình duyệt Chrome / Firefox / Edge

---

## Cấu trúc thư mục
```
flashcard/
├── config/
│   └── db.php              ← Kết nối database (PDO)
├── assets/
│   ├── style.css           ← CSS giao diện
│   └── script.js           ← JavaScript (Vanilla)
├── api/
│   ├── login.php           ← API đăng nhập
│   ├── register.php        ← API đăng ký
│   ├── logout.php          ← Đăng xuất
│   ├── save_set.php        ← Lưu bộ từ vựng
│   ├── delete_set.php      ← Xóa bộ từ vựng
│   ├── save_card.php       ← Lưu flashcard
│   ├── delete_card.php     ← Xóa flashcard
│   ├── flag_card.php       ← Đánh dấu thẻ
│   └── update_srs.php      ← Cập nhật thuật toán SRS ⭐
├── views/
│   ├── header.php          ← Header dùng chung
│   └── footer.php          ← Footer dùng chung
├── index.php               ← Trang đăng nhập / đăng ký
├── dashboard.php           ← Danh sách bộ từ vựng
├── cards.php               ← Quản lý flashcard
├── study.php               ← Trang học (lật thẻ + SRS)
├── stats.php               ← Thống kê học tập
└── database.sql            ← Tạo database
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
define('DB_USER', 'root');     // XAMPP mặc định
define('DB_PASS', '');         // XAMPP mặc định: để trống
```

### Bước 4: Khởi động XAMPP
- Mở XAMPP Control Panel
- Start **Apache** và **MySQL**

### Bước 5: Truy cập website
Mở trình duyệt → `http://localhost/flashcard/`

---

## Tài khoản Demo
| Email | Mật khẩu | Vai trò |
|-------|----------|---------|
| student@demo.com | password | Người dùng |
| admin@demo.com | password | Admin |

---

## Tính năng chính
- ✅ Đăng ký / Đăng nhập bảo mật (password_hash bcrypt)
- ✅ Quản lý bộ từ vựng (CRUD)
- ✅ Quản lý Flashcard (CRUD + đánh dấu)
- ✅ Lật thẻ 3D (CSS transform + transition)
- ✅ Thuật toán SRS Leitner (10 hộp)
- ✅ Cập nhật SRS bằng AJAX (không tải lại trang)
- ✅ Thống kê học tập (biểu đồ 7 ngày, tỷ lệ ghi nhớ)
- ✅ Responsive trên điện thoại và máy tính

---

## Thuật toán SRS (Leitner đơn giản)
| Đánh giá | Hành động | Khoảng cách ôn |
|----------|-----------|----------------|
| 😵 Quên (0) | Reset về Hộp 1 | 1 ngày |
| 😓 Khó (1) | Tăng 1 hộp | 2^(box-1) / 2 ngày |
| 🙂 Khá (2) | Tăng 1 hộp | 2^(box-1) ngày |
| 😄 Nhớ (3) | Tăng 1 hộp | 2^(box-1) ngày |

Ví dụ: Hộp 1 → 1 ngày, Hộp 2 → 2 ngày, Hộp 3 → 4 ngày, Hộp 4 → 8 ngày...

---

## Bảo mật
- Dùng **PDO + Prepared Statements** → Chống SQL Injection
- Mật khẩu mã hóa bằng **bcrypt** (password_hash)
- Kiểm tra quyền sở hữu trước mọi thao tác sửa/xóa
- Session PHP để xác thực người dùng

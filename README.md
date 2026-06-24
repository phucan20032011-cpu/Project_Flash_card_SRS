# FlashCard SRS — Hướng dẫn Cài đặt & Chạy trên XAMPP

## Yêu cầu hệ thống

- XAMPP (Apache + MySQL + PHP 7.4+)
- Trình duyệt Chrome / Firefox / Edge

---

## Cấu trúc thư mục

```
flashcard/
├── api/
│   ├── change_password.php
│   ├── delete_card.php
│   ├── delete_set.php
│   ├── delete_user.php
│   ├── flag_card.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── reset_password.php
│   ├── save_card.php
│   ├── save_set.php
│   ├── update_srs.php
│   └── verify_identity.php
├── assets/
│   ├── script.js
│   └── style.css
├── config/
│   └── db.php
├── views/
│   ├── footer.php
│   └── header.php
├── admin_dashboard.php
├── admin_users.php
├── cards.php
├── dashboard.php
├── database.sql
├── forgot_password.php
├── index.php
├── profile.php
├── README.md
├── stats.php
└── study.php
```

---

## phân chia công việc nhóm

```
1. Lê Lý Phúc An - Nhóm trưởng: Phụ trách Hệ thống lõi & Xác thực
Trọng tâm là thiết lập nền tảng, kiến trúc database, luồng xác thực bảo mật và quản lý tài khoản.
-	Database & Môi trường: config/db.php, database.sql (Thiết kế schema, cấu trúc 5 bảng dữ liệu cơ sở).
-	Xác thực (Auth): index.php, api/login.php, api/register.php, api/logout.php. Xử lý mã hóa mật khẩu (password_hash), xác thực (password_verify) và quản lý Session.
-	Quản lý tài khoản: profile.php, forgot_password.php, api/reset_password.php, api/verify_identity.php, api/change_password.php.
-	Quản lý tiến độ: Tổng hợp code, ráp nối các module và quản trị mã nguồn.
2. Trần Văn Dương: Phụ trách Quản lý dữ liệu (CRUD Content)
Trọng tâm là luồng thao tác dữ liệu chính của người dùng (Thêm, sửa, xóa bộ từ và flashcard), đảm bảo tính toàn vẹn dữ liệu.
-	Giao diện quản lý: dashboard.php (Hiển thị danh sách bộ từ), cards.php (Hiển thị chi tiết thẻ trong bộ).
-	API Bộ từ vựng (Sets): api/save_set.php, api/delete_set.php.
-	API Thẻ nhớ (Cards): api/save_card.php, api/delete_card.php, api/flag_card.php.
-	Logic Database: Xử lý ràng buộc khóa ngoại (ON DELETE CASCADE) để đảm bảo không bị rác dữ liệu khi xóa bộ từ.
3. Đinh Việt Hoàng: Phụ trách UI/UX & Tương tác Frontend (Study UX)
Trọng tâm là trải nghiệm người dùng, thiết kế giao diện tổng thể và hiệu ứng lật thẻ 3D – phần hình ảnh của trang web.
-	Giao diện học tập: study.php (Bố cục khu vực lật thẻ, thanh tiến độ học tập).
-	Tài nguyên tĩnh (Assets):
+	assets/style.css: Xây dựng toàn bộ layout, CSS Grid/Flexbox, responsive đa thiết bị, và CSS Transform cho hiệu ứng lật thẻ 3D.
+	assets/script.js: Xử lý DOM, logic bắt sự kiện lật thẻ, hiển thị Toast thông báo.
-	Layout chung: views/header.php, views/footer.php (bao gồm xử lý dropdown menu và modal).
4. Đinh Hoàng Gia Bảo: Phụ trách Logic SRS, Thống kê & Quản trị (Admin)
Trọng tâm là phần logic (thuật toán lặp lại ngắt quãng) và các chức năng báo cáo, thống kê dữ liệu.
-	Thuật toán SRS: api/update_srs.php (Tính toán ngày ôn tiếp theo dựa trên hệ số Leitner, xử lý logic Quên/Khó/Khá/Nhớ).
-	Thống kê (Analytics): stats.php (Truy xuất dữ liệu từ study_history và srs_schedule để vẽ biểu đồ, tính tỷ lệ ghi nhớ và chuỗi streak).
-	Phân hệ Admin: admin_dashboard.php (Thống kê toàn trang), admin_users.php (Danh sách user), api/delete_user.php (Quyền xóa tài khoản).
-	Kết nối API: Viết các hàm fetchAPI trong JavaScript để giao tiếp bất đồng bộ với Backend lúc lật thẻ.


```

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

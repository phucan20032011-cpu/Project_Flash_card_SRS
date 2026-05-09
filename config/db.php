<?php
// config/db.php - Kết nối cơ sở dữ liệu
// Sử dụng PDO để kết nối an toàn

define('DB_HOST', 'localhost');
define('DB_NAME', 'flashcard_srs');
define('DB_USER', 'root');       // Tài khoản XAMPP mặc định
define('DB_PASS', '');           // Mật khẩu XAMPP mặc định (để trống)
define('DB_CHARSET', 'utf8mb4');

// Hàm tạo kết nối PDO - gọi hàm này ở mọi file cần truy vấn DB
function getDB()
{
    static $pdo = null; // Chỉ tạo kết nối 1 lần (Singleton pattern đơn giản)

    if ($pdo === null) {
        // $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $dsn = "mysql:host=" . DB_HOST . ";port=3307;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi bằng exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về mảng kết hợp
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Dùng prepared statement thật
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Hiển thị lỗi thân thiện thay vì lộ thông tin kỹ thuật
            die(json_encode(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu!']));
        }
    }

    return $pdo;
}

// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra người dùng đã đăng nhập chưa
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Hàm bắt buộc đăng nhập - dùng ở các trang cần xác thực
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /flashcard/index.php');
        exit;
    }
}

// Hàm trả về JSON - dùng cho các file API
function jsonResponse($success, $message, $data = [])
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}



// Hàm bắt buộc quyền Admin - dùng cho các trang quản trị
function requireAdmin()
{
    requireLogin(); // Phải đăng nhập trước
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Nếu không phải admin, đá về trang dashboard của user
        header('Location: /flashcard/dashboard.php');
        exit;
    }
}

<?php
// config/db.php - Kết nối cơ sở dữ liệu


define('DB_HOST', 'localhost');
define('DB_NAME', 'flashcard_srs');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Hàm tạo kết nối 
function getDB()
{
    static $pdo = null; // tạo kết nối 1 lần
    if ($pdo === null) {
        // $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $dsn = "mysql:host=" . DB_HOST . ";port=3307;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Hiển thị lỗi 
            die(json_encode(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu!']));
        }
    }

    return $pdo;
}

// session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//kiểm tra đã đăng nhập
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// bắt buộc đăng nhập - dùng ở các trang cần xác thực
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /flashcard/index.php');
        exit;
    }
}

// trả về JSON - dùng cho các file API
function jsonResponse($success, $message, $data = [])
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}



// quyền Admin - dùng cho trang quản trị
function requireAdmin()
{
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Nếu không phải admin, về trang dashboard
        header('Location: /flashcard/dashboard.php');
        exit;
    }
}

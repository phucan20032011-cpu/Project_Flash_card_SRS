<?php
// login.php - Xử lý đăng nhập
require_once __DIR__ . '/../config/db.php';

// POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

// Lấy dữ liệu JSON từ client
$input = json_decode(file_get_contents('php://input'), true);

$email    = trim($input['email']    ?? '');
$password =      $input['password'] ?? '';

// Kiểm tra đầu vào
if (empty($email) || empty($password)) {
    jsonResponse(false, 'Vui lòng nhập đầy đủ email và mật khẩu!');
}

$db = getDB();

// Tìm user theo email - dùng Prepared Statement để tránh SQL Injection
$stmt = $db->prepare("SELECT user_id, email, full_name, password_hash, role FROM Users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Kiểm tra user và mật
if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(false, 'Email hoặc mật khẩu không đúng!');
}

// Lưu thông tin vào session
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['email']     = $user['email'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role']      = $user['role'];

jsonResponse(true, 'Đăng nhập thành công!');

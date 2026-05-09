<?php
// register.php - Xử lý đăng ký tài khoản mới
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

$input = json_decode(file_get_contents('php://input'), true);

$full_name = trim($input['full_name'] ?? '');
$email     = trim($input['email']     ?? '');
$password  =      $input['password']  ?? '';

// Kiểm tra đầu vào
if (empty($full_name) || empty($email) || empty($password)) {
    jsonResponse(false, 'Vui lòng điền đầy đủ thông tin!');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Email không hợp lệ!');
}

if (strlen($password) < 6) {
    jsonResponse(false, 'Mật khẩu phải có ít nhất 6 ký tự!');
}

$db = getDB();

// Kiểm tra email đã tồn tại chưa
$stmt = $db->prepare("SELECT user_id FROM Users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonResponse(false, 'Email này đã được đăng ký!');
}

// Mã hóa mật khẩu bằng bcrypt 
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Thêm user mới vào database
$stmt = $db->prepare("INSERT INTO Users (email, full_name, password_hash, role) VALUES (?, ?, ?, 'user')");
$stmt->execute([$email, $full_name, $password_hash]);

$userId = $db->lastInsertId();

// Tự động đăng nhập
$_SESSION['user_id']   = $userId;
$_SESSION['email']     = $email;
$_SESSION['full_name'] = $full_name;
$_SESSION['role']      = 'user';

jsonResponse(true, 'Đăng ký tài khoản thành công!');

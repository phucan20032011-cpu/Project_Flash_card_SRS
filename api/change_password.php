<?php
// api/change_password.php - Đổi mật khẩu (cần đăng nhập, nhập mật khẩu cũ để xác nhận)
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin();

$input       = json_decode(file_get_contents('php://input'), true);
$oldPassword = $input['old_password'] ?? '';
$newPassword = $input['new_password'] ?? '';
$userId      = $_SESSION['user_id'];

if (empty($oldPassword) || empty($newPassword)) {
    jsonResponse(false, 'Vui lòng nhập đầy đủ thông tin!');
}

if (strlen($newPassword) < 6) {
    jsonResponse(false, 'Mật khẩu mới phải có ít nhất 6 ký tự!');
}

$db = getDB();

// Lấy mật khẩu hiện tại
$stmt = $db->prepare("SELECT password_hash FROM Users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Kiểm tra mật khẩu cũ
if (!password_verify($oldPassword, $user['password_hash'])) {
    jsonResponse(false, 'Mật khẩu hiện tại không đúng!');
}

// Cập nhật mật khẩu mới
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
$stmt->execute([$hash, $userId]);

jsonResponse(true, 'Đổi mật khẩu thành công!');
